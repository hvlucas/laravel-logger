@php
    $split = (int) ceil(count($attributes)/2);
@endphp
<table class="history table-responsive">
    <thead>

        <tr>
            <th><ts>{{$history->first()->created_at->format('e')}}</ts></th>
            @foreach($history as $event)
                <th>
                    @if($loop->iteration%2)
                        <ts>{{$event->created_at->format('F jS, Y @ H:i:s')}}</ts>
                    @endif
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($attributes as $attr)
            <tr>
                <td class="attribute-name"><tag>{{$attr}}</tag></td>
                @foreach($history as $event)
                    @php
                        $value = $event->model_attributes[$attr] ?? null;
                    @endphp
                    @if(!$loop->first)
                        <td class="column-split"> 
                            @if($loop->parent->iteration == $split) 
                                <i class="fas fa-arrow-alt-right"></i> 
                                <ts data-toggle="tooltip" title="{{$event->created_at->format('F jS, Y @ H:i:s (e)')}}">{{$event->user_name}} {{$event->activity}} {{$event->created_at->diffForHumans()}}</ts>
                            @endif
                        </td>
                    @endif
                    <td>{{$value}}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
