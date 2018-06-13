@if($model->isTrackingAuthenticatedUser())
    <tag class="user">{{$event->user_name ?: 'UnAuthenticated'}}</tag>
@endif
