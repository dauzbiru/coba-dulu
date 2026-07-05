<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringFinding extends Model
{
    protected $fillable = [
        'monitoring_report_id',
        'major',
        'minor',
        'peringatan_awal',
        'pengawas',
        'rata_rata_aj',
        'tds',
        'mesin_ozon',
        'note',
        'kondisi_cat',
        'kondisi_awning',
        'kondisi_vinyl',
        'kondisi_stiker_kaca',
        'ttd_petugas',
        'ttd_pimpinan',
        'penjelasan_isi',
        'penjelasan_isi_3',
    ];

    protected $casts = [
        'penjelasan_isi' => 'array',
        'penjelasan_isi_3' => 'array',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(MonitoringReport::class, 'monitoring_report_id');
    }
}
