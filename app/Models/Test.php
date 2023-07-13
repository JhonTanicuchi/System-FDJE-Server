<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;


    protected $fillable = [
        'ophthalmologist',
        'ophthalmologist_date',
        'nephrologist',
        'nephrologist_date',
        'podiatrist',
        'podiatrist_date',
        'lipidic',
        'lipidic_date',
        'thyroid',
        'thyroid_date',
        'patient',
        'state',
        'observations',
        'archived',
        'archived_at',
        'archived_by'

    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient');
    }

    public function state()
    {
        return $this->belongsTo(Catalog::class, 'state');
    }

    public function archived_by()
    {
        return $this->belongsTo(Person::class, 'archived_by');
    }
}
