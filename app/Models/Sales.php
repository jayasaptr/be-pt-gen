<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    protected $fillable = [
        'customer_id',
        'sales_date',
        'sales_amount',
        'sales_status',
    ];

    public function customerId()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? false, function ($query, $search) {
            $query->where('sales_date', 'like', '%' . $search . '%')
                ->orWhere('sales_amount', 'like', '%' . $search . '%')
                ->orWhere('sales_status', 'like', '%' . $search . '%');
        });
    }
}
