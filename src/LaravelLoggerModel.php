<?php

namespace HVLucas\LaravelLogger;

use ReflectionClass;
use HVLucas\LaravelLogger\Exceptions\ClassNotMatchedException;
use HVLucas\LaravelLogger\App\Event;

class LaravelLoggerModel
{
    // Model class name being tracked
    protected $class_name;

    // Events that are going to be tracked
    protected $events;

    // Model attributes that are going to be tracked
    protected $attributes;

    // Flag to determine if Authenticated User is being tracked
    protected $tracks_current_user;

    // Flag to determine if Data is being tracked, this flag should overwrite if $attributes property is set
    protected $tracks_data;

    public function __construct(string $class_name, array $events, array $attributes, bool $tracks_user, bool $tracks_data)
    {
        $this->class_name = $class_name;

        if(empty($events)){
            $events = config('laravel_logger.default_events', ['created', 'updated', 'deleted', 'retrieved']);
        }
        $this->events = $events;

        //if attributes is not set in config, check model class for 'loggable' property
        if(empty($attributes)){
            $reflection = new ReflectionClass($class_name);
            if($reflection->hasProperty('loggable')){
                $property = $reflection->getProperty('loggable');
                $property->setAccessible('true');
                $attributes = $property->getValue(new $class_name);
            }
        }

        $this->attributes = $attributes;
        $this->tracks_current_user = $tracks_user;
        $this->tracks_data = $tracks_data;
    }

    public function isTrackingAuthenticatedUser()
    {
        return $this->tracks_current_user;
    }

    public function isTrackingData()
    {
        return $this->tracks_data;
    }

    public function isTrackingEvent(string $event)
    {
        return array_search($event, $this->events) !== false;
    }

    public function getAttributeValues($model)
    {
        //TODO
        //Throw exception if model class does not match model given
        $class_name = get_class($model);
        if($class_name != $this->class_name){
            throw new ClassNotMatchedException("Model '$class_name' does not match $this->class_name.");
        }
        
        $attributes = $this->attributes;
        if(empty($attributes)){
            $hidden = ['id'] + $model->getHidden();
            $attributes = $model->setHidden($hidden)->attributesToArray();
        }else{
            $attributes = $model->only($attributes);
        }
        return json_encode($attributes);
    }

    /* Getters */

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

    // Sets starting point for each model record
    public function setStartingPoint()
    {
        $event_instance = new Event;
        $event_table = $event_instance->getTable();

        if(!Schema::hasTable($event_table)){
            return;
        }
        
        $model = $this->class_name;
        $model_instance = new $model;
        $model_table = $model_instance->getTable();
        $model_key = $model_instance->getKeyName();

        $models = $model::leftJoin($event_table, "$model_table.$model_key", '=', "$event_table.model_id")->select("$model_table.*", "$event_table.activity as event_activity_id")->whereNull("$event_table.activity")->get();

        foreach($models as $init_model){
            $created_at = new DateTime;
            $created_at->setTimestamp(time());
            $attributes = $this->getAttributeValues($init_model);
            Event::create([
                'activity' => 'startpoint',
                'user_id' => null,
                'model_id' => (string) $init_model->{$init_model->getKeyName()},
                'model_name' => $model, 
                'model_attributes' => $attributes,
                'user_agent' => null,
                'session_id' => null,
                'ajax' => false,
                'full_url' => null,
                'created_at' => $created_at,
            ]);
        }
    }
}
