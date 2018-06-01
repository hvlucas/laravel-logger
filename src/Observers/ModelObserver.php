<?php

namespace HVLucas\LaravelLogger\Observers;

use ReflectionClass;
use DateTime;
use Illuminate\Support\Facades\Auth;
use HVLucas\LaravelLogger\App\Event;
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

    // Sets up variables to log event
    private function logModelEvent($model_tracked, $event): void
    {
        $tracker = LaravelLogger::getTracker();
        if($tracker->isTracking()){
            return;
        }

        // Flag tracking to true
        $tracker->setTracking(true);

        $model = $tracker->getModel(get_class($model_tracked));
        if($model->isTrackingEvent($event)){
            $attributes = [];
            if($model->isTrackingData()){
                $attributes = $model->getAttributeValues($model_tracked);
            }

            $current_user_id = null;
            if($model->isTrackingAuthenticatedUser()){
                $current_user_id = $tracker->getUserId();
            }

            $created_at = new DateTime;
            $created_at->setTimestamp(time());
            $data = [
                'activity' => $event,
                'model_id' => (string) $model_tracked->{$model_tracked->getKeyName()},
                'model_name' => get_class($model_tracked),
                'model_attributes' => $attributes,
                'created_at' => $created_at,
                'user_id' => $current_user_id,
                'user_agent' => $tracker->getUserAgent(),
                'full_url' => $tracker->getFullUrl(),
                'ajax' => $tracker->getAjax(),
                'session_id' => $tracker->getSessionId()
            ];

            static::storeEvent($data);
        }

        $tracker->setTracking(false);
    }

    private static function storeEvent($data)
    {
        //TODO
        //validate Event
        Event::create($data);
    }
}
