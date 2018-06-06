<?php

namespace HVLucas\LaravelLogger;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use HVLucas\LaravelLogger\LaravelLoggerTracker;
use HVLucas\LaravelLogger\Observers\ModelObserver;
use HVLucas\LaravelLogger\Facades\LaravelLogger;
use HVLucas\LaravelLogger\App\Event;
use HVLucas\LaravelLogger\Exceptions\TableNotFoundException;
use DateTime;

class LaravelLoggerServiceProvider extends ServiceProvider
{
    // Boot Application
    public function boot(): void
    {
        $this->bootLaravelLogger();
    }

    // Register Application
    public function register()
    {
        $this->app->bind('LaravelLogger', function(){
            return new LaravelLoggerTracker();
        });
    }

    // Boot Application with given configuration
    public function bootLaravelLogger(): void
    {
        // Load config
        $config = __DIR__ . '/config/laravel_logger.php';
        $this->mergeConfigFrom($config, 'laravel_logger');

        $this->publishes([
            __DIR__.'/config/laravel_logger.php' => config_path('laravel_logger.php'),
        ]);

        // Publish the config/laravel_logger.php file
        $this->publishes([$config => config_path('laravel_logger.php')], 'config');

        // Register Routes
        $this->loadRoutesFrom(__DIR__.'/routes/laravel_logger.php');

        // Register views
        $this->loadViewsFrom(__DIR__ . '/resources/views/', 'laravel_logger');

        // Register Migrations
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        // Publish Laravel Logger assets
        $this->publishes([
           __DIR__.'/resources/assets/' => public_path('vendor/laravel-logger'),
        ], 'public');

        // Publish DataTables
        $this->publishes([
           base_path('vendor/datatables/datatables/media') => public_path('vendor/laravel-logger/vendor/datatables'),
        ], 'public');

        $this->publishes([
            base_path('vendor/twbs/bootstrap/dist/') => public_path('vendor/laravel-logger/vendor/bootstrap'),
        ], 'public');

        // Singleton LaravelLoggerTracker bind
        $this->app->singleton('LaravelLoggerTracker', function() {
            return new LaravelLoggerTracker();
        });

        // Read from config or Auto Detect Models on the fly
        $loggable_models = config('laravel_logger.loggable_models', $this->autoDetectModels());
        foreach((array) $loggable_models as $loggable){
            if($this->validModel($loggable)){
                $this->handleModel($loggable);
            }
        }
    }

    
    // Returns a list of models based on application base path
    private function autoDetectModels(): array
    {
        $dir_tree = preg_grep('/.*\.php/', scandir(base_path('app/')));
        $models = array();
        foreach($dir_tree as $file){
            $model_namespace = config('laravel_logger.base_model_namespace', 'App');
            $file_name = "$model_namespace\\$file";
            $model = preg_replace('/\.php$/', '', $file_name);
            if(class_exists($model)){
                try {
                    $instance = new $model;
                }catch(\Throwable $e){
                    continue;
                }
                $table = $instance->getTable();
                if(Schema::hasTable($table)){
                    $models[] = $model;
                }
            }
        }
        return $models;
    }

    
    // Validates passed model data, could be array with attribute settings, or string of class name
    private function validModel($data): bool
    {
        if(is_string($data) && class_exists($data)){
            return true;
        }

        $validator = Validator::make($data, [
            'model'         => 'required|string',
            'events'        => 'nullable',
            'attributes'    => 'nullable',
            'tracks_user'   => 'nullable|boolean',
            'tracks_data'   => 'nullable|boolean',
        ]);

        return $validator->passes();
    }

    
    // Start tracking model using Laravel Observers 
    private function handleModel($data): void
    {
        $model = $data; 
        $events = [];
        $attributes = [];
        //TODO
        //get events/attributes/tracks_user/tracks_data from model itself
        $tracks_user = true;
        $tracks_data = true;
        $is_favorite = false;
        if(!is_string($data)){
            $model = $data['model'];

            if(isset($data['events'])){
                $events = (array) $data['events'];
            }
            
            if(isset($data['attributes'])){
                $attributes = (array) $data['attributes'];
            }

            if(isset($data['tracks_user'])){
                $tracks_user = (bool) $data['tracks_user'];
            }

            if(isset($data['tracks_data'])){
                $tracks_data = (bool) $data['tracks_data'];
            }

            if(isset($data['favorite'])){
                $is_favorite = (bool) $data['favorite'];
            }
        }

        // Now that we have pulled config data for each model, we create an instance of LaravelLoggerModel and observe
        // its events. We also set the starting point for each model record in DB
        $laravel_logger_model = new LaravelLoggerModel($model, $events, $attributes, $tracks_user, $tracks_data, $is_favorite);
        LaravelLogger::push($laravel_logger_model);
        $model::observe($this->app->make('HVLucas\LaravelLogger\Observers\ModelObserver'));
        $laravel_logger_model->setStartingPoint();
    }
}
