@component('laravel_logger::components.tag', ['class' => strtolower($event->method) . ' method', 'filter' => 'method'])
    {{$event->method}}
@endcomponent
{{$event->parsed_url}}
