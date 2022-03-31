<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegistrationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('registration', function (Blueprint $table) {
        //     $table->unsignedInteger('user_id');
        //     $table->unsignedInteger('course_id');

        //     $table->timestamp('created_at')->useCurrent();
        //     $table->primary(['user_id', 'course_id']);
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('registration');
    }
}
