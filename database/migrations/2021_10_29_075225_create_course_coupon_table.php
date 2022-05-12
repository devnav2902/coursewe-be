<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseCouponTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_coupon', function (Blueprint $table) {
            $table->string('code', 20);
            $table->unsignedInteger('course_id');
            $table->char('discount_price')->default(0);
            $table->string('coupon_id');
            $table->boolean('status')->default(1);
            // $table->integer('enrollment_limit')->nullable();
            $table->integer('currently_enrolled')->default(0);
            $table->timestamp('expires');
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['code', 'course_id'], 'COURSE_COUPON_PRIMARY');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_coupon');
    }
}
