<?php

namespace App\Observers;

use ReflectionClass;
use Illuminate\Support\Facades\Auth;
use HVLucas\LaravelLogger\Event;

class ModelObserver
{
    /*
     * Log created eloquent event
     */
    public function created($model)
    {
        $this->logModelEvent($model, 'created', 'info', true);
    }

    /*
     * Log updated eloquent event
     */
    public function updated($model)
    {
        $this->logModelEvent($model, 'updated', 'info', true);
    }

    /*
     * Log deleted eloquent event
     */
    public function deleted($model)
    {
        $this->logModelEvent($model, 'deleted', 'info', false);
    }

    //TODO
    //custom events

    /*
     * Sets up variables to log event
     */
    private function logModelEvent($model, $event, $log_type, $with_data){
        $class = get_class($model);
        $reflection = new ReflectionClass($class);

        if($reflection->hasProperty('loggable')){
            $property = $reflection->getProperty('loggable');
            $property->setAccessible('true');
            $loggable_attributes = $property->getValue(new $class);
        }

        $current_user = Auth::user();
        $current_user_id = $current_user->{$current_user->getKeyName()} ?? null;

        if($with_data && config('laravel_logger.with_data')){
            if(isset($loggable_attributes)){
                $attributes = $model->only($loggable_attributes);
            }else{
                $attributes = $model->setHidden($model->getHidden())->attributesToArray();
            }
            $attributes = json_encode($attributes);
        }

        $data = [];
        //TODO 
        //sanitize data
        static::storeEvent($data);
    }

    //TODO
    //create Event
    private static function storeEvent($data){
        Event::create($data);
    }
}
