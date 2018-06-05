<div class="row">
@foreach($history as $event)
    <div class="col-lg">
        <div class="event-history-point">
            {{-- 

                TODO 
                style this b   

              --}}
            @foreach($event->model_attributes as $attribute => $value)
                {{$attribute}} : {{$value}}
            @endforeach
        </div>
    </div>
@endforeach
