<?php

namespace HVLucas\LaravelLogger\App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use HVLucas\LaravelLogger\App\Event;
use HVLucas\LaravelLogger\Facades\LaravelLogger;

abstract class LaravelLoggerController extends Controller
{

    static $scale_options = ['all time', 'past year', 'past month', 'past day'];
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
        $scale_options = json_encode(static::$scale_options);
        if(Validator::make($request->all(), $rules)->passes()){
            $history = $this->getHistory($request->all(), $event);
            $attributes = array_keys($history->first()->model_attributes ?? []);
            $this->setHistoryAttributes($history, $minimizer, $differential, $startpoint, $endpoint, $labels, $smallest_diff, $event_timestamps);
            // render history (information+slider+history table)
            $history_view = view('laravel_logger::history.show', compact('history', 'attributes', 'event_timestamps', 'startpoint', 'endpoint', 'minimizer', 'event', 'smallest_diff', 'labels', 'scale_options'))->render();
            // place history in a modal component
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
            'event_point' => 'sometimes|numeric', 
            'scale_filter' => 'sometimes|numeric',
            'minimizer' => 'required|numeric' 
        ];
        $history_table = -1;
        if(Validator::make($request->all(), $rules)->passes()){
            $history = $this->getHistory($request->all(), $event);
            $attributes = array_keys($history->first()->model_attributes ?? []);
            $minimizer = $request->minimizer;
            if(isset($request->scale_filter)){
                $view = 'laravel_logger::history.information';
                $this->setHistoryAttributes($history, $minimizer, $differential, $startpoint, $endpoint, $labels, $smallest_diff, $event_timestamps);
            }else{
                $view = 'laravel_logger::history.table';
            }
            $history_table = view($view, compact('history', 'attributes', 'minimizer', 'event', 'startpoint', 'endpoint', 'labels', 'smallest_diff', 'event_timestamps'))->render();
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
    private function getHistory($data, &$event)
    {
        $event = Event::find($data['event_id']);
        $history = Event::where(['model_name' => $event->model_name, 'model_id' => $event->model_id])->where('activity', '!=', 'created')->orderBy('created_at');
        $created_at = null;
        if(isset($data['event_point']) && isset($data['minimizer'])){
            $delimiter = $data['event_point'] * $data['minimizer'];
            $created_at = Carbon::createFromTimestamp($delimiter);
        }elseif(isset($data['scale_filter']) && isset(static::$scale_options[$data['scale_filter']])){
            $selected = static::$scale_options[$data['scale_filter']]; 
            switch($selected){
                case 'past year':
                    $created_at = new Carbon('last year');
                    break;
                case 'past month':
                    $created_at = new Carbon('last month');
                    break;
                case 'past day':
                    $created_at = new Carbon('last day');
                    break;
            }
        }

        if($created_at){
            $history->where('created_at', '>=', $created_at);
        }
        return $history->get();
    }

    // Set variables for history view
    private function setHistoryAttributes($history, &$minimizer, &$differential, &$startpoint, &$endpoint, &$labels, &$smallest_diff, &$event_timestamps)
    {
        //make a slider that shows history points 
        //in case the model is recent we need to convert from days to hours or minutes)
        $minimizer = 60*60*24;
        $differential = 0;
        $level = 0;
        $deepness = [ 24, 60, 60 ];
        $first = $history->first();
        $last = $history->last();
        while(isset($deepness[$level]) && $differential < 1){
            $minimizer /= $deepness[$level];
            $startpoint = $first->created_at->getTimestamp()/$minimizer;
            $endpoint = $last->created_at->getTimestamp()/$minimizer;
            $differential = ($endpoint-$startpoint)/40;
            ++$level;
        }

        // label the time scale
        $diff = $last->created_at->diffInSeconds($first->created_at);
        $split = $diff/4;
        $labels = collect([]);
        foreach(range(0, 4) as $index){
            $label = $first->created_at->copy()->addSeconds($split*$index);
            //months vary days, so base it on that
            $month = 86400 * (int) $last->created_at->endOfMonth()->format('d');
            $day = 86400;

            if($diff <= $day){
                $format = 'H:i:s a';
            }elseif($diff <= $month){
                $format = 'F jS';
            }else{
                $format = 'F Y';
            }

            $labels[] = $label->format($format);
        }
        // don't repeat labels
        $labels = json_encode($labels->unique()->values()->all());

        $smallest_diff = 1;
        $comp = $startpoint;
        $event_timestamps = json_encode($history->map(function($h) use ($differential, $minimizer, $endpoint, &$comp, &$smallest_diff){
            $end = $h->created_at->getTimestamp()/$minimizer + $differential;
            //endpoint can sometimes outscale the slider
            if($end > $endpoint){
                $end  = $endpoint - $differential;
            }

            //slider distance it travels when sliding
            if(abs($end - $comp) < $smallest_diff){
                $smallest_diff = abs($end-$comp);
            }
            $comp = $end;

            return [
                'start' => $h->created_at->getTimestamp()/$minimizer,
                'end' => $end,
                'class' => $h->activity
            ];
        })->all());

        // can't slide if it's 0
        if($smallest_diff == 0){
            $smallest_diff = 0.1;
        }
    }
}
