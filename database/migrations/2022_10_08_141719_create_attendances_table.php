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
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->uuid("user_id");
            $table->string("attendance_time");
            $table->string("leaving_time")->nullable();
            $table->uuid("role_id")->nullable();
            $table->integer("status"); // 0 Present  1  late  2 absent  3 vacation 
            $table->string("mac_address");
            $table->string("ip_mobile");
            $table->string("lang_tude");
            $table->string("lat_tude");
            $table->double("num_clock")->default(0);
            $table->string("over_time")->nullable();
            $table->date("date");
            $table->text("note")->nullable();
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
        Schema::dropIfExists('attendances');
    }
};