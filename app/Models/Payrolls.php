<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payrolls extends Model
{
    protected $fillable = [
        'employee_id',
        'pay_period',
        'basic_salary',
        'deductions',
        'bonuses',
        'total_paid',
        'status',
        'paid_at',
    ];
}
