<li class="nav-item">
    <a class="nav-link {{$active}}" id="{{$id}}-tab" data-toggle="tab" href="#{{$id}}" role="tab" aria-controls="{{$id}}" aria-selected="{{$selected}}">{{$slot}} <span class="event-count">({{$count}})</span>@if($favorite)<i class="fas fa-star favorite"></i>@endif</a>
</li>
