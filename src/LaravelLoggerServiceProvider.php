<?php

namespace HVLucas\LaravelLogger;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Validation\Validator;
use HVLucas\LaravelLogger\Observers\ModelObserver;
use HVLucas\LaravelLogger\LaravelLoggerTracker;

class LaravelLoggerServiceProvider extends ServiceProvider
{
    /*
     * Boot Application
     */
    public function boot(): void
    {
        $this->registerLaravelLogger();
    }

    /*
     * TODO
     */
    public function registerLaravelLogger(): void
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

        //TODO
        //boot Observer for models that are going to be Logged
        $loggable_models = config('laravel_logger.loggable_models', $this->autoDetectModels());
        if(empty($loggable_models)){
            //TODO
            //handle error exception
        }

        $this->app->singleton('LaravelLoggerTracker', function() {
            return new LaravelLoggerTracker();
        });

        $laravel_logger = $this->app->make(LaravelLoggerTracker::class);

        foreach($loggable_models as $loggable){
            if($this->validModel($loggable)){
                $this->handleModel($loggable);
            }
        }
    }

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
            'log_user'      => 'nullable|boolean',
            'log_data'      => 'nullable|boolean',
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
        LaravelLoggerTracker::push($laravel_logger_model);
        $model::observe($this->app->make('HVLucas\LaravelLogger\Observers\ModelObserver'));
    }
}
