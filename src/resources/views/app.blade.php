<!DOCTYPE html>
<html lang="{{ config('app.locale', 'en') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- CSRF Token --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- CSS --}}
        <link rel="stylesheet" href="{{ asset('/vendor/bootstrap/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('/vendor/datatables/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('/vendor/laravel-logger/css/all.css') }}">

        {{-- JS --}}
        <script src="{{ asset('/vendor/datatables/js/jquery.js') }}" type="text/javascript"></script>
        <script src="{{ asset('/vendor/bootstrap/js/bootstrap.min.js') }}" type="text/javascript"></script>
        <script src="{{ asset('/vendor/datatables/js/jquery.dataTables.js') }}" type="text/javascript"></script>
        <script src="{{ asset('/vendor/datatables/js/dataTables.bootstrap4.min.js') }}" type="text/javascript"></script>
        <script src="{{ asset('/vendor/laravel-logger/js/main.js') }}" type="text/javascript"></script>

        <title>Events</title>
    </head>
    <body>
        @yield('laravel_logger')
    </body>
</html>
