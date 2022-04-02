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
        Schema::table('comment', function (Blueprint $table) {
            $table->foreign('author_id')->references('id')->on('users');
            $table->foreign('course_id')->references('id')->on('course');
        });
        Schema::table('role_permission', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('role');
            $table->foreign('permission_id')->references('id')->on('permission');
        });
        Schema::table('reply_comment', function (Blueprint $table) {
            $table->foreign('comment_id')->references('id')->on('comment');
            $table->foreign('author_id')->references('id')->on('users');
        });
        Schema::table('sub_category', function (Blueprint $table) {
            $table->foreign('level_3_id')->references('id')->on('all_category');
        });
        Schema::table('category', function (Blueprint $table) {
            $table->foreign('level_1_id')->references('id')->on('all_category');
        });
        Schema::table('group_category', function (Blueprint $table) {
            $table->foreign('level_2_id')->references('id')->on('all_category');
        });
        Schema::table('course', function (Blueprint $table) {
            $table->foreign('author_id')->references('id')->on('users');
            $table->foreign('price_id')->references('id')->on('price');
            $table->foreign('sub_category_id')->references('id')->on('sub_category');
            $table->foreign('group_category_id')->references('id')->on('group_category');
            $table->foreign('instructional_level_id')->references('id')
                ->on('instructional_level');
        });
        Schema::table('course_outcome', function (Blueprint $table) {
            $table->foreign('course_id')->references('id')->on('course');
        });
        Schema::table('course_requirements', function (Blueprint $table) {
            $table->foreign('course_id')->references('id')->on('course');
        });
        Schema::table('like', function (Blueprint $table) {
            $table->foreign('comment_id')->references('id')->on('comment');
            $table->foreign('user_id')->references('id')->on('users');
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

        // Schema::table('categories_course', function (Blueprint $table) {
        //     $table->foreign('course_id')->references('id')->on('course');
        //     $table->foreign('category_id')->references('id')->on('category');
        // });

        Schema::table('resources', function (Blueprint $table) {
            $table->foreign('lecture_id')->references('id')->on('lectures')->onDelete('cascade');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('role');
        });
        Schema::table('bio', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
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
