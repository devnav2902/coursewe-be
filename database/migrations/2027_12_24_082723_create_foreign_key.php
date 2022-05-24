<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course', function (Blueprint $table) {
            $table->foreign('author_id')->references('id')->on('users');
            $table->foreign('price_id')->references('id')->on('price');
            $table->foreign('instructional_level_id')->references('id')
                ->on('instructional_level');
        });
        Schema::table('course_outcome', function (Blueprint $table) {
            $table->foreign('course_id')->references('id')->on('course');
        });
        Schema::table('course_requirements', function (Blueprint $table) {
            $table->foreign('course_id')->references('id')->on('course');
        });

        Schema::table('course_bill', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('course_id')->references('id')->on('course');
        });

        Schema::table('rating', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('course_id')->references('id')->on('course');
        });
        Schema::table('sections', function (Blueprint $table) {
            $table->foreign('course_id')->references('id')->on('course');
        });
        Schema::table('lectures', function (Blueprint $table) {
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
        });
        Schema::table('progress', function (Blueprint $table) {
            $table->foreign('lecture_id')->references('id')->on('lectures');
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::table('categories_course', function (Blueprint $table) {
            $table->foreign('course_id')->references('id')->on('course');
            $table->foreign('category_id')->references('category_id')->on('categories');
        });

        Schema::table('resources', function (Blueprint $table) {
            $table->foreign('lecture_id')->references('id')->on('lectures')->onDelete('cascade');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('role');
        });

        Schema::table('notification_course', function (Blueprint $table) {
            $table->foreign('course_id')->references('id')->on('course');

            $table->foreign('entity_id')->references('id')->on('notification_entity');
        });

        Schema::table('course_coupon', function (Blueprint $table) {
            $table->foreign('course_id')->references('id')->on('course');
            $table->foreign('coupon_id')->references('id')->on('coupon');
        });

        Schema::table('review_course', function (Blueprint $table) {
            $table->foreign('course_id')->references('id')->on('course');
        });

        Schema::table('cart', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('cart_type_id')->references('id')->on('cart_type');
            $table->foreign('course_id')->references('id')->on('course');
        });
        Schema::table('progress_logs', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('lecture_id')->references('id')->on('lectures');
            $table->foreign('course_id')->references('id')->on('course');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('foreign_key');
    }
}
