<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesItem extends Model
{
    protected $fillable = [
        'sales_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
    ];

    public function salesId()
    {
        return $this->belongsTo(Sales::class, 'sales_id', 'id');
    }
    public function productId()
    {
        return $this->belongsTo(Products::class, 'product_id', 'id');
    }
}
