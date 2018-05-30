<?php

namespace HVLucas\LaravelLogger;

use DateTime;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Validation\Validator;
use HVLucas\LaravelLogger\Observers\ModelObserver;
use HVLucas\LaravelLogger\LaravelLoggerTracker;
use HVLucas\LaravelLogger\Facades\LaravelLogger;
use HVLucas\LaravelLogger\App\Event;
use Illuminate\Support\Facades\Schema;

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

        $loggable_models = config('laravel_logger.loggable_models', $this->autoDetectModels());
        if(empty($loggable_models)){
            // TODO
            // throw exception of no detectable models 
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
                $models[] = $model;
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

        $event_instance = new Event;
        $event_table = $event_instance->getTable();
        
        $model_instance = new $model;
        $model_table = $model_instance->getTable();
        $model_key = $model_instance->getKeyName();

        // Fetch models that have not been initiated
        // TODO
        // Throw exception for not running migrations
        if(Schema::hasTable($event_table)){
            $models = $model::leftJoin($event_table, "$model_table.$model_key", '=', "$event_table.model_id")->select("$model_table.*", "$event_table.activity as event_activity_id")->whereNull("$event_table.activity")->get();
            foreach($models as $init_model){
                $created_at = new DateTime;
                $created_at->setTimestamp(time());
                $attributes = $laravel_logger_model->getAttributeValues($init_model);

                Event::create([
                    'activity' => 'startpoint',
                    'user_id' => null,
                    'model_id' => (string) $init_model->{$init_model->getKeyName()},
                    'model_name' => $model, 
                    'model_attributes' => $attributes,
                    'user_agent' => null,
                    'session_id' => null,
                    'ajax' => false,
                    'full_url' => null,
                    'created_at' => $created_at,
                ]);
            }
        }

    }
}
