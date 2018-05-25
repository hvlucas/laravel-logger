<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use HVLucas\LaravelLogger\App\Event;

class CreateLaravelLoggerEventTable extends Migration
{
    /*
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $event = new Event;
        $connection = $event->getLogConnection();
        $table = $event->getTable();
        $table_exists = Schema::connection($connection)->hasTable($table);

        if (!$table_exists) {
            $user_model = config('laravel_logger.user_model', 'App\User');
            $user = new $user_model;
            $user_key_type = $user->getKeyType();
            
            if(array_search($user_key_type, $event->getPrimaryKeyTypes()) === false){
                $keys = implode(', ', $event->getPrimaryKeyTypes());
                echo "Primary Key type for User Model is not supported. Use: $keys\n";
                exit(1);
            }

            Schema::connection($connection)->create($table, function (Blueprint $table) {
                $table->increments('id');
                $table->string('activity');

                if($user_key_type == 'int'){
                    $table->integer('user_id')->nullable();
                }elseif($user_key_type == 'string'){
                    $table->string('user_id')->nullable();
                }
                
                //model could have string primary_key
                $table->string('model_id');
                $table->string('model_name');
                $table->longText('model_attributes')->nullable();

                $table->dateTime('created_at');
                $table->softDeletes();
            });
        }
    }

    /*
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $event = new Event;
        $connection = $event->getLogConnection();
        $table = $event->getTable();
        Schema::connection($connection)->dropIfExists($table);
    }
}
