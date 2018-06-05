<div class="row">
    <div class="col-lg event-container">
        <div class="event-history-attrs">
            @foreach($attributes as $attr)
                {{$attr}}
            @endforeach
        </div>
            @foreach($history as $event)
                <div class="event-history-values">
                    <tag class="{{$event->activity}}">{{$event->activity}}</tag>
                    @foreach($event->model_attributes as $value)
                        {{$value}}
                    @endforeach
                    @if(!$loop->last)
                        <i class="fas fa-arrow-alt-right pointer"></i>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
