<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart', function (Blueprint $table) {
            $table->string('session_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('cart_type_id');
            $table->unsignedInteger('course_id');
            $table->string('coupon_code')->nullable();
            $table->timestamps();

            $table->primary(['session_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart');
    }
}
