<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Komplain extends Model
{
    protected $fillable = [
        'periode',
        'tanggal_komplain',
        'kode_gerai',
        'nama_gerai',
        'uraian',
        'media_laporan',
        'kategori_laporan',
        'prioritas',
        'pic_penanganan',
        'tindak_lanjut',
        'status',
        'tanggal_follow_up',
        'tanggal_close',
        'wa_template',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_komplain' => 'date:Y-m-d',
            'tanggal_follow_up' => 'date:Y-m-d',
            'tanggal_close' => 'date:Y-m-d',
        ];
    }
}
