<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('role_id')->default(2);
            $table->string('fullname', 200);
            $table->string('slug', 100);
            $table->string('email')->unique();
            $table->string('avatar')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->char('account_status', 10)->default(1);
            $table->string('password');
            $table->string('headline', 100)->nullable();
            $table->string('bio', 5000)->nullable();
            $table->string('website')->nullable();
            $table->string('youtube')->nullable();
            $table->string('facebook')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('twitter')->nullable();
            $table->string('phoneNumber')->nullable();
            $table->string('cityCode')->nullable();
            $table->string('city')->nullable();
            $table->string('districtCode')->nullable();
            $table->string('district')->nullable();
            $table->string('wardCode')->nullable();
            $table->string('ward')->nullable();
            $table->string('address')->nullable();
            $table->string('nation')->nullable();
            $table->string('identification')->nullable();
            $table->string('workCityCode')->nullable();
            $table->string('workCity')->nullable();
            $table->string('workDistrictCode')->nullable();
            $table->string('workDistrict')->nullable();
            $table->string('workWardCode')->nullable();
            $table->string('workWard')->nullable();
            $table->string('workAddress')->nullable();
            $table->date('dob')->nullable();
            $table->boolean('gender')->default(1);
            $table->rememberToken();

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
        Schema::dropIfExists('users');
    }
}
