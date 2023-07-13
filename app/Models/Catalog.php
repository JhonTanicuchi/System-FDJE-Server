<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Catalog extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'value',
    ];

    public function person ()
    {
        return $this->hasMany(Person::class,'person');
    }

    public function patient()
    {
        return $this->hasMany(Patient::class,'patient');
    }

    public function detailSupplyDeliveries()
    {
        return $this->hasMany(DetailSupplyDeliveries::class);
    }
}
