<?php

namespace HVLucas\LaravelLogger\Exceptions;

use HVLucas\LaravelLogger\LaravelLoggerException;

/*
 * Exception thrown model passed to LaravelLoggerModel methods does not match to `$model->class_name`
 */
class ClassNotMatchedException extends LaravelLoggerException
{
}
