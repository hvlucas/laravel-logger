<?php

Route::group([
    'prefix' => config('laravel_logger.route_prefix', 'events'), 
    'namespace' => 'HVLucas\LaravelLogger\App\Http\Controllers'
], function(){
    Route::get('/list', 'EventController@list');
    Route::get('/show/{event}/', 'EventController@show');
    Route::post('/set-starting-point/{event}/', 'EventController@setStartingPoint');
    Route::delete('/destroy/{event}/', 'EventController@destroy');
    Route::delete('/force-destroy/{soft_event}/', 'EventController@forceDestroy');
});
