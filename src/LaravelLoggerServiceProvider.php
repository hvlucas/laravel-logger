<?php

namespace HVLucas\LaravelLogger;

use Illuminate\Support\ServiceProvider;

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

        //Publish the config/breadcrumbs.php file
        $this->publishes([$config => config_path('laravel_logger.php')], 'config');

        //Register Routes
        $this->loadRoutesFrom(__DIR__.'/routes/laravel_logger.php');

        //Register views
        $this->loadViewsFrom(__DIR__ . '/resources/views/', 'laravel_logger');

        //TODO
        //boot Observer for models that are going to be Logged
        $loggable_models = config('laravel_logger.loggable_models', $this->autoDetectModels());
        if(empty($loggable_models)){
            //TODO
            //handle error exception
        }
    }

    private function autoDetectModels(): array
    {
        $dir_tree = preg_grep('/.*\.php/', scandir(base_path('app/')));
        $models = array();
        foreach($dir_tree as $file){
            $model_namespace = config('laravel_logger.base_model_namespace', 'App');
            $model = "$model_namespace\\$file";
            if(class_exists($model)){
                $models[] = preg_replace('/\.php$/', '', $model);
            }
        }
        return $models;
    }
}
