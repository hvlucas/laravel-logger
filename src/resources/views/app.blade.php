<!DOCTYPE html>
<html lang="{{ config('app.locale', 'en') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- CSRF Token --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- CSS --}}
        <link rel="stylesheet" href="{{ asset('/vendor/laravel-logger/vendor/bootstrap/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('/vendor/laravel-logger/vendor/datatables/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('/vendor/laravel-logger/css/font-awesome/fontawesome-all.min.css') }}">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.1/css/responsive.dataTables.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/10.0.2/css/bootstrap-slider.css">
        <link rel="stylesheet" href="{{ asset('/vendor/laravel-logger/css/all.css') }}">

        {{-- JS --}}
        <script src="{{ asset('/vendor/laravel-logger/vendor/datatables/js/jquery.js') }}" type="text/javascript"></script>
        <script src="{{ asset('/vendor/laravel-logger/vendor/bootstrap/js/bootstrap.min.js') }}" type="text/javascript"></script>
        <script src="{{ asset('/vendor/laravel-logger/vendor/datatables/js/jquery.dataTables.js') }}" type="text/javascript"></script>
        <script src="{{ asset('/vendor/laravel-logger/vendor/datatables/js/dataTables.bootstrap4.min.js') }}" type="text/javascript"></script>
        <script src="https://cdn.datatables.net/responsive/2.2.1/js/dataTables.responsive.min.js" type="text/javascript"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/10.0.2/bootstrap-slider.min.js" type="text/javascript"></script>
        <script src="{{ asset('/vendor/laravel-logger/js/main.js') }}" type="text/javascript"></script>

        <title>Events</title>
    </head>
    <body>
        <div class="events-container">
            <div class="row">
                <div class="col-lg">
                    @yield('laravel_logger')
                </div>
            </div>
        </div>
    </body>
</html>
