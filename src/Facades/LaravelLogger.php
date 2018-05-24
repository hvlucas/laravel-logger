<?php

namespace HVLucas\LaravelLogger\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelLogger extends Facade
{
    protected static function getFacadeAccessor() 
    {
        return 'laravel_logger';
    }
}
