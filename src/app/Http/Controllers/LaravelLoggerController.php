<?php

namespace HVLucas\LaravelLogger\App\Http\Controllers;

use Illuminate\Routing\Controller;
use HVLucas\LaravelLogger\App\Event;
use HVLucas\LaravelLogger\Facades\LaravelLogger;

abstract class LaravelLoggerController extends Controller
{
    // TODO
    // Filter by individual models
    // Filter by user
    // Filter by range
    // Export PDF/CSV
    public function list()
    {
        $models = LaravelLogger::getModelCollection()->sortBy('is_favorite', 'desc');
        $models = $models->map(function($model){
            $model_events = Event::where('activity', '!=', 'startpoint')->where('model_name', $model->getClassName())->orderBy('created_at', 'desc')->get();
            return [ 'model' => LaravelLogger::getModel($model->getClassName()), 'events' => $model_events];
        });
        return view('laravel_logger::index', compact('models'));
    }

    // TODO
    // View Model Instance history
    public function show(Event $event)
    {
    }

    // TODO
    // Soft delete Event
    public function destroy(Event $event)
    {
    }

    // TODO
    // Force delete Event
    public function forceDestroy(Event $soft_event)
    {
    }

    // TODO
    // Clear history and set starting point to current point in time
    public function setStartingPoint()
    {
    }
}
