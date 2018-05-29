<?php

namespace HVLucas\LaravelLogger\App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use SoftDeletes;

    /*
     * The connection name for the model.
     */
    protected $log_connection;

    /*
     * Table name of Event
     */
    protected $table;

    /*
     * Supported primary key types
     */
    protected $primary_key_types = ['int', 'string'];

    /*
     * Guarded attributes
     */
    protected $guarded = [];

    /*
     * Date attributes
     */
    protected $dates = [ 'deleted_at' ];

    /*
     * TODO
     * set attributes
     * Allowed attributes to fill in Model
     */
    protected $fillable = [];

    /*
     * TODO
     * set casts
     * Cast attributes when saving
     */
    protected $casts = [];

    /*
     * Constructor for Model
     */
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        $this->log_connection = config('laravel_logger.log_connection');
        $this->table = config('laravel_logger.table_name', 'logged_events');
    }

    /*
     * Get log connection name 
     */
    public function getLogConnection()
    {
        return $this->log_connection;
    }

    /*
     * Get supported primary key types
     */
    public function getPrimaryKeyTypes(){
        return $this->primary_key_types;
    }

    /*
     * Get table name
     */
    public function getTable()
    {
        return $this->table;
    }

    /*
     * An activity has a user.
     */
    public function user()
    {
        return $this->hasOne(config('laravel_logger.user_model'));
    }

    /*
     * TODO
     * Validator rules
     */
    public static function rules(){
        return [];
    }
}
