<?php

namespace HVLucas\LaravelLogger\App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use DateTime;
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
            $history = $this->getHistory($request->all());
            $event_id = $request->event_id;
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
                $differential = ($endpoint-$startpoint)/40;
                ++$level;
            }
            $event_timestamps = json_encode($history->map(function($h) use ($differential, $minimizer, $endpoint){
                $end = $h->created_at->getTimestamp()/$minimizer + $differential;
                if($end > $endpoint){
                    $end  = $endpoint - $differential;
                }
                return [
                    'start' => $h->created_at->getTimestamp()/$minimizer,
                    'end' => $end,
                    'class' => $h->activity
                ];
            })->all());

            $history_view = view('laravel_logger::history', compact('history', 'attributes', 'event_timestamps', 'startpoint', 'endpoint', 'minimizer', 'event_id'))->render();
            $modal = view('laravel_logger::components.modal', ['slot' => $history_view])->render();        
        }
        return response()->json($modal);
    }

    // Filter history based on slider data passed
    public function filterHistory(Request $request)
    {
        $e = new Event;
        $rules = [ 
            'event_id' => 'required|exists:'.$e->getTable().',id', 
            'event_point' => 'required|numeric', 
            'minimizer' => 'required|numeric' 
        ];
        $history_table = -1;
        if(Validator::make($request->all(), $rules)->passes()){
            $history = $this->getHistory($request->all());
            $attributes = array_keys($history->first()->model_attributes ?? []);
            $minimizer = $request->minimizer;
            $history_table = view('laravel_logger::history_table', compact('history', 'attributes', 'minimizer'))->render();
        }
        return response()->json($history_table);
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

    // Filter and return history based on data given  
    private function getHistory($data)
    {
        $event = Event::find($data['event_id']);
        $history = Event::where(['model_name' => $event->model_name, 'model_id' => $event->model_id])->where('activity', '!=', 'created')->orderBy('created_at');
        //dd($history->pluck('created_at'));
        if(isset($data['event_point']) && isset($data['minimizer'])){
            $delimiter = $data['event_point'] * $data['minimizer'];
            $created_at = new DateTime;
            $created_at->setTimestamp($delimiter);
            $history->where('created_at', '>=', $created_at);
        }
        return $history->get();
    }
}
