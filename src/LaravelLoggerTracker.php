<?php

namespace HVLucas\LaravelLogger;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use HVLucas\LaravelLogger\Exceptions\ModelNotFoundException;

class LaravelLoggerTracker
{
    // Models that are being tracked by Observer
    protected $models;

    // This property is to check if session is being tracked during event, this will prevent multiple logging events
    // from triggering during a fetch
    protected $is_tracking;

    public function __construct() 
    {
        $this->models       = [];
        $this->is_tracking  = false;
    }

    // Push an instance of LaravelLoggerModel into list of trackable models
    public function push(LaravelLoggerModel $model)
    {
        $this->models[$model->getClassName()] = $model;
    }

    // Set is tracking property to a boolean state
    public function setTracking(bool $state)
    {
        $this->is_tracking = $state;
    }

    /* Getters */

    // Return is tracking property
    public function isTracking()
    {
        return $this->is_tracking;
    }

    // Get model from list based on class name string
    public function getModel(string $class_name)
    {
        if(!isset($this->models[$class_name])){
            throw new ModelNotFoundException;
        }
        return $this->models[$class_name];
    }
    
    // Return all Models
    public function getModels()
    {
        return $this->models;
    }

    // Return all Models in Collection form
    public function getModelCollection()
    {
        return collect($this->getModels());
    }
    
    // Return instance of LaravelLoggerTracker
    public function getTracker()
    {
        return $this;
    } 

    // Return request method
    public function getMethod()
    {
        return Request::method();
    }

    // Return request IP Address
    public function getIp()
    {
        return Request::ip();
    }

    // Return request full url 
    public function getFullUrl()
    {
        return Request::fullUrl();
    }

    // Return user agent property
    public function getUserAgent()
    {
        return Request::userAgent();
    }

    // Get user id property with authenticated user
    public function getUserId()
    {
        $id = null;
        $user = Auth::user();
        if($user){
            $id = $user->{$user->getKeyName()};
        }
        return $id;
    }
}
