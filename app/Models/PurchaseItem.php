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

    public function purchaseId()
    {
        return $this->belongsTo(Purchases::class, 'purchase_id', 'id');
    }

    public function productId()
    {
        return $this->belongsTo(Products::class, 'product_id', 'id');
    }
}
