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
        Schema::create('holidays', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string("title");
            $table->string("body")->nullable();
            $table->date('from_day')->nullable();
            $table->date('to_day')->nullable();
            $table->date('from_hour')->nullable();
            $table->date('to_hour')->nullable();
            $table->uuid("user_id");
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
        Schema::dropIfExists('holidays');
    }
};