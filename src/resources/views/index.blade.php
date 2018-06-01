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
                    $class_break = explode('\\', $class_name);
                    $no_namespace       = end($class_break);
                    $lower_no_namespace = strtolower($no_namespace);
                    $id = $aria         = $lower_no_namespace;
                    $link_text          = $class_name;
                    $selected           = $loop->first ? 'true' : 'false';
                    $active = $loop->first ? 'active' : '';
                    $count              = $events->count();
                @endphp

                @component('laravel_logger::components.nav_item', compact('id', 'aria', 'link_text', 'selected', 'count', 'active'))
                    {{$no_namespace}}
                @endcomponent
            @endforeach
        </ul>
        <div class="tab-content" id="myTabContent">
            @foreach($models as $model_event)
                @php
                    $model              = $model_event['model'];
                    $class_name         = $model->getClassName();
                    $class_break = explode('\\', $class_name);
                    $no_namespace       = end($class_break);
                    $id = strtolower($no_namespace);
                    $tab_classes = $loop->first ? 'show active' : '';
                @endphp

                @component('laravel_logger::components.tab', compact('tab_classes', 'id'))
                    <table class="events">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Event</th>
                                <th>User Responsible</th>
                                <th>Session ID</th>
                                <th>IP Address</th>
                                <th>URL Request</th>
                                <th>When</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($model_event['events'] as $event)
                            <tr>
                                <td>{{$event->model_id}}</td>
                                <td>{{$event->activity}}</td>
                                <td>{{$event->user_id}}</td>
                                <td>{{$event->session_id}}</td>
                                <td>{{$event->ip_address}}</td>
                                <td>{{$event->url}}</td>
                                <td>{{$event->created_at->diffForHumans()}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endcomponent
            @endforeach
        </div>
    </div>
@endsection

