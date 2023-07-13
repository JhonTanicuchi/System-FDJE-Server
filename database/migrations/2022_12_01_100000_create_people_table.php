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
        Schema::create('people', function (Blueprint $table) {
            $table->increments('id');
            $table->string('names');
            $table->string('last_names');

            $table->integer('identification_type')->unsigned()->nullable();
            $table->foreign('identification_type')->references('id')->on('catalogs');

            $table->string('identification');
            $table->string('gender')->nullable();
            $table->string('date_birth')->nullable();
            $table->string('place_birth')->nullable();
            $table->string('disability')->nullable();
            $table->string('mobile_phone')->nullable();
            $table->string('landline_phone')->nullable();

            $table->integer('nationality')->unsigned()->nullable();
            $table->foreign('nationality')->references('id')->on('catalogs');

            $table->integer('region')->unsigned()->nullable();
            $table->foreign('region')->references('id')->on('catalogs');

            $table->string('address')->nullable();
            $table->string('province')->nullable();
            $table->string('canton')->nullable();
            $table->string('parish')->nullable();

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
        Schema::dropIfExists('people');
    }
};
