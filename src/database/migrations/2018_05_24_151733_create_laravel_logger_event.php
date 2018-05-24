<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use HVLucas\LaravelLogger\App\Event;

class CreateLaravelLoggerEventTable extends Migration
{
    /**
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
            Schema::connection($connection)->create($table, function (Blueprint $table) {
                $table->increments('id');
                $table->string('activity');
                $table->string('model_name');
                $table->longText('attributes')->nullable();

                //TODO
                //is interchangeable, set toggles
                //$table->integer('user_id')->nullable();
                //$table->integer('model_id');

                $table->dateTime('created_at');
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $event = new Event;
        $connection = $event->getConnectionName();
        $table = $event->getTableName();

        Schema::connection($connection)->dropIfExists($table);
    }
}
