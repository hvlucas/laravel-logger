<?php

namespace HVLucas\LaravelLogger;

use Illuminate\Support\Facades\Request;
use Illuminate\Contracts\Session;
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

    // Browser/Device user is using
    protected $user_agent;

    // Full URL of Request made
    protected $full_user;

    // Request was made through AJAX
    protected $ajax;

    public function __construct() 
    {
        $this->current_user_id  = Auth::getUser()
        $this->ip_address       = Request::ip();
        $this->session_id       = Session::getId();
        $this->user_agent       = null;
        $this->full_url         = null;
        $this->ajax             = null;
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

    // Get session id
    public function getSessionId()
    {
        return $this->session_id;
    }

    public function getIp()
    {
        return $this->ip_address;
    }

    public function refreshFullUrl()
    {
        $this->full_url = Request::fullUrl();
    }

    public function refreshIsAjax()
    {
        $this->ajax = Request::ajax();
    }

    public function refreshUserAgent()
    {
        $this->user_agent = Request::userAgent();
    }
}
