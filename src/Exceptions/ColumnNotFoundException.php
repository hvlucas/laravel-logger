<?php

namespace HVLucas\LaravelLogger\Exceptions;

use HVLucas\LaravelLogger\LaravelLoggerException;

/*
 * Exception thrown when a user column passed through config does not exist in the user's table
 */
class ColumnNotFoundException extends LaravelLoggerException
{
}
