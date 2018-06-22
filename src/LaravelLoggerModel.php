<?php

namespace HVLucas\LaravelLogger;

use Carbon\Carbon;
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

    // Flag to only log event when an authenticated user is present
    protected $only_when_authenticated;
    
    // Constructor for LaravelLoggerModel
    public function __construct(string $class_name, array $attributes, array $sync_attributes, bool $tracks_user, bool $tracks_data, bool $only_when_authenticated, bool $is_favorite)
    {
        $this->class_name = $class_name;
        $this->attributes = $attributes;
        $this->sync_attributes = $sync_attributes;
        $this->tracks_current_user = $tracks_user;
        $this->tracks_data = $tracks_data;
        $this->only_when_authenticated = $only_when_authenticated;
        $this->is_favorite = $is_favorite;
    }

    // Get model instance attribute values (json encoded)
    public function getAttributeValues($model, $for_sync)
    {
        if(!$this->tracks_data){
            return json_encode([]);
        }

        $class_name = get_class($model);
        if($class_name != $this->class_name){
            throw new ClassNotMatchedException("Model `$class_name` does not match `$this->class_name`.");
        }
        
        if($for_sync){
            $attributes = $this->sync_attributes;
        }else{
            $attributes = $this->attributes;
        }

        return json_encode($model->only($attributes));
    }

    // Returns `$this->attributes` property
    public function getAttributes()
    {
        return $this->attributes;
    }

    // Returns `$this->sync_attributes` property
    public function getSyncAttributes()
    {
        return $this->sync_attributes;
    }

    // Return `$this->tracks_current_user` property
    public function isTrackingAuthenticatedUser()
    {
        return $this->tracks_current_user;
    }

    // Return `$this->tracks_data` property
    public function isTrackingData()
    {
        return $this->tracks_data;
    }

    // Returns `$this->class_name` property
    public function getClassName()
    {
        return $this->class_name;
    }

    // Returns `$this->events` property
    public function getEvents()
    {
        return $this->events;
    }

    // Returns `$this->is_favorite` property
    public function getIsFavorite()
    {
        return $this->is_favorite;
    }

    // Returns `$this->only_when_authenticated` property
    public function getOnlyWhenAuthenticated()
    {
        return $this->only_when_authenticated;
    }

    // Sets starting point for each model record
    public function setStartingPoint()
    {
        $event_instance = new Event;
        $event_table = $event_instance->getTable();
        // if user hasn't migrated yet, dont set starting point
        if(!Schema::hasTable($event_table)){
            return;
        }
        
        $model = $this->class_name;
        $model_instance = new $model;
        $model_table = $model_instance->getTable();
        $model_key = $model_instance->getKeyName();
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

    // Log event
    public function logModelEvent($model_tracked, $event): void
    {
        $tracker = LaravelLogger::getTracker();
        $current_user_id = null;
        if($this->isTrackingAuthenticatedUser()){
            $current_user_id = $tracker->getUserId();
        }
        // don't log event when current user is null and only_when_authenticated is true
        if($this->getOnlyWhenAuthenticated() && $current_user_id === null){
            return;
        }
        //if we're going to log, set starting point when event is `created`
        if($event == 'created'){
            $this->setStartingPoint();
        }

        $attributes = $sync_attributes = json_encode([]);
        if($this->isTrackingData()){
            $attributes = $this->getAttributeValues($model_tracked, false);
            $sync_attributes = $this->getAttributeValues($model_tracked, true);
        }

        Event::store([
            'activity' => $event,
            'model_id' => (string) $model_tracked->{$model_tracked->getKeyName()},
            'model_name' => get_class($model_tracked),
            'model_attributes' => $attributes,
            'sync_attributes' => $sync_attributes,
            'created_at' => new Carbon,
            'user_id' => $current_user_id,
            'user_agent' => $tracker->getUserAgent(),
            'ip_address' => $tracker->getIp(),
            'full_url' => $tracker->getFullUrl(),
            'method' => $tracker->getMethod()
        ]);
    }
}
