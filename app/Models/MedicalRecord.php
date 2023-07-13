<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    use HasFactory;

     protected $fillable = [
        'weight',
        'size',
        'diabetes_type',
        'diagnosis_date',
        'diagnostic_period',
        'last_hb_test',
        'hb_value',
        'glucose_checks',
        'written_record',
        'single_measurement',
        'monitoring_system',
        'basal_insulin_type',
        'morning_basal_dose',
        'evening_basal_dose',
        'prandial_insulin_type',
        'breakfast_prandial_dose',
        'lunch_prandial_dose',
        'dinner_prandial_dose',
        'correction_prandial_dose',
        'has_convulsions',
        'hypoglycemia_symptoms',
        'hypoglycemia_frequency',
        'min_hypoglycemia',
        'hypoglycemia_treatment',
        'doctor',
        'doctor_name',
        'last_visit',
        'hospital_type',
        'hospital',
        'hospital_name',
        'other_disease',
        'supply_opt_in',
        'assistance_type',
    ];

    public $timestamps = false;

    public function diabetes_type()
    {
        return $this->belongsTo(Catalog::class, 'diabetes_type');
    }

    public function diagnostic_period()
    {
        return $this->belongsTo(Catalog::class, 'diagnostic_period');
    }

    public function basal_insulin_type()
    {
        return $this->belongsTo(Catalog::class, 'basal_insulin_type');
    }

    public function prandial_insulin_type()
    {
        return $this->belongsTo(Catalog::class, 'prandial_insulin_type');
    }

    public function doctor()
    {
        return $this->belongsTo(Catalog::class, 'doctor');
    }

    public function hospital_type()
    {
        return $this->belongsTo(Catalog::class, 'hospital_type');
    }

    public function hospital()
    {
        return $this->belongsTo(Catalog::class, 'hospital');
    }

    public function assistance_type()
    {
        return $this->belongsTo(Catalog::class, 'assistance_type');
    }
}
