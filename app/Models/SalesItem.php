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
}
