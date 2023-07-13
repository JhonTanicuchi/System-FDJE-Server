<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'type',
        'person',
        'legal_representative',
        'family_record',
        'social_medical',
        'medical_record',
        'type_patient',
        'type_identification',
        'identification',
        'status',
        'archived',
        'archived_at',
        'archived_by',
        'updated_at',
        'created_at',
    ];

    public function type()
    {
        return $this->belongsTo(Catalog::class, 'type');
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'person');
    }

    public function medical_record()
    {
        return $this->belongsTo(MedicalRecord::class, 'medical_record');
    }

    public function family_record()
    {
        return $this->belongsTo(FamilyRecord::class, 'family_record');
    }

    public function glycemia_test()
    {
        return $this->hasMany(GlycemiaTest::class);
    }

    public function supplyDeliveries()
    {
        return $this->hasMany(SupplyDelivery::class);
    }

    public function hemoglobinTest()
    {
        return $this->hasMany(HemoglobinTest::class, 'patient');
    }

    public function test()
    {
        return $this->hasMany(HemoglobinTest::class, 'patient');
    }

    public function archived_by()
    {
        return $this->belongsTo(Person::class, 'archived_by');
    }
}
