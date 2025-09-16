<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'unit_name',
        'deskripsi_kerusakan',
        'nama_pemilik',
        'tanggal_perbaikan',
    ];
}
