<?php

namespace HVLucas\LaravelLogger\Observers;

use HVLucas\LaravelLogger\Facades\LaravelLogger;

class ModelObserver
{
    // Log created eloquent event
    public function created($model)
    {
        $this->logModelEvent($model, 'created');
    }
    
    // Log updated eloquent event
    public function updated($model)
    {
        $this->logModelEvent($model, 'updated');
    }

    // Log deleted eloquent event
    public function deleted($model)
    {
        $this->logModelEvent($model, 'deleted');
    }

    // Log restored eloquent event
    public function restored($model)
    {
        $this->logModelEvent($model, 'restored');
    }

    // Log event
    private function logModelEvent($model_tracked, $event): void
    {
        LaravelLogger::getModel(get_class($model_tracked))->logModelEvent($model_tracked, $event);
    }
}
