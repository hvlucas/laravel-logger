<?php

namespace HVLucas\LaravelLogger;

use Carbon\Carbon;
use ReflectionClass;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use HVLucas\LaravelLogger\App\Event;
use HVLucas\LaravelLogger\Exceptions\ClassNotMatchedException;

class LaravelLoggerModel
{
    // Model class name being tracked
    protected $class_name;

    // Model attributes that are going to be tracked
    protected $attributes;

    // Model attributes that are going to be updated when syncing
    protected $sync_attributes;

    // Flag to determine if Authenticated User is being tracked
    protected $tracks_current_user;

    // Flag to determine if Data is being tracked, this flag should overwrite if $attributes property is set
    protected $tracks_data;

    // Flag to see if this Model should show up first on the list of models being tracked
    protected $is_favorite;
    
    // Constructor for LaravelLoggerModel
    public function __construct(string $class_name, array $attributes, array $sync_attributes, bool $tracks_user, bool $tracks_data, $is_favorite)
    {
        $this->class_name = $class_name;

        //if attributes is not set in config, check model class for 'trackable_attributes' property
        $reflection = new ReflectionClass($class_name);
        if(empty($attributes)){
            if($reflection->hasProperty('trackable_attributes')){
                $property = $reflection->getProperty('trackable_attributes');
                $property->setAccessible('true');
                $attributes = (array) $property->getValue(new $class_name);
            }
        }

        $this->attributes = $attributes;

        if(empty($sync_attributes)){
            if($reflection->hasProperty('sync_attributes')){
                $property = $reflection->getProperty('sync_attributes');
                $property->setAccessible('true');
                $sync_attributes = (array) $property->getValue(new $class_name);
            }else{
                $sync_attributes = $attributes;
            }
            foreach($sync_attributes as $attr){
                if(!Schema::hasColumn($table, $attr)){
                    throw new ColumnNotFoundException("Column `$attr` was not found in $table table");

                }
            }
        }

        $this->sync_attributes = $sync_attributes;
        $this->tracks_current_user = $tracks_user;
        $this->tracks_data = $tracks_data;
        $this->is_favorite = $is_favorite;
    }

    // Get model instance attribute values (json encoded)
    public function getAttributeValues($model, $for_sync)
    {
        $class_name = get_class($model);
        if($class_name != $this->class_name){
            throw new ClassNotMatchedException("Model `$class_name` does not match `$this->class_name`.");
        }
        
        if($for_sync){
            $attributes = $this->sync_attributes;
        }else{
            $attributes = $this->attributes;
        }

        if(empty($attributes)){
            $hidden = array_merge(['id', 'created_at', 'updated_at'], $model->getHidden());
            $attributes = $model->refresh()->setHidden($hidden)->attributesToArray();
        }else{
            $attributes = $model->only($attributes);
        }

        return json_encode($attributes);
    }

    // Returns syncable attributes
    public function getSyncAttributes()
    {
        return $this->sync_attributes;
    }

    // Return tracks_current_user attribute
    public function isTrackingAuthenticatedUser()
    {
        return $this->tracks_current_user;
    }

    // Return tracks_data attribute
    public function isTrackingData()
    {
        return $this->tracks_data;
    }

    // Returns class name
    public function getClassName()
    {
        return $this->class_name;
    }

    // Returns events that are going to get tracked
    public function getEvents()
    {
        return $this->events;
    }

    // Returns is_favorite property
    public function getIsFavorite()
    {
        return $this->is_favorite;
    }

    // Sets starting point for each model record
    public function setStartingPoint()
    {
        $event_instance = new Event;
        $event_table = $event_instance->getTable();

        // if user hasn't migrated yet, dont initialize starting point
        if(!Schema::hasTable($event_table)){
            return;
        }
        
        $model = $this->class_name;
        $model_instance = new $model;
        $model_table = $model_instance->getTable();
        $model_key = $model_instance->getKeyName();
        if($model != "App\NewNamespace\ModelTestV2"){
            return;
        }
        $ids = Event::where('model_name', $model)->pluck('model_id')->unique();
        $models = $model::whereNotIn($model_key, $ids)->get();

        foreach($models as $init_model){
            $attributes = $this->getAttributeValues($init_model, false);
            $sync_attributes = $this->getAttributeValues($init_model, true);
            Event::store([
                'activity' => 'startpoint',
                'user_id' => null,
                'model_id' => (string) $init_model->{$model_key},
                'model_name' => $model, 
                'model_attributes' => $attributes,
                'sync_attributes' => $sync_attributes,
                'user_agent' => null,
                'method' => null,
                'full_url' => null,
                'created_at' => new Carbon,
            ]);
        }
    }

    // Return the class name without the namespace
    public function getClassNameNoNamespace()
    {
        $class_name = explode('\\', $this->class_name);
        return end($class_name);
    }

    // Return class name with full namespace with replacement char
    public function getClassNameNoSlashes($char='-')
    {
        return strtolower(implode($char, explode('\\', $this->class_name)));
    }
}
