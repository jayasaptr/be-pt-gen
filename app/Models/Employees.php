<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employees extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'position',
        'salary',
        'join_date',
    ];
}
