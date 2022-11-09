<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_name')->unique();
            $table->string("full_name");
            $table->string('password');
            $table->integer("user_type"); // 0 admin  1 user
            $table->double("salary")->nullable();
            // $table->string("start_attendance")->nullable();
            // $table->string("leave_attendance")->nullable();
            $table->double("reward")->nullable()->default(0);

            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();
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
};