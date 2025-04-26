<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendances extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'check_in_time',
        'check_out_time',
        'note',
    ];

    public function employeeId()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'id');
    }
}
