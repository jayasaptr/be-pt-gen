<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'category_id',
        'stock',
        'price'
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategories::class);
    }
}
