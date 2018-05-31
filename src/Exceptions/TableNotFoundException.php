<?php

namespace HVLucas\LaravelLogger\Exceptions;

use HVLucas\LaravelLogger\LaravelLoggerException;

/*
 * Exception thrown when events table does not exist: try running 'php artisan migrate'
 */
class TableNotFoundException extends LaravelLoggerException
{
}
