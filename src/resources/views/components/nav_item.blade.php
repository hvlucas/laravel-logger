<li class="nav-item">
    <a class="nav-link {{$active}}" id="{{$id}}-tab" data-model="{{$parsed_class_name}}" data-toggle="tab" href="#{{$id}}" role="tab" aria-controls="{{$id}}" aria-selected="{{$selected}}">
        <i style="display: none;" class="fas fa-archive archive-icon"></i>
        {{$slot}} 
        <span class="event-count">({{$count}})</span>
        @if($favorite)
            <i class="fas fa-star favorite"></i>
        @endif
        <i class="nav-item-menu far fa-ellipsis-h" data-fa-mask="fas fa-comment" style="display: none;"></i>
        <ul class="fa-ul nav-item-menu-dropdown" style="display: none;">
            <li class="select-all">
                <span class="fa-li"><i class="fal fa-hand-pointer" aria-hidden="true"></i></span>
                <span class="select-text">Select</span> all events (<span class="visible-events">50</span>)
            </li>
            <li class="archive-selected">
                <span class="fa-li"><i class="fal fa-archive" aria-hidden="true"></i></span>
                <span class="archive-selected-text">Archive selected events</span> (<span class="events-selected">0</span>)
            </li>
            <li class="view-archived">
                <span class="fa-li"><i class="fal fa-box-open" aria-hidden="true"></i></span>
                <span class="archive-text">View archived events</span>
            </li>
            <li class="export-events">
                <span class="fa-li"><i class="fal fa-file-excel" aria-hidden="true"></i></span>
                Export events to CSV
            </li>
        </ul>
    </a>
</li>
