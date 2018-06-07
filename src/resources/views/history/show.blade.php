<div class="row">
    <div class="col-lg">
        <div class="history-scale">
            <input id="scale-slider" type="text"
                 data-provide="slider"
                 data-slider-ticks="[0, 1, 2, 3]"
                 data-slider-ticks-labels='{{$scale_options}}'
                 data-slider-min="0"
                 data-slider-max="3"
                 data-slider-step="1"
                 data-slider-value="0"
                 data-event-id="{{$event->id}}"
                 data-minimizer="{{$minimizer}}"
                 data-slider-tooltip="hide" 
                 />
        </div>
        @include('laravel_logger::history.information')
    </div>
</div>
