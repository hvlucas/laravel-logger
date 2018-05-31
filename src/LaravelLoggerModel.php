<?php

namespace HVLucas\LaravelLogger;

use ReflectionClass;
use HVLucas\LaravelLogger\Exceptions\ClassNotMatchedException;

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

    // Getters
    public function getClassName()
    {
        return $this->class_name;
    }

    public function getEvents()
    {
        return $this->events;
    }
}
