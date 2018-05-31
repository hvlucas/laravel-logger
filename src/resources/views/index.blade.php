@extends('laravel_logger::app')
@section('laravel_logger')
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        @foreach($models as $model_event)
            @php
                $model              = $model_event['model'];
                $events             = $model_event['events'];

                $class_name         = $model->getClassName();
                $class_break = explode('\\', $class_name);
                $no_namespace       = end($class_break);
                $lower_no_namespace = strtolower($no_namespace);
                $id = $aria         = $lower_no_namespace;
                $link_text          = $class_name;
                $selected           = $loop->first ? 'true' : 'false';
                $count              = $events->count();
            @endphp

            @component('laravel_logger::components.nav_item', compact('id', 'aria', 'link_text', 'selected', 'count'))
                {{$no_namespace}}
            @endcomponent
        @endforeach
    </ul>
    <div class="tab-content" id="myTabContent">
        @foreach($models as $model_events)
            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                {{-- 
                    TODO 
                    Display events
                  --}}
                {{$model_events['events']->count()}}
            </div>
        @endforeach
    </div>
@endsection
