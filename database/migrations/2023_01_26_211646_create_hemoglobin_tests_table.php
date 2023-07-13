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
        Schema::create('hemoglobin_tests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('hb1ac_result');
            $table->date('hb1ac_date');
            $table->date('endocrinologist_date');
            $table->float('weight');
            $table->float('size');

            $table->integer('state')->unsigned();
            $table->foreign('state')->references('id')->on('catalogs');

            $table->string('observations')->nullable();

            $table->boolean('delivered')->default(true);

            $table->integer('patient')->unsigned();
            $table->foreign('patient')->references('id')->on('patients');
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
        Schema::dropIfExists('hemoglobin_tests');
    }
};
