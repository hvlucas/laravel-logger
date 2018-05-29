<?php

namespace HVLucas\LaravelLogger;

use HVLucas\LaravelLogger\Exceptions\ModelNotFoundException;

class LaravelLoggerTracker
{
    // Models that are being tracked by Observer
    protected $models;

    // TBD
    public function __construct() {}

    // Push an instance of LaravelLoggerModel into list of trackable models
    public function push(LaravelLoggerModel $model)
    {
        $this->models[$model->getClassName()] = $model;
    }

    public function getModel(string $model)
    {
        if(!isset($this->models[$model])){
            throw new ModelNotFoundException;
        }
        return $this->models[$model];
    }
}
