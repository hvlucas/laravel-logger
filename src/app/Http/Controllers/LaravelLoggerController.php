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
        $model_events = Event::orderBy('created_at', 'desc')->get()->groupBy('model_name');
        $events = $model_events->mapWithKeys(function($models, $class_name){
            return [LaravelLogger::getModel($class_name) => $models];
        });
        return view('laravel_logger::index', compact('events'));
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
