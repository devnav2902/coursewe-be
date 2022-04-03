<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCourseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('author_id');
            $table->unsignedInteger('instructional_level_id');
            $table->unsignedInteger('price_id');
            $table->string("title", 60);
            $table->string("subtitle", 120)->nullable();
            $table->boolean('isPublished')->default(0);
            $table->boolean('submit_for_review')->default(0);
            $table->text("description")->nullable();
            $table->string("slug", 256)->nullable();
            $table->string("thumbnail", 256)->nullable();
            $table->string("video_demo", 256)->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course');
    }
}
