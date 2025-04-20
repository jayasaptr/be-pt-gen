<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'note',
        'purchase_item_id',
    ];

    public function productId()
    {
        return $this->belongsTo(Products::class, 'product_id', 'id');
    }
}
