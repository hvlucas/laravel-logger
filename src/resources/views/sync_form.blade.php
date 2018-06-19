<div class="row event-sync" id="event_{{$event->id}}">
    <div class="col">
        {{-- 
            TODO
            Finish styling/formatting this
          --}}
        <p class="sync-info">Syncing {{$class_name}} of id {{$id}} to <tag class="{{$event->activity}}">{{$event->activity}}</tag> event which happened in {{$event->created_at->format('F jS, Y @ H:m:s T')}}.</p>
        <p>Please make sure data below is accurate before proceeding. If there are fields you do not wish to update, please uncheck the corresponding checkboxes.</p>
        <p>In case you have any questions about configuration, check the <a target="_blank" href="https://github.com/hvlucas/laravel-logger">documentation.</a></p>
    </div>
</div>
<div class="row">
    <div class="col">
        <form id="sync-form" accept-charset="UTF-8">
            {{ csrf_field() }}
            <table id="syncing-model">
                <thead>
                    <th>Attribute</th>
                    <th>From</th>
                    <th></th>
                    <th>To</th>
                    <th>Update?</th>
                </thead>
                <tbody>
                    @foreach($attributes as $attribute)
                        <tr>
                            <td>{{$attribute['column']}}</td>
                            <td>{{is_array($attribute['old']) ? json_encode($attribute['old']) : $attribute['old']}}</td>
                            <td><i class="fas fa-caret-right"></i></td>
                            <td>{{is_array($attribute['new']) ? json_encode($attribute['new']) : $attribute['new']}}</td>
                            <td><input type="checkbox" name="save[{{$attribute['column']}}]" checked="checked" value="true"/>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <input type="hidden" name="sync_event_id" value="{{$event->id}}"/>
            <input type="hidden" name="model_id" value="{{$id}}"/>
            <input type="submit" class="btn btn-primary" value="Sync"/>
        </form>
    </div>
</div>
