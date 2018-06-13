@extends('laravel_logger::app')
@section('laravel_logger')
    <div class="nav-container">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            @foreach($models as $model_event)
                @php
                    $model              = $model_event['model'];
                    $events             = $model_event['events'];

                    $model              = $model_event['model'];
                    $class_name         = $model->getClassName();
                    $class_break        = explode('\\', $class_name);
                    $no_namespace       = end($class_break);
                    $lower_no_namespace = strtolower($no_namespace);
                    $id                 = $lower_no_namespace;
                    $link_text          = $class_name;
                    $selected           = $loop->first ? 'true' : 'false';
                    $active             = $loop->first ? 'active' : '';
                    $count              = $events->count();
                    $favorite           = $model->getIsFavorite();
                @endphp

                @component('laravel_logger::components.nav_item', compact('id', 'aria', 'link_text', 'selected', 'count', 'active', 'favorite'))
                    {{$no_namespace}}
                @endcomponent
            @endforeach
        </ul>
        <div class="tab-content" id="myTabContent">
            @foreach($models as $model_event)
                @php
                    $model          = $model_event['model'];
                    $class_name     = $model->getClassName();
                    $class_break    = explode('\\', $class_name);
                    $no_namespace   = end($class_break);
                    $id             = strtolower($no_namespace);
                    $tab_classes    = $loop->first ? 'show active' : '';
                @endphp

                @component('laravel_logger::components.tab', compact('tab_classes', 'id'))
                    <table cellspacing="0" data-model="{{$class_name}}" class="events">
                        <thead>
                            <tr>
                                <th><i class="fal fa-database fa-sm"></i>ID</th>
                                <th><i class="fal fa-inbox fa-sm"></i>Event</th>
                                <th><i class="fal fa-user-tag fa-sm"></i>Authenticated User</th>
                                <th><i class="fal fa-globe fa-sm"></i>IP Address</th>
                                <th><i class="fal fa-tablet-android-alt fa-sm"></i>User Agent</th>
                                <th><i class="fal fa-shipping-fast fa-sm"></i>Request</th>
                                <th><i class="fal fa-shipping-timed fa-sm"></i>When</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                @endcomponent
            @endforeach
        </div>
    </div>
@endsection
