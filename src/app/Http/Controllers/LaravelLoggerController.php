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

    static $scale_options = ['all time', 'past year', 'past month', 'past week', 'past day'];

    // Render view of model index
    public function list()
    {
        $models = LaravelLogger::getModelCollection()->sortByDesc(function($model){
            return $model->getIsFavorite();
        });
        $models = $models->map(function($model){
            return [ 'model' => LaravelLogger::getModel($model->getClassName()), 'events' => collect([])];
        });
        $show_options = [50 => 50, 150 => 150, 300 => 300, 500 => 500, null => 'All'];
        return view('laravel_logger::index', compact('models', 'show_options'));
    }

    // TODO
    // Filter by range
    // Export PDF/CSV
    // Returns list of events based on model given by ajax request; returning json with views to render on dom by main.js
    public function filterEvents(Request $request)
    {
        $model = LaravelLogger::getModel($request->model);
        if($model == null){
            return response()->json([]);
        }

        // Set default query
        $query = [
            'start' => 0,
            'length' => 50,
        ];

        // Join with user table to pull name
        $event = new Event;
        $events_table = $event->getTable();
        $user_table = LaravelLogger::getUserTable();
        $user_table_pk = LaravelLogger::getUserInstance()->getKeyName();
        $user_column = LaravelLogger::getUserColumn() ?: 'id';

        //Possibly in the future  we can check if user column was set to something 
        //if it wasn't then we don't need to join and we can just filter the query by user_id xD
        $model_events = Event::join($user_table, "$events_table.user_id", '=', "$user_table.$user_table_pk")
            ->select("$events_table.*", "$user_table.$user_column as user_name")
            ->where('activity', '!=', 'startpoint')
            ->where('model_name', $model->getClassName());


        // Set slice variables
        if(is_numeric($request->start)){
            $query['start'] = $request->start < 0 ? 0 : $request->start;
        }
        if(is_numeric($request->length)){
            $query['length'] = $request->length < 0 ? $model_events->count() : $request->length;
        }

        // Get tags available before narroing query with where's
        $events = clone $model_events;
        $event_tags = $events->pluck('activity')->filter()->unique()->map(function($activity){
            return view('laravel_logger::components.tag', ['class' => $activity, 'filter' => 'activity', 'slot' => $activity])->render();
        })->values()->all();

        $method_tags = $events->pluck('method')->filter()->unique()->map(function($method){
            return view('laravel_logger::components.tag', ['class' => strtolower($method), 'filter' => 'method', 'slot' => $method])->render();
        })->values()->all();

        $auth_user_tags = collect([]);
        $events->each(function($event) use (&$auth_user_tags) {
            $auth_user_tags[] = $event->user_name ?: 'UnAuthenticated';
        });
        $auth_user_tags = $auth_user_tags->filter()->unique()->map(function($user){
            return view('laravel_logger::components.tag', ['class' => 'user', 'filter' => 'user_id', 'slot' => $user])->render();
        })->values()->all();

        // merge'em together for view
        $tags = array_merge($event_tags, $auth_user_tags, $method_tags);

        // Filter query by tags selected in the front-end
        $searchable_tags = [
            'method',
            'user_id',
            'activity',
        ];

        // Filter query by tags and search input field
        $model_events->where(function($query) use ($request, $searchable_tags, $user_table, $user_column){
            foreach((array) $request->tags as $tag){
                if( !isset($tag['filter']) || 
                    !isset($tag['value']) || 
                    array_search($tag['filter'], $searchable_tags) === false){
                    continue;
                }

                // Special case: we set 'null' to 'UnAunthenticated' when sending tags to view
                if($tag['filter'] == 'user_name' && $tag['value'] == 'UnAuthenticated'){
                    $tag['value'] = null;
                }
                $query->orWhere($tag['filter'], $tag['value']);
            }

            if(isset($request->search['value'])){
                $search = $request->search['value'];
                $query->where('model_id', 'LIKE', "%$search%")
                    ->orWhere('activity', 'LIKE', "%$search%")
                    ->orWhere('user_id', 'LIKE', "%$search%")
                    ->orWhere("$user_table.$user_column", 'LIKE', "%$search%")
                    ->orWhere('ip_address', 'LIKE', "%$search%")
                    ->orWhere('full_url', 'LIKE', "%$search%")
                    ->orWhere('method', 'LIKE', "%$search%");
            }
        });

        // Get total of events that are going to be displayed
        $total = $model_events->count();

        $columns = [
            0 => 'model_id',
            1 => 'activity',
            2 => 'user_id',
            3 => 'ip_address',
            5 => 'full_url', // the key is supposed to be 5 don't yell at me
            6 => 'created_at',
        ];

        // Server side ordering
        if($request->order && is_array($request->order)){
            foreach($request->order as $index => $order){
                $index = $order['column'] ?? null;
                if(!isset($columns[$index])){
                    continue;
                }
                $sortBy = $columns[$index];
                $order = $order['dir'];
                $model_events->orderBy($sortBy, $order);
            }
        }else{
            $model_events->orderByDesc('created_at');
        }

        // Paginate so we don't take 20 years to load pages
        $model_events = $model_events->get()->slice($query['start'], $query['length'])->values()->all();

        return response()->json([
			'draw' => (int) $request->draw,
			'recordsTotal' => count($model_events),
            'recordsFiltered' => $total,
            'data' => $model_events,
            'tags' => $tags,
        ]);
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

    // Return sync form
    public function syncForm(Request $request)
    {
        $e = new Event;
        $rules = [
            'model_id' => 'required',
            'sync_event_id' => 'required|exists:'.$e->getTable().',id'
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function($validator) use($request){
            $event = Event::find($request->sync_event_id);
            $model = new $event->model_name;
            if(Validator::make($request->all(), ['model_id' => 'required|exists:'.$model->getTable().','.$model->getKeyName()])->fails()){
                $validator->errors()->add('model_id', 'Does not exists');
            }
        });

        $sync_form = -1;
        if($validator->passes()){
            $event = Event::find($request->sync_event_id);
            $event_attributes = $event->model_attributes;
            $instance = $event->model_name::find($request->model_id);
            $class_name = get_class($instance);
            $id = $instance->{$instance->getKeyName()};
            $model = LaravelLogger::getModel($class_name);
            $current_attributes = json_decode($model->getAttributeValues($instance), true);
            $attributes = array();
            foreach($current_attributes as $column => $attr){
                $attributes[] = ['column' => $column, 'old' => $attr, 'new' => $event_attributes[$column]??null];
            }

            $form = view('laravel_logger::sync_form', compact('attributes', 'class_name', 'id', 'event'));
            $sync_form = view('laravel_logger::components.modal', ['class' => 'sync-modal', 'slot' => $form])->render();
        }

        return response()->json($sync_form);
    }

    // Sync model to an event point in the model's timeline and creates a `sync` Event instance 
    // TODO
    // config option to set which attributes will be updated
    public function syncModel(Request $request)
    {
        $e = new Event;
        $rules = [
            'model_id' => 'required',
            'sync_event_id' => 'required|exists:'.$e->getTable().',id',
            'save' => 'required|array',
        ];
        // request comes from a serialized AJAX request, parse it then validate
        parse_str($request->sync_data, $data);
        $validator = Validator::make($data, $rules);
        $validator->after(function($validator) use($data){
            $event = Event::find($data['sync_event_id']);
            $model = new $event->model_name;
            if(Validator::make($data, ['model_id' => 'required|exists:'.$model->getTable().','.$model->getKeyName()])->fails()){
                $validator->errors()->add('model_id', 'Does not exists');
            }
        });

        $type = 'danger';
        $message = 'Something went wrong! If you believe this is a bug please open an issue here: https://github.com/hvlucas/laravel-logger/issues/new';

        if($validator->passes()){
            $tracker = LaravelLogger::getTracker();
            $event = Event::find($data['sync_event_id']);

            $event_attributes = collect($event->model_attributes)->only(array_keys($data['save']))->all();
            $model = LaravelLogger::getModel($event->model_name);
            $class_name = $model->getClassName();
            $new_instance = new $class_name;
            // updating using a 'where' query builder does not trigger Laravel Events
            // only issue comes when there are more than one instance with the same primary key; in which case we ignore bad design and update all of them out of spite
            $model_id = $data['model_id'];
            $event->model_name::where($new_instance->getKeyName(), $model_id)->update($event_attributes);

            Event::store([
                'activity' => 'sync',
                'model_id' => (string) $model_id,
                'model_name' => $class_name,
                'model_attributes' => json_encode($event_attributes),
                'created_at' => new Carbon,
                'user_id' => $tracker->getUserId(),
                'user_agent' => $tracker->getUserAgent(),
                'ip_address' => $tracker->getIp(),
                'full_url' => $tracker->getFullUrl(),
                'method' => $tracker->getMethod()
            ]);

            $type = 'success';
            $message = "Model $class_name of ID $model_id has been synced successfully!";
        }

        return response()->json(view('laravel_logger::components.alert', compact('type', 'message'))->render());
    }

    // Soft deletes an Event
    // TODO 
    // add front-end functionality for this
    public function destroy(Event $event)
    {
        $event->delete();
    }

    // Force delete an Event
    // TODO 
    // add front-end functionality for this
    public function forceDestroy(Event $soft_event)
    {
        $soft_event->forceDelete();
    }

    // Filters and returns history based on data given  
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
                case 'past week':
                    $created_at = new Carbon('last week');
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
        if($history->count() == 0){
            return;
        }
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
            $startpoint = ($first->created_at->getTimestamp()/$minimizer) - 1;
            $endpoint = ($last->created_at->getTimestamp()/$minimizer) + 1;
            $differential = ($endpoint-$startpoint)/100;
            ++$level;
        }

        // label the time scale with appropriate formatted date
        $diff = $last->created_at->diffInSeconds($first->created_at);
        $split = $diff/4;
        $labels = collect([]);
        foreach(range(0, 4) as $index){
            $label = $first->created_at->copy()->addSeconds($split*$index);
            // month/week/day in seconds (not accounting for variation in month days)
            $month = 2678400;
            $week = 604800;
            $day = 86400;

            if($diff <= $day){
                $format = 'F jS H:i:s a';
            }elseif($diff <= $week){
                $format = 'l H:i:s a';
            }elseif($diff <= $month){
                $format = 'F jS Y';
            }else{
                $format = 'F Y';
            }

            $labels[] = $label->format($format);
        }
        // don't repeat labels
        $labels = json_encode($labels->unique()->values()->all());

        $smallest_diff = 1;
        $comp = $startpoint;
        // iterate through each data point and set scaled timestamp
        $event_timestamps = json_encode($history->map(function($h) use ($differential, $minimizer, $endpoint, &$comp, &$smallest_diff){
            $end = $h->created_at->getTimestamp()/$minimizer + $differential;
            //endpoint can sometimes outscale the slider
            if($end > $endpoint){
                $end  = $endpoint - $differential;
            }

            // slider distance traveled when dragging mouse across scale
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
        if($smallest_diff <= 0){
            $smallest_diff = 0.1;
        }
    }
}
