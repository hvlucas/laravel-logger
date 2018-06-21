<?php

namespace HVLucas\LaravelLogger\Observers;

use ReflectionClass;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use HVLucas\LaravelLogger\App\Event;
use HVLucas\LaravelLogger\Facades\LaravelLogger;

class ModelObserver
{
    // Log created eloquent event
    public function created($model)
    {
        LaravelLogger::getModel(get_class($model))->setStartingPoint();
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

    // Sets up variables to log event
    private function logModelEvent($model_tracked, $event): void
    {
        $tracker = LaravelLogger::getTracker();
        $model = $tracker->getModel(get_class($model_tracked));
        $current_user_id = null;
        if($model->isTrackingAuthenticatedUser()){
            $current_user_id = $tracker->getUserId();
        }

        // don't log event when current user is null and only_when_authenticated is true
        if($model->getOnlyWhenAuthenticated() && $current_user_id === null){
            return;
        }

        $attributes = $sync_attributes = json_encode([]);
        if($model->isTrackingData()){
            $attributes = $model->getAttributeValues($model_tracked, false);
            $sync_attributes = $model->getAttributeValues($model_tracked, true);
        }


        Event::store([
            'activity' => $event,
            'model_id' => (string) $model_tracked->{$model_tracked->getKeyName()},
            'model_name' => get_class($model_tracked),
            'model_attributes' => $attributes,
            'sync_attributes' => $sync_attributes,
            'created_at' => new Carbon,
            'user_id' => $current_user_id,
            'user_agent' => $tracker->getUserAgent(),
            'ip_address' => $tracker->getIp(),
            'full_url' => $tracker->getFullUrl(),
            'method' => $tracker->getMethod()
        ]);
    }
}
