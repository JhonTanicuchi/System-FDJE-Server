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
        Schema::create('supplies_deliveries', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('patient')->unsigned();
            $table->foreign('patient')->references('id')->on('patients');

            $table->boolean('delivered')->default(true);
            $table->integer('delivered_by')->unsigned();
            $table->foreign('delivered_by')->references('id')->on('users');

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
        Schema::dropIfExists('supplies_deliveries');
    }
};
