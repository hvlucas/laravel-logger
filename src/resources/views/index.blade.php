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
                    <table cellspacing="0" class="events">
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
                            @foreach($model_event['events'] as $event)
                            <tr>
                                <td>
                                    {{-- show model history using bootstrap modals --}}
                                    <a data-toggle="tooltip" title="Open model history" href="javascript:void(0)" class="open-model-history" data-event-id="{{$event->id}}">{{$event->model_id}}</a>
                                </td>
                                <td>
                                    <tag class="{{$event->activity}}">{{$event->activity}}</tag>
                                </td>
                                @if($model->isTrackingAuthenticatedUser())
                                    <td>
                                        <tag class="user">{{$event->user_name ?: 'UnAuthenticated'}}</tag>
                                    </td>
                                @else
                                    <td></td>
                                @endif
                                @if($event->ip_address)
                                    <td>
                                        <a target="_blank" href="https://ipinfo.io/{{$event->ip_address}}">{{$event->ip_address}}</a>
                                    </td>
                                @else
                                    <td></td>
                                @endif
                                <td>
                                    <i class="{{$event->fa_browser}}"></i> <sub>{{$event->parsed_version}}</sub> <i class="{{$event->fa_os}}"></i>
                                </td>
                                <td>
                                    <tag class="{{strtolower($event->method)}} method">{{$event->method}}</tag> {{$event->parsed_url}}
                                </td>
                                <td>
                                    {{$event->created_at->diffForHumans()}}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endcomponent
            @endforeach
        </div>
    </div>
@endsection
