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

    // Current session ID
    protected $session_id;

    // Current IP address
    protected $ip_address;

    // Current user ID
    protected $user_id;

    // Browser/Device request is coming from
    protected $user_agent;

    // Full URL of Request made
    protected $full_url;

    // Request was made through AJAX
    protected $ajax;

    // This property is to check if session is being tracked during event, this will prevent multiple logging events
    // from triggering during a fetch
    protected $is_tracking;

    public function __construct() 
    {
        $this->ip_address       = Request::ip();
        $this->session_id       = Session::getId();
        $this->user_id          = null;
        $this->user_agent       = null;
        $this->full_url         = null;
        $this->ajax             = null;
        $this->is_tracking      = false;
    }

    // Push an instance of LaravelLoggerModel into list of trackable models
    public function push(LaravelLoggerModel $model)
    {
        $this->models[$model->getClassName()] = $model;
    }

    // Get model from list based on class name string
    public function getModel(string $class_name)
    {
        if(!isset($this->models[$class_name])){
            throw new ModelNotFoundException;
        }
        return $this->models[$class_name];
    }
    
    // Return instance of LaravelLoggerTracker
    public function getTracker()
    {
        return $this;
    } 

    // Return session id
    public function getSessionId()
    {
        return $this->session_id;
    }

    // Return IP Address
    public function getIp()
    {
        return $this->ip_address;
    }

    // Return AJAX property
    public function getAjax()
    {
        return $this->ajax;
    }

    // Return full url property
    public function getFullUrl()
    {
        return $this->full_url;
    }

    // Return user agent property
    public function getUserAgent()
    {
        return $this->user_agent;
    }

    // Set full url property to current request full url
    public function refreshFullUrl()
    {
        $this->full_url = Request::fullUrl();
    }

    // Set ajax property to current request ajax 
    public function refreshIsAjax()
    {
        $this->ajax = Request::ajax();
    }

    // Set user agent property to current request user agent
    public function refreshUserAgent()
    {
        $this->user_agent = Request::userAgent();
    }

    // Set user id property with authenticated user
    public function refreshUserId()
    {
        $id = null;
        $user = Auth::user();
        if($user){
            $id = $user->{$user->getKeyName()};
        }
        $this->user_id = $id;
    }

    // Return is tracking property
    public function isTracking()
    {
        return $this->is_tracking;
    }

    // Set is tracking property to a boolean state
    public function setTracking(bool $state)
    {
        $this->is_tracking = $state;
    }
}
