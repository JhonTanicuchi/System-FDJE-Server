<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HemoglobinTest extends Model
{
    use HasFactory;

    protected $dates = ['created_at'];

    protected $fillable = [
        'patient',
        'hb1ac_result',
        'hb1ac_date',
        'endocrinologist_date',
        'weight',
        'size',
        'state',
        'observations',
        'archived',
        'archived_at',
        'archived_by',
        'created_at',
        'updated_at',

    ];
    public function patient()
    {
        return $this->belongsTo(Patient::class,'patient');
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
