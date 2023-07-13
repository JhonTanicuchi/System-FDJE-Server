<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuppliesDelivery extends Model
{
    use HasFactory;

    protected $dates = ['created_at'];

    protected $fillable = [
        'patient',
        'date',
        'status',
        'delivered',
        'delivered_by',
        'archived',
        'archived_at',
        'archived_by',
        'created_at',
        'updated_at',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient');
    }

    public function detail_supplies_delivery()
    {
        return $this->hasMany(DetailSuppliesDelivery::class, 'supplies_delivery');
    }

    public function delivered_by()
    {
        return $this->belongsTo(Person::class, 'delivered_by');
    }

    public function archived_by()
    {
        return $this->belongsTo(Person::class, 'archived_by');
    }
}
