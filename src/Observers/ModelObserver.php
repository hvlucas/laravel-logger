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
    protected $with_user;

    public function __construct($events, $attributes, $with_user)
    {
        $this->events = $events;
        $this->attributes = $attributes;
        $this->with_user = $with_user;
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
    private function logModelEvent($model, $event){
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
        if($this->with_user){
            $current_user = Auth::user();
            $current_user_id = $current_user->{$current_user->getKeyName()} ?? null;
        }

        $attributes = null;
        if(isset($loggable_attributes)){
            $attributes = $model->only($loggable_attributes);
            $attributes = json_encode($attributes);
        }

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
