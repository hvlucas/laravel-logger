<?php

namespace HVLucas\LaravelLogger;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Validation\Validator;
use HVLucas\LaravelLogger\Observers\ModelObserver;

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
        ]);

        return $validator->passes();
    }

    /*
     * Start observing model
     * TODO
     * Check for settings inside the Model given
     */
    private function handleModel($data): void
    {
        $default_events = config('laravel_logger.default_events', ['created', 'updated', 'deleted', 'retrieved']);
        if(is_string($data)){
            $model = $data; 
            $events = $default_events;
            $attributes = [];
        }else{
            $model = $data['model'];
            $events = $data['events'] ?? $default_events;
            $attributes = $data['attributes'] ?? [];
        }

        $model::observe(new ModelObserver($events, $attributes, config('laravel_logger.log_user', true)));
    }
}
