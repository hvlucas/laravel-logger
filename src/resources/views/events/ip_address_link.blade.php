@if($event->ip_address)
    <a target="_blank" href="https://ipinfo.io/{{$event->ip_address}}">{{$event->ip_address}}</a>
@endif
