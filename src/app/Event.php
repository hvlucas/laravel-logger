<?php

namespace HVLucas\LaravelLogger\App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use HVLucas\LaravelLogger\Facades\LaravelLogger;
// Sinergi Browser Dectection - https://github.com/sinergi/php-browser-detector 
use Sinergi\BrowserDetector\Browser; 
use Sinergi\BrowserDetector\Device;
use Sinergi\BrowserDetector\Os;

class Event extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    // The connection name for the model.
    protected $log_connection;

    // Table name of Event
    protected $table;

    // Supported primary key types
    protected $primary_key_types = ['int', 'string'];

    // Guarded attributes
    protected $guarded = [];

    // Date attributes
    protected $dates = [ 'created_at', 'deleted_at' ];

    // Allowed attributes to fill in Model
    protected $fillable = [];

    // Cast attributes when saving
    protected $casts = [];

    // When model gets converted to json
    protected $appends = ['model_id_link', 'event_tag', 'auth_user_tag', 'ip_address_link', 'user_agent_icons', 'request', 'when'];

    // Validation Rule
    public static $rules = [
        'activity' => 'required|max:255',
        // TODO
        // validate if user primary key is string
        'user_id' => 'nullable',
        'model_id' => 'required|max:255',
        'model_name' => 'required|max:255',
        'model_attributes' => 'required|max:4294967295',
        'user_agent' => 'nullable|max:255',
        'method' => 'nullable|max:255',
        'ip_address' => 'nullable|max:255',
    ];

    // Constructor for Model
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        $this->log_connection = config('laravel_logger.log_connection');
        $this->table = config('laravel_logger.table_name', 'logged_events');
    }

    // Get log connection name 
    public function getLogConnection()
    {
        return $this->log_connection;
    }

    // Get supported primary key types
    public function getPrimaryKeyTypes(){
        return $this->primary_key_types;
    }

    // Get table name
    public function getTable()
    {
        return $this->table;
    }

    // An activity has a user.
    public function user()
    {
        return $this->belongsTo(config('laravel_logger.user_model', 'App\User'));
    }

    // Return config's user column to call on `$this->user`
    public function getUserColumn()
    {
        return config('laravel_logger.user_column', null);
    }

    // Return user column or the id
    public function getUserNameAttribute()
    {
        return ($this->getUserColumn() && $this->user) ? $this->user->{$this->getUserColumn()} : $this->user_id;
    }

    // Return Sinergi\Browser
    public function getBrowser()
    {
        return new Browser($this->user_agent);
    }

    // Return Sinergi\Device
    public function getDevice()
    {
        return new Device($this->user_agent);
    }

    // Return Sinergi\OS
    public function getOs()
    {
        return new Os($this->user_agent);
    }

    // Return FontAwesome Browser icon
    public function getFaBrowserAttribute()
    {
        $browser = $this->getBrowser()->getName();
        switch($browser){
            case 'Pocket Internet Explorer':
            case 'Internet Explorer':
                $icon = 'fab fa-internet-explorer';
                break; 

            case 'Microsoft Edge':
                $icon = 'fab fa-edge';
                break; 

            case 'Chrome':
                $icon = 'fab fa-chrome';
                break; 

            case 'GoogleBot':
                $icon = 'fab fa-google';
                break;

            case 'Yahoo! Slurp':
                $icon = 'fab fa-yahoo';
                break;

            case 'BlackBerry':
                $icon = 'fab fa-blackberry';
                break;

            case 'Firefox':
            case 'Mozilla':
                $icon = 'fab fa-firefox';
                break; 

            case 'Safari':
                $icon = 'fab fa-safari';
                break; 

            case 'wkhtmltopdf':
                $icon = 'far fa-file-pdf';
                break;

            case 'Opera':
            case 'Opera Mini':
                $icon = 'fab fa-opera';
                break;

            default:
                $icon = 'far fa-browser';
                break; 
        }
        return $icon;
    }

    // Return FontAwesome OS icon
    public function getFaOsAttribute()
    {
        $device = $this->getOs()->getName();
        switch($device){
            case 'OS X':
            case 'iOS':
                $icon = 'fab fa-apple';
                break; 
                
            case 'Windows';
            case 'Windows Phone';
                $icon = 'fab fa-windows';
                break; 

            case 'Android':
                $icon = 'fab fa-android';
                break; 
            
            case 'Chrome OS':
                $icon = 'fab fa-chrome';
                break;

            case 'Linux':
            case 'FreeBSD':
            case 'OpenBSD':
            case 'NetBSD':
            case 'OpenSolaris':
            case 'SunOS':
                $icon = 'fab fa-linux';
                break; 

            case 'BlackBerry':
                $icon = 'fab fa-blackberry';
                break;

            case 'BeOS':
            case 'SymbOS':
            case 'Nokia':
            default:
                $icon = 'far fa-mobile';
        }
        return $icon;
    }

    // Return minimalistic version of Browser
    public function getParsedVersionAttribute()
    {
        $version = $this->getBrowser()->getVersion();
        return explode('.', $version)[0] ?? $version;
    }

    // Return create_at as a Carbon instance
    public function getCreatedAtAttribute($created_at)
    {
        return new Carbon($created_at);
    }
    
    // Parse full url and return without domain
    public function getParsedUrlAttribute()
    {
        return parse_url($this->full_url)['path'] ?? null;
    } 

    // Return decoded `$this->model_attributes`
    public function getModelAttributesAttribute($model_attr)
    {
        return json_decode($model_attr, true);
    }

    // Return LaravelLoggerModel instance of model associated with `$this`
    public function getModel()
    {
        return LaravelLogger::getModel($this->model_name);
    }

    // Return rules for create an Event
    public static function rules(){
        return static::$rules;
    }

    // Returns previous event
    public function getPreviousAttribute()
    {
        $events = $this->getEventIds();
        $instance_index = $events->search($this->id);
        $previous_id = $events[--$instance_index] ?? null;
        return $previous_id == null ? null : self::find($previous_id);
    }

    // Returns next event
    public function getNextAttribute()
    {
        $events = $this->getEventIds();
        $instance_index = $events->search($this->id);
        $previous_id = $events[++$instance_index] ?? null;
        return $previous_id == null ? null : self::find($previous_id);
    }

    // Saves Event to DB; returns true when successfull
    public static function store(array $data): bool
    { 
        $valid = Validator::make($data, static::$rules)->passes();
        if($valid){
            self::create($data);
        }
        return $valid;
    }

    // Return model ID link view
    public function getModelIdLinkAttribute()
    {
        return view('laravel_logger::events.model_id_link', ['event' => $this])->render();
    }

    // Return event tag view
    public function getEventTagAttribute()
    {
        return view('laravel_logger::events.event_tag', ['event' => $this])->render();
    }

    /// Return authenticated user tag view
    public function getAuthUserTagAttribute()
    {
        return view('laravel_logger::events.auth_user_tag', ['event' => $this, 'model' => $this->getModel()])->render();
    }

    // Return IP Address link view
    public function getIpAddressLinkAttribute()
    {
        return view('laravel_logger::events.ip_address_link', ['event' => $this])->render();
    }

    // Return User Agent icons view
    public function getUserAgentIconsAttribute()
    {
        return view('laravel_logger::events.user_agent_icons', ['event' => $this])->render();
    }

    // Return request view
    public function getRequestAttribute()
    {
        return view('laravel_logger::events.request', ['event' => $this])->render();
    }

    // Return when formatted for readability
    public function getWhenAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    // Return collection of Event IDs that are related to `$this` model id and model name
    private function getEventIds()
    {
        return self::where(['model_name' => $this->model_name, 'model_id' => $this->model_id])->orderBy('created_at')->get()->pluck('id');
    }
}
