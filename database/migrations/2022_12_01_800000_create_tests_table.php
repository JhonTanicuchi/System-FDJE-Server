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
        Schema::create('tests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ophthalmologist');
            $table->date('ophthalmologist_date')->nullable();
            $table->string('nephrologist');
            $table->date('nephrologist_date')->nullable();
            $table->string('podiatrist');
            $table->date('podiatrist_date')->nullable();
            $table->string('lipidic');
            $table->date('lipidic_date')->nullable();
            $table->string('thyroid');
            $table->date('thyroid_date')->nullable();

            $table->integer('state')->unsigned();
            $table->foreign('state')->references('id')->on('catalogs');

            $table->string('observations')->nullable();

            $table->boolean('delivered')->default(true);

            $table->boolean('archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->integer('archived_by')->unsigned()->nullable();
            $table->foreign('archived_by')->references('id')->on('users');
            $table->integer('patient')->unsigned();
            $table->foreign('patient')->references('id')->on('patients');

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
        Schema::dropIfExists('tests');
    }
};
