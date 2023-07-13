<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalRepresentative extends Model
{
    use HasFactory;

    protected $fillable = [
        'person',
        'relationship',
    ];

    public $timestamps = false;

    public function person()
    {
        return $this->belongsTo(Person::class, 'person');
    }
}
