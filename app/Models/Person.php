<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Person extends Model
{
    use HasFactory;

    protected $fillable = [
        'identification_type',
        'identification',
        'names',
        'last_names',
        'gender',
        'nationality',
        'date_birth',
        'place_birth',
        'disability',
        'address',
        'region',
        'province',
        'canton',
        'parish',
        'mobile_phone',
        'landline_phone'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'user');
    }

    public function identification_type()
    {
        return $this->belongsTo(Catalog::class, 'identification_type');
    }

    public function nationality()
    {
        return $this->belongsTo(Catalog::class, 'nationality');
    }

    public function region()
    {
        return $this->belongsTo(Catalog::class, 'region');
    }
}
