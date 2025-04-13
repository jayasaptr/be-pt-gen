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
}
