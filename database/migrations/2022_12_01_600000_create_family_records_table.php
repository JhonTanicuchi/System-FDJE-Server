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
        Schema::create('family_records', function (Blueprint $table) {
            $table->increments('id');

            $table->string('household_head')->nullable();
            $table->string('housing_zone')->nullable();
            $table->string('housing_type')->nullable();
            $table->integer('members')->nullable();
            $table->integer('contributions')->nullable();
            $table->integer('minors')->nullable();
            $table->string('members_disability')->nullable();
            $table->string('diabetes_problem')->nullable();

            $table->integer('legal_representative')->unsigned()->nullable();
            $table->foreign('legal_representative')->references('id')->on('legal_representatives');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('family_records');
    }
};
