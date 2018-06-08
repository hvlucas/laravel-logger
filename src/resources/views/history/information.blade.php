<div class="history-container">
    <div class="history-filter">
        <input id="history-slider" type="text"
             data-slider-id="history-slider"
             data-slider-min="{{$startpoint}}"
             data-slider-max="{{$endpoint}}"
             data-slider-step="{{$smallest_diff}}"
             data-slider-value="{{$startpoint}}"
             data-slider-rangeHighlights='{{$event_timestamps}}'
             data-event-id="{{$event->id}}"
             data-minimizer="{{$minimizer}}"
             data-slider-ticks-labels='{{$labels}}'
             data-slider-tooltip="hide" 
             />
    </div>
    <div class="history-table">
        @include('laravel_logger::history.table')
    </div>
</div>
