<?php

namespace HVLucas\LaravelLogger\Exceptions;

use HVLucas\LaravelLogger\LaravelLoggerException;

/*
 * Exception thrown when a model is fetched but it is not registered through the service provider
 */
class ModelNotFoundException extends LaravelLoggerException
{
}
