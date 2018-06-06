<?php

namespace HVLucas\LaravelLogger\App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
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
        $models = LaravelLogger::getModelCollection()->sortByDesc(function($model){
            return $model->getIsFavorite();
        });
        $models = $models->map(function($model){
            $model_events = Event::where('activity', '!=', 'startpoint')->where('model_name', $model->getClassName())->orderBy('created_at', 'desc')->get();
            return [ 'model' => LaravelLogger::getModel($model->getClassName()), 'events' => $model_events];
        });
        return view('laravel_logger::index', compact('models'));
    }

    // Fectches history of model instance
    public function modelHistory(Request $request)
    {
        $e = new Event;
        $rules = [ 'event_id' => 'required|exists:'.$e->getTable().',id' ];


        $modal = -1;
        if(Validator::make($request->all(), $rules)->passes()){
            $event = Event::find($request->event_id);
            $history = Event::where(['model_name' => $event->model_name, 'model_id' => $event->model_id])->where('activity', '!=', 'created')->orderBy('created_at')->get();
            $attributes = array_keys($history->first()->model_attributes ?? []);

            //make a slider that shows history points 
            //in case the model is recent we need to convert from days to hours)
            $minimizer = 60*60*24;
            $differential = 0;
            $level = 0;
            $deepness = [ 24, 60 ];
            while(isset($deepness[$level]) && $differential < 1){
                $minimizer /= $deepness[$level];
                $startpoint = $history->first()->created_at->getTimestamp()/$minimizer - 1;
                $endpoint = time()/$minimizer + 1;
                $differential = ($endpoint-$startpoint)/10;
                ++$level;
            }
            $event_timestamps = json_encode($history->map(function($h) use ($differential, $minimizer){
                return [
                    'start' => $h->created_at->getTimestamp()/$minimizer,
                    'end' => $h->created_at->getTimestamp()/$minimizer + $differential,
                    'class' => $h->activity
                ];
            })->all());
            $history = view('laravel_logger::history', compact('history', 'attributes', 'event_timestamps', 'startpoint', 'endpoint'))->render();
            $modal = view('laravel_logger::components.modal', ['slot' => $history])->render();        
        }
        return response()->json($modal);
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
