<?php

namespace HVLucas\LaravelLogger;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Validation\Validator;
use HVLucas\LaravelLogger\Observers\ModelObserver;
use HVLucas\LaravelLogger\LaravelLoggerTracker;
use HVLucas\LaravelLogger\Facades\LaravelLogger;

class LaravelLoggerServiceProvider extends ServiceProvider
{
    /*
     * Boot Application
     */
    public function boot(): void
    {
        $this->bootLaravelLogger();
    }

    /*
     * Register Application
     */
    public function register()
    {
        $this->app->bind('LaravelLogger', function(){
            return new LaravelLoggerTracker;
        });
    }

    /*
     * Boot Application with given configuration
     */
    public function bootLaravelLogger(): void
    {
        //Load config
        $config = __DIR__ . '/config/laravel_logger.php';
        $this->mergeConfigFrom($config, 'laravel_logger');
        $this->publishes([
            __DIR__.'/config/laravel_logger.php' => config_path('laravel_logger.php'),
        ]);

        //Publish the config/laravel_logger.php file
        $this->publishes([$config => config_path('laravel_logger.php')], 'config');

        //Register Routes
        $this->loadRoutesFrom(__DIR__.'/routes/laravel_logger.php');

        //Register views
        $this->loadViewsFrom(__DIR__ . '/resources/views/', 'laravel_logger');

        //Register Migrations
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $loggable_models = config('laravel_logger.loggable_models', $this->autoDetectModels());
        if(empty($loggable_models)){
            //TODO
            //throw exception of no detectable models 
        }

        $this->app->singleton('LaravelLoggerTracker', function() {
            return new LaravelLoggerTracker();
        });

        foreach($loggable_models as $loggable){
            if($this->validModel($loggable)){
                $this->handleModel($loggable);
            }
        }
    }

    /*
     * Returns a list of models based on application base path
     */
    private function autoDetectModels(): array
    {
        $dir_tree = preg_grep('/.*\.php/', scandir(base_path('app/')));
        $models = array();
        foreach($dir_tree as $file){
            $model_namespace = config('laravel_logger.base_model_namespace', 'App');
            $file_name = "$model_namespace\\$file";
            $model = preg_replace('/\.php$/', '', $file_name);
            if(class_exists($model)){
                $models[] = $model;
            }
        }
        return $models;
    }

    /*
     * Validates passed model data, could be array with attribute settings, or string of class name
     */
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

    /*
     * Start tracking model using Laravel Observers 
     */
    private function handleModel($data): void
    {
        $model = $data; 
        $events = [];
        $attributes = [];
        $tracks_user = true;
        $tracks_data = true;
        if(!is_string($data)){
            $model = $data['model'];

            if(isset($data['events'])){
                $events = (array) $data['events'];
            }
            
            if(isset($data['attributes'])){
                $attributes = (array) $data['attributes'];
            }

            if(isset($data['log_user'])){
                $tracks_user = (bool) $data['log_user'];
            }

            if(isset($data['log_data'])){
                $tracks_data = (bool) $data['log_data'];
            }
        }

        $laravel_logger_model = new LaravelLoggerModel($model, $events, $attributes, $tracks_user, $tracks_data);
        LaravelLogger::push($laravel_logger_model);
        $model::observe($this->app->make('HVLucas\LaravelLogger\Observers\ModelObserver'));
    }
}
