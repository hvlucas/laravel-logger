@if($model->isTrackingAuthenticatedUser())
    @component('laravel_logger::components.tag', ['class' => 'user', 'filter' => 'user_id'])
        {{$event->user_name ?: 'UnAuthenticated'}}
    @endcomponent
@endif
