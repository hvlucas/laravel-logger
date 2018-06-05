<div class="row">
    <div class="col-lg event-container">
        <table class="history">
            <tbody>
                @php
                    $split = ceil(count($attributes)/2);
                @endphp
                @foreach($attributes as $attr)
                    <tr>
                        <td>{{$attr}}</td>
                        @foreach($history as $event)
                            @php
                                $value = $event->model_attributes[$attr] ?? null;
                            @endphp
                            <td>{{$value}}</td>
                            @if(!$loop->last)
                                <td> 
                                    @if($loop->iteration == $split) 
                                        <i class="fas fa-arrow-alt-right"></i> 
                                    @endif
                                </td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
