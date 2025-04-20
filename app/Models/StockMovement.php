<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'note'
    ];

    public function productId()
    {
        return $this->belongsTo(Products::class, 'product_id', 'id');
    }
}
