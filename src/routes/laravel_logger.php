<?php

Route::group([
    'prefix' => config('laravel_logger.route_prefix', 'events'), 
    'namespace' => 'HVLucas\LaravelLogger\App\Http\Controllers'
], function(){
    Route::get('/list', 'EventsController@list');
    Route::get('/show/{event}/', 'EventsController@show');
    Route::post('/set-starting-point/{event}/', 'EventsController@setStartingPoint');
    Route::delete('/destroy/{event}/', 'EventsController@destroy');
    Route::delete('/force-destroy/{soft_event}/', 'EventsController@forceDestroy');
});

Route::group([
    'prefix' => 'events-ajax-helpers',
    'namespace' => 'HVLucas\LaravelLogger\App\Http\Controllers'
], function(){
    Route::post('/model-history', 'EventsController@modelHistory');
    Route::get('/model-history/filter', 'EventsController@filterHistory');
});
