<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchases extends Model
{
    protected $fillable = [
        'supplier_id',
        'purchase_date',
        'total_amount',
        'status'
    ];

    public function supplierId()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    
}
