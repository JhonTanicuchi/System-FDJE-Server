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
        Schema::create('detail_supplies_deliveries', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('supplies_delivery')->unsigned();
            $table->foreign('supplies_delivery')->references('id')->on('supplies_deliveries');

            $table->integer('supply')->unsigned();
            $table->foreign('supply')->references('id')->on('catalogs');

            $table->integer('quantity')->unsigned();

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
        Schema::dropIfExists('detail_supplies_deliveries');
    }
};
