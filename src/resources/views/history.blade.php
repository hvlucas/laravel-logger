<div class="row">
    <div class="col-lg">
        <div class="history-info">
            @php
                $updated = $history->where('activity', 'updated')->count();
                $deleted = $history->where('activity', 'deleted')->count();
                $restored = $history->where('activity', 'restored')->count();
            @endphp
            {{-- 
                TODO
                style this
              --}}
            <p>Model: {{$event->model_name}}</p>
            <p>Model ID: {{$event->model_id}}</p>
            <p>updated: {{$updated}}</p>
            <p>deleted: {{$deleted}}</p>
            <p>restored: {{$restored}}</p>
        </div>
        <div class="history-filter">
            <input id="history-slider" type="text"
                 data-slider-id="history-slider"
                 data-slider-min="{{$startpoint}}"
                 data-slider-max="{{$endpoint}}"
                 data-slider-step="{{$smallest_diff}}"
                 data-slider-value="{{$startpoint}}"
                 data-slider-rangeHighlights='{{$event_timestamps}}'
                 data-event-id="{{$event->id}}"
                 data-minimizer="{{$minimizer}}"/>
        </div>
        <div class="history-container">
            @include('laravel_logger::history_table')
        </div>
    </div>
</div>

