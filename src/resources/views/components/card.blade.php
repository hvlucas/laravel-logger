<div class="card">
    @if(isset($header))
        <div class="card-header">
            {{$header}}
        </div>
    @endif
    <div class="card-body">
        <h5 class="card-title">{{$title}}</h5>
        @if(isset($subtitle))
            <h6 class="card-subtitle mb-2 text-muted">{{$subtitle}}</h6>
        @endif
        <p class="card-text">{{$slot}}</p>
        @if(isset($links))
            @foreach($links as $route => $text)
                <a href="{{$route}}" class="card-link">{{$text}}</a>
            @endforeach
        @endif
    </div>
</div>
