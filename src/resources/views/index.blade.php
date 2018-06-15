@extends('laravel_logger::app')
@section('laravel_logger')
    <div class="filter-container">
        @php
            $model = $models->first()['model'];
            $parsed_class_name = $model->getClassNameNoSlashes();
        @endphp
        <ul class="filtering-tags">
        </ul>
        <a id="clear-filter" href="#">clear</a>
        <div class="form-inline">
            <label for="show" class="mr-md-2">Show</label>
            <select id="show" class="form-control mr-md-3">
                @foreach($show_options as $value => $option)
                    <option value="{{$value}}">{{$option}}</option>
                @endforeach
            </select>
            <input class="form-control" data-model="{{$parsed_class_name}}" type="text" placeholder="Search..." id="search"/>
        </div>
        <ul class="searchable-tags">
        </ul>
    </div>
    <div class="nav-container">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            @foreach($models as $model_event)
                @php
                    $model              = $model_event['model'];
                    $events             = $model_event['events'];

                    $model              = $model_event['model'];
                    $class_name         = $model->getClassName();
                    $no_namespace       = $model->getClassNameNoNamespace();
                    $parsed_class_name  = $model->getClassNameNoSlashes();
                    $id                 = strtolower($no_namespace);
                    $link_text          = $class_name;
                    $selected           = $loop->first ? 'true' : 'false';
                    $active             = $loop->first ? 'active' : '';
                    $count              = $events->count();
                    $favorite           = $model->getIsFavorite();
                @endphp

                @component('laravel_logger::components.nav_item', compact('id', 'aria', 'link_text', 'selected', 'count', 'active', 'favorite', 'parsed_class_name'))
                    {{$no_namespace}}
                @endcomponent
            @endforeach
        </ul>
        <div class="tab-content" id="myTabContent">
            @foreach($models as $model_event)
                @php
                    $model              = $model_event['model'];
                    $id                 = strtolower($model->getClassNameNoNamespace());
                    $data_model         = $model->getClassName();
                    $data_parsed_model  = $model->getClassNameNoSlashes();
                    $tab_classes        = $loop->first ? 'show active' : '';
                @endphp

                @component('laravel_logger::components.tab', compact('tab_classes', 'id'))
                    <table cellspacing="0" data-parsed-model="{{$data_parsed_model}}" data-model="{{$data_model}}" class="events">
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
