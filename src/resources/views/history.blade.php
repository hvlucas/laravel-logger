<div class="row">
    <div class="col-lg">
        <div class="history-filter">
            <input id="history-slider" type="text"
                 data-slider-id="history-slider"
                 data-slider-min="{{$startpoint}}"
                 data-slider-max="{{$endpoint}}"
                 data-slider-step="1"
                 data-slider-value="{{$startpoint}}"
                 data-slider-rangeHighlights='{{$event_timestamps}}'
                 data-event-id="{{$event_id}}"
                 data-minimizer="{{$minimizer}}"/>
        </div>
        <div class="history-container">
            @include('laravel_logger::history_table')
        </div>
    </div>
</div>
