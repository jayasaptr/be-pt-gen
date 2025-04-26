<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    protected $fillable = [
        'transaction_type',
        'reference_type',
        'reference_id',
        'amount',
        'description',
        'transaction_date'
    ];

    public function salesId()
    {
        return $this->belongsTo(Sales::class, 'reference_id', 'id');
    }
    public function purchaseId()
    {
        return $this->belongsTo(Purchases::class, 'reference_id', 'id');
    }
}
