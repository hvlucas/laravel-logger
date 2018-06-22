<?php

namespace HVLucas\LaravelLogger;

use ReflectionClass;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use HVLucas\LaravelLogger\App\Event;
use HVLucas\LaravelLogger\App\Http\Middleware\LogEvent;
use HVLucas\LaravelLogger\Facades\LaravelLogger;
use HVLucas\LaravelLogger\LaravelLoggerTracker;
use HVLucas\LaravelLogger\Exceptions\ColumnNotFoundException;
use HVLucas\LaravelLogger\Exceptions\InvalidSyntaxException;
use HVLucas\LaravelLogger\Exceptions\TableNotFoundException;
use HVLucas\LaravelLogger\Observers\ModelObserver;

class LaravelLoggerServiceProvider extends ServiceProvider
{
    // Boot Application
    public function boot(Router $router): void
    {
        //Register middleware
        $router->aliasMiddleware('log_event', LogEvent::class);
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

        // Check if column given exists in user table
        $user_model = config('laravel_logger.user_model', 'App\User');
        $user = new $user_model;
        $user_table = $user->getTable();
        $user_column = config('laravel_logger.user_column', $user->getKeyName());

        if(!Schema::hasColumn($user_table, $user_column)){
            throw new ColumnNotFoundException("Column `$user_column` was not found in `$user_table` table");
        }

        // Read from config or auto Detect Models on the fly
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
        $dir_tree = preg_grep('/.*\.php/', scandir(base_path(config('laravel_logger.discover_path', 'app/'))));
        $models = array();
        foreach($dir_tree as $file){
            $model_namespace = config('laravel_logger.discover_namespace', 'App');
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

        if(gettype($data) != 'array'){
            throw new InvalidSyntaxException('Data passed through config is not a string or an array, please double check your syntax');
        }

        $validator = Validator::make($data, [
            'model'                     => 'required|string',
            'events'                    => 'nullable',
            'trackable_attributes'      => 'nullable',
            'sync_attributes'           => 'nullable',
            'tracks_user'               => 'nullable|boolean',
            'tracks_data'               => 'nullable|boolean',
        ]);

        return $validator->passes();
    }

    // Start tracking model using Laravel Observers 
    private function handleModel($model_data): void
    {
        $model = $model_data; 
        if(!is_string($model_data)){
            $model = $model_data['model'];
            $attributes = $model_data['trackable_attributes'] ?? [];
            $sync_attributes = $model_data['sync_attributes'] ?? [];
            $tracks_user = $model_data['tracks_user'] ?? null;
            $tracks_data = $model_data['tracks_data'] ?? null;
            $is_favorite = $model_data['is_favorite'] ?? null;
            $only_when_authenticated = $model_data['only_when_authenticated'] ?? null;
        }
        // if model needs variables for its constructor, then skip it;
        // TODO 
        // find a work around
        try {
            $model_instance = new $model;
        }catch(\Throwable $e){
            return;
        }

        if(!isset($tracks_user)){ 
            $tracks_user = $this->getProperty('tracks_user', $model);
            if($tracks_user === null){
                $tracks_user = true;
            }
        }

        if(!isset($attribute)){
            $attributes = [];
        }

        if(!isset($sync_attribute)){
            $sync_attributes = [];
        }

        if(!isset($tracks_data)){
            $tracks_data = $this->getProperty('tracks_data', $model);
            if($tracks_data === null){
                $tracks_data = true;
            }elseif(!$tracks_data){
                $trackable_attributes = [];
                $sync_attributes = [];
            }
        }

        if(!isset($is_favorite)){
            $is_favorite = (bool) $this->getProperty('is_favorite', $model);
        }

        if(!isset($only_when_authenticated)){
            $only_when_authenticated = (bool) $this->getProperty('only_when_authenticated', $model);
        }
        // If not tracking user, then set only_when_authenticated flag to false
        if(!$tracks_user && $only_when_authenticated){
            $only_when_authenticated = false;
        }
        // check for table_name existance in Schema
        $table_name = $model_instance->getTable();
        if(!Schema::hasTable($table_name)){
            throw new TableNotFoundException("`$table_name` was not found in your Schema. Make sure you have the correct table name.");
        }
        //if attributes is not set in config, check model class for 'trackable_attributes' property
        if(empty($attributes) && $tracks_data){
            $attributes = $this->getProperty('trackable_attributes', $model);
            if($attributes === null){
                $all_attributes = Schema::getColumnListing($table_name);
                $hidden_attributes = [$model_instance->getKeyName(), 'created_at', 'updated_at'];
                //remove primary key/created_at/updated_at from tracking list and merge hidden attributes as well
                $hidden_attributes = array_merge($hidden_attributes, (array) $this->getProperty('hidden', $model));
                $attributes = array_diff($all_attributes, $hidden_attributes);
            }
        }
        // when sync_attributes is empty, then check model property;
        if(empty($sync_attributes) && $tracks_data){
            $sync_attributes = $this->getProperty('sync_attributes', $model);
            if($sync_attributes === null){
                $sync_attributes = $attributes;
            }
        }
        // check if sync_attributes exists in DB
        foreach($sync_attributes as $attr){
            if(!Schema::hasColumn($table_name, $attr)){
                throw new ColumnNotFoundException("Column `$attr` was not found in `$table_name` table");
            }
        }
        // Now that we have pulled config data for this model instance, we create an instance of LaravelLoggerModel and 
        // observe its events. We also set the starting point for each model record in DB
        $laravel_logger_model = new LaravelLoggerModel($model, $attributes, $sync_attributes, $tracks_user, $tracks_data, $only_when_authenticated, $is_favorite);
        LaravelLogger::push($laravel_logger_model);
        $model::observe($this->app->make('HVLucas\LaravelLogger\Observers\ModelObserver'));
        $laravel_logger_model->setStartingPoint();
    }

    // Returns model's property
    private function getProperty($name, $model)
    {
        $reflection = new ReflectionClass($model);
        if($reflection->hasProperty($name)){
            $property = $reflection->getProperty($name);
            $property->setAccessible('true');
            return $property->getValue(new $model);
        }
        return null;
    }
}
