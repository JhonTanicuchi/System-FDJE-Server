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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->increments('id');

            $table->float('weight')->nullable();
            $table->float('size')->nullable();

            $table->integer('diabetes_type')->unsigned();
            $table->foreign('diabetes_type')->references('id')->on('catalogs');

            $table->date('diagnosis_date');

            $table->integer('diagnostic_period')->unsigned();
            $table->foreign('diagnostic_period')->references('id')->on('catalogs');

            $table->date('last_hb_test');
            $table->float('hb_value');
            $table->integer('glucose_checks');
            $table->string('written_record');
            $table->string('single_measurement');
            $table->string('monitoring_system');

            $table->integer('basal_insulin_type')->unsigned();
            $table->foreign('basal_insulin_type')->references('id')->on('catalogs');

            $table->integer('morning_basal_dose');
            $table->integer('evening_basal_dose');

            $table->integer('prandial_insulin_type')->unsigned();
            $table->foreign('prandial_insulin_type')->references('id')->on('catalogs');

            $table->integer('breakfast_prandial_dose');
            $table->integer('lunch_prandial_dose');
            $table->integer('dinner_prandial_dose');
            $table->integer('correction_prandial_dose');

            $table->string('has_convulsions')->nullable();
            $table->string('hypoglycemia_symptoms')->nullable();
            $table->integer('hypoglycemia_frequency')->nullable();
            $table->integer('min_hypoglycemia')->nullable();
            $table->string('hypoglycemia_treatment')->nullable();

            $table->integer('doctor')->unsigned()->nullable();
            $table->foreign('doctor')->references('id')->on('catalogs');

            $table->date('last_visit')->nullable();

            $table->integer('hospital_type')->unsigned()->nullable();
            $table->foreign('hospital_type')->references('id')->on('catalogs');

            $table->integer('hospital')->unsigned()->nullable();
            $table->foreign('hospital')->references('id')->on('catalogs');

            $table->string('other_disease')->nullable();
            $table->string('supply_opt_in')->nullable();

            $table->integer('assistance_type')->unsigned()->nullable();
            $table->foreign('assistance_type')->references('id')->on('catalogs');
        });
    }
    /**
     * Reverse the migrations.
     * @return void
     */
      public function down()
     {
        Schema::dropIfExists('medical_records');
    }
};
