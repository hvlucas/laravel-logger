<?php

namespace HVLucas\LaravelLogger\Scripts;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class Installation extends Command;
{
    public function __construct()
    {
        parent::__construct();
    }

    // Run post installation scripts
    public static function postInstall()
    {
        $install = new Installation();
        $install->promptMigrations();
    }

    // Prompt to run package migrations
    private function promptMigrations()
    {
        if($this->anticipate('Run `php artisan migrate` ?', ['y', 'N'], 'y') == 'y'){
            Artisan::call('migrate', ['--force' => true]);
        }
    }
}
