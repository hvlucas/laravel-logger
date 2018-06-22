<?php

namespace HVLucas\LaravelLogger\App\Http\Middleware;

use Closure;
use HVLucas\LaravelLogger\Facades\LaravelLogger;

class LogEvent
{
    // Fetches params from request and verifies model exists in the data-set of LaravelLoggerTracker
    public function handle($request, Closure $next, $event='retrieved')
    {
        $parameters = collect($request->route()->parameters())->values();
        foreach($request->route()->signatureParameters() as $param){
            $reflection = $param->getClass();
            $model_name = $reflection->name;
            $model = LaravelLogger::getModel($model_name);
            if($model){
                $instance = $parameters[$param->getPosition()];
                $model->logModelEvent($instance, $event);
            }
        }
        return $next($request);
    }
}
