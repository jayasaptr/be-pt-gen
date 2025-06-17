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
        'sales_item_id',
    ];

    public function productId()
    {
        return $this->belongsTo(Products::class, 'product_id', 'id');
    }

    public function purchaseItemId()
    {
        return $this->belongsTo(PurchaseItem::class, 'purchase_item_id', 'id');
    }

    public function salesItemId()
    {
        return $this->belongsTo(SalesItem::class, 'sales_item_id', 'id');
    }
}
