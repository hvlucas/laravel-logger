<?php

namespace HVLucas\LaravelLogger;

use HVLucas\LaravelLogger\App\Event;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class LaravelLoggerRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::bind('soft_event', function($soft_event){
            return Event::onlyTrashed()->find($soft_event);
        });
    }
}
