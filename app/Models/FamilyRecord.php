<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'household_head',
        'housing_zone',
        'housing_type',
        'members',
        'contributions',
        'minors',
        'members_disability',
        'diabetes_problem',
        'legal_representative',
    ];

    public $timestamps = false;

    public function legal_representative()
    {
        return $this->belongsTo(LegalRepresentative::class, 'legal_representative');
    }
}
