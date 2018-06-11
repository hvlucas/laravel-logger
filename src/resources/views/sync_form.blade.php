<div class="row">
    <div class="col">
        <p class="sync-info">Syncing {{$class_name}} of id {{$id}} to {{$event}} event which happened in {{$happened}}</p>
    </div>
</div>
<div class="row">
    <div class="col">
        {{--
            TODO
            style/format this
          --}}
        @foreach($attributes as $attribute)
            {{$attribute['column']}}: {{$attribute['old']}} => {{$attribute['new']}}
        @endforeach
    </div>
</div>
