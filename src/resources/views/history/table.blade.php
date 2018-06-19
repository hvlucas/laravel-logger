@php
    $split = (int) ceil(count($attributes)/2);
    $timezone = null;
    if($history->count() > 0){
        $timezone = $history->first()->created_at->format('e');
        $previous_event = $history->first()->previous;
        if($previous_event){
            $history->prepend($previous_event);
        }
    }
@endphp
<input type="hidden" data-model-id="{{$event->model_id}}"/>
<div class="table-container">
    <i class="fal fa-angle-left modal-scroll fa-3x" style="display:none;" data-direction="left"></i>
    <table class="history table-responsive">
        <thead>
            <tr>
                <th><ts>{{$timezone}}</ts></th>
                @foreach($history as $model_event)
                    <th>
                        <ts>{{$model_event->created_at->format('F jS, Y @ H:i:s')}}</ts> <i data-toggle="tooltip" title="Sync up {{$model_event->model_name}} {{$model_event->model_id}} to this point" data-event-id="{{$model_event->id}}" class="sync-model fal fa-sync-alt fa-xs"></i>
                    </th>
                    <th></th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($attributes as $attr)
                <tr>
                    <td class="attribute-name"><tag>{{$attr}}</tag></td>
                    @foreach($history as $model_event)
                        @php
                            $value = $model_event->model_attributes[$attr] ?? null;
                            if(is_array($value)){
                                $value = json_encode($value, true);
                            }
                        @endphp
                        @if(!$loop->first)
                            <td class="column-split"> 
                                @if($loop->parent->iteration == $split) 
                                    <i class="fas fa-arrow-alt-right"></i> 
                                    <ts data-toggle="tooltip" title="{{$model_event->created_at->format('F jS, Y @ H:i:s (e)')}}">
                                        @if($model_event->getModel()->isTrackingAuthenticatedUser()) 
                                            {{$model_event->user_name ?? 'UnAuthenticated'}} 
                                        @endif 
                                        {{$model_event->activity}} 
                                        {{$model_event->created_at->diffForHumans()}}
                                    </ts>
                                @endif
                            </td>
                        @endif
                        <td>{{$value}}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
    <i class="fal fa-angle-right modal-scroll fa-3x" @if($history->count() == 0) style="display:none;" @endif data-direction="right"></i>
</div>
