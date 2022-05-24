<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgressLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('progress_logs', function (Blueprint $table) {
            // $table->increments('id');
            $table->unsignedInteger('lecture_id');
            $table->unsignedInteger('course_id');
            $table->unsignedInteger('user_id');
            $table->integer('last_watched_second')->default(0);
            $table->timestamps();
            $table->primary(['user_id', 'lecture_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('progress_logs');
    }
}
