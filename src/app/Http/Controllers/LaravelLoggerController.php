<?php

namespace HVLucas\LaravelLogger\App\Http\Controllers;

use Illuminate\Routing\Controller;
use HVLucas\LaravelLogger\App\Event;

abstract class LaravelLoggerController extends Controller
{
    // TODO
    // Filter by individual models
    // Filter by user
    // Filter by range
    // Export PDF/CSV
    public function list()
    {
    }

    // TODO
    // View Model Instance history
    public function show(Event $event)
    {
    }

    // TODO
    // Soft delete Event
    public function destroy(Event $event)
    {
    }

    // TODO
    // Force delete Event
    public function forceDestroy(Event $soft_event)
    {
    }

    // TODO
    // Clear history and set starting point to current point in time
    public function setStartingPoint()
    {
    }
}
