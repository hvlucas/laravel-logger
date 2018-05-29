<?php

namespace App\Observers;

use ReflectionClass;
use Illuminate\Support\Facades\Auth;
use HVLucas\LaravelLogger\Event;

class ModelObserver
{

    // Events that are going to be logged
    protected $events;

    // Model attributes that are going to be logged
    protected $attributes;

    // Flag to log Authenticated user (or not)
    protected $log_user;

    public function __construct($events, $attributes, $log_user)
    {
        $this->events = $events;
        $this->attributes = $attributes;
        $this->log_user = $log_user;
    }

    /*
     * Log created eloquent event
     */
    public function created($model)
    {
        $this->logModelEvent($model, 'created');
    }

    /*
     * Log updated eloquent event
     */
    public function updated($model)
    {
        $this->logModelEvent($model, 'updated');
    }

    /*
     * Log deleted eloquent event
     */
    public function deleted($model)
    {
        $this->logModelEvent($model, 'deleted');
    }

    /*
     * Log retrieved eloquent event
     */
    public function retrieved($model)
    {
        $this->logModelEvent($model, 'retrieved');
    }



    //TODO
    //custom events

    /*
     * Sets up variables to log event
     */
    private function logModelEvent($model, $event)
    {
        if(array_search($event, $this->events) === false){
            return;
        }

        $class = get_class($model);
        $reflection = new ReflectionClass($class);

        if($this->attributes){
            $loggable_attributes = $this->attributes;
        }elseif($reflection->hasProperty('loggable')){
            $property = $reflection->getProperty('loggable');
            $property->setAccessible('true');
            $loggable_attributes = $property->getValue(new $class);
        }

        $current_user_id = null;
        if($this->log_user){
            $current_user = Auth::user();
            $current_user_id = $current_user->{$current_user->getKeyName()} ?? null;
        }

        $attributes = null;
        if(isset($loggable_attributes)){
            $attributes = $model->only($loggable_attributes);
            $attributes = json_encode($attributes);
        }

        $data = [
            'activity' => $event,
            'model_id' => (string) $model->id,
            'model_name' => $class,
            'model_attributes' => $attributes,
            'created_at' => time(),
            'user_id' => $current_user_id,
        ];

        static::storeEvent($data);
    }

    private static function storeEvent($data)
    {
        //TODO
        //validate Event
        Event::create($data);
    }
}
