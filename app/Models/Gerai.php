<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gerai extends Model
{
    protected $fillable = ['kode_gerai', 'nama_gerai', 'franchisee', 'opening_at'];

    protected function casts(): array
    {
        return [
            'opening_at' => 'date:Y-m-d',
        ];
    }
}
