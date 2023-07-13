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
        Schema::create('patients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique()->nullable();

            $table->integer('person')->unsigned();
            $table->foreign('person')->references('id')->on('people');

            $table->integer('type')->unsigned();
            $table->foreign('type')->references('id')->on('catalogs');

            $table->integer('medical_record')->unsigned();
            $table->foreign('medical_record')->references('id')->on('medical_records');

            $table->integer('family_record')->unsigned();
            $table->foreign('family_record')->references('id')->on('family_records');

            $table->boolean('active')->default(true);
            $table->boolean('archived')->default(false);
            $table->timestamp('archived_at')->nullable();

            $table->integer('archived_by')->unsigned()->nullable();
            $table->foreign('archived_by')->references('id')->on('users');

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
        Schema::dropIfExists('patients');
    }
};
