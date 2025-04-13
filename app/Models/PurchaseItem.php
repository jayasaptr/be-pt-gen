<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'price',
        'total'
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchases::class);
    }

    public function product()
    {
        return $this->belongsTo(Products::class);
    }
}
