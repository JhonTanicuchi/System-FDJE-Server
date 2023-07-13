<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailSuppliesDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplies_delivery',
        'supply',
        'quantity',
    ];

    public function suppliesDelivery()
    {
        return $this->belongsTo(SuppliesDelivery::class, 'supplies_delivery');
    }

    public function supply()
    {
        return $this->belongsTo(Catalog::class, 'supply');
    }
}
