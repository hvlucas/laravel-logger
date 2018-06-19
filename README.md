# NOT LIVE

## # Laravel Logger v1.0
Laravel Logger was made to keep track of Model history! When you have an application with multiple user roles, it is important to know who made changes to the application Database. Laravel Logger can help you do that with an easy setup!

With Laravel Logger you can:
  - Keep track of of model change history
  - Have a clean front-end to view model changes
  - Sync model with previous states
  - Export model history (CSV, PDF) from a Date Range or its full history
  - View everything from a clean and responsive front-end

## # Compatibility
| Laravel Logger | Laravel | PHP   |
| -------------- | ------- | ----  |
| 1.x            | 5.5     | >=7.0 |

## # Configuration
There are a few configurations that can be set from the get-go. They are all optional, but will improve flexibility. Since LaravelLogger is configured at compiler-time (when your application's Service Providers are being booted), it is **important to setup your config file before running php artisan migrate (first time installation).** Otherwise, it will try to auto-discover models in your `app/` directory, and create a copy of each database row available.   
Each model row will have a `startpoint`. Meaning from the moment LaravelLogger's service provider boots up, it will try to find a model instance which has not started its tracking yet. Which is why it would be benefitial for you to fully understand the configuration!

| Config | Type | Default | What it does |
|------|----|-------|------------|
|route_prefix|*string*|"events"|Route prefix which the list of events is going to be. `https://{server_domain}/{your_custom_prefix}/list/`|
|log_connection|*string*|`null`|If you wish to store model instances in a different database you create a connection in `config/database.php`.|
|table_name|*string*|"logged_events"|Name of Event table that is going to be created.|
|user_model|*string*|"App\User"|Authenticated user class name in which an Event will be associated with. Make sure to include namespacing.|
|user_column|*string*|`null`|Column for the associated user of an Event to be displayed in the front-end. Omitting this option will display their Primary Key by default.|
|loggable_models|*string\|array*|`null`|Ommitting this option will cause LaravelLogger to go through your `app` folder to automatically search for models to track.|
|discover_path|*string*|"app/"|If `loggable_models` is `null`, this config can set the path in which LaravelLogger will automatically search for models to track.|
|discover_namespace|*string*|"App"|If `loggable_models` option is left blank, this option can be set to define the namespace of your `discover_path`.|


### # Configuration - Initialization by Config

This is a list of available sub-options for the `loggable_models` option:

|Option|Type|Required|Default|Description|
|------|----|--------|-------|-----------|
|model|*string*|yes|`null`|Model which is going to be tracked.|
|trackable_attributes|*string\|array*|no|`null`|Which attributes which will be stored when an event happens. If this is not set, it will pull all non-hidden attributes.|
|sync_attributes|*string\|array*|no|`null`|Which attributes will update when syncing model. When this is not set, it will default to `trackable_attributes`. **Columns must exist in model table.**|
|tracks_data|*bool*|no|`true`|Are attributes being tracked? If `trackable_attributes` is set, then `tracks_data` will overwrite that setting.|
|tracks_user|*bool*|no|`true`|Is the authenticated user being tracked?|
|is_favorite|*bool*|no|`false`|Show this model in the beginning of the event's index page. Models are sorted by favoritism then alphabetically.|

An advantage of LaravelLogger is that it incorporates Laravel's accessors. 
```php
class MyClass extends Model 
{
    //...
    public function getWeightAttribute()
    {
        return (int) $this->weight / 1.5;
    }
    //...
}
```

Then add it to your `config/laravel_logger.php` 
```php
'loggable_models' => [
    //...
    [
        'model' => 'App\Model',
        'trackable_attributes' => [...,'weight',...]
        //...
    ],
    //...
],
```
To read more about accessors, check Laravel's [documentation](https://laravel.com/docs/5.6/eloquent-mutators#accessors-and-mutators)

### # Configuration - Initialization by Model

You can set up `loggable_models` in many different ways. Just passing a string will only track one model and its attributes.   
For example:
```php
//...
'loggable_models' => 'App\MyClass'
//or
'loggable_models' => ['App\MyClass', 'App\SpecialModelNamespace\Team']
//...
```
There are ways you can configure what will be tracked through the model itself. In each file, you can set protected properties which will do the trick for LaravelLogger:
```php
class MyClass extends Model 
{
    //...
    protected $trackable_attributes = ['name', 'title', 'role_id', 'salary', 'gender'];
    protected $sync_attributes = ['name', 'title', 'gender'];
    protected $tracks_user = false;
    protected $tracks_data = true;
    protected $is_favorite = true;
    //...
}
```

### # Configuration - Sensitive Data
In case you don't want to store/display sensitive data, you can use Laravel's hidden attributes (or set  `trackable_attributes` config). LaravelLogger will automatically ignore `id, created_at, updated_at`, unless specified.
```php
class MyClass extends Model 
{
    protected $hidden = ['password'];
}
```

## # Installation
Require our package:
```console
composer require hvlucas/laravel-logger 1.0
```
Publish required files and select the number corresponding to this package:
```console
php artisan vendor:publish
```
```console
Which provider or tag's files would you like to publish?:
  [0 ] Publish files from all providers and tags listed below
  [X ] Provider:  HVLucas\LaravelLogger\LaravelLoggerServiceProvider
```
Setup your `config/laravel_logger.php` and then run migrations:
```console
php artisan migrate
```
## # Front-End

### # Fron-End - Filtering
LaravelLogger takes advantages of Server-Side Processing DataTables has to offer. By clicking on individual tags, we can start filtering by them.
![Tags screenshot](https://s3.amazonaws.com/laravel-logger/Screenshot+from+2018-06-18+10-21-34.png)  

Here are some other keywords you can use in the search bar. You can also use regular keywords, which will filter through every record on the table server-sided.

|Keyword|Tags|
|-------|------|
|*tag/tags*|All available tags|
|*users/user*|Authenticated user Primary Key/Column tags|
|*event/events*|All events|
|*activity/activities*|Alias for *event*
|*method/methods*|Method type associated with event|
|*request/requests*|Alias for *method*|

### Front-End - Syncing
LaravelLogger can keep track of your model well, but it can also sync your model to a point in time when necessary! You can click on the ID of the model you wish to inspect with more detail.
![Sync screenshot](https://s3.amazonaws.com/laravel-logger/Screenshot+from+2018-06-18+10-43-14.png)

TODO 
* Show syncing example
* Add export example
* Add middleware section

## # Testing
None as of yet

## # Development
If you have any ideas to contribute or bug fixes in mind: 
  - Fork the repo 
  - Create a PR for me to take a look
