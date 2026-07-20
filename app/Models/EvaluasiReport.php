<?php

namespace App\Models;

use App\Models\Concerns\Reportable;
use Illuminate\Database\Eloquent\Model;

class EvaluasiReport extends Model
{
    use Reportable;

    protected $table = 'evaluasi_reports';
    protected $fillable = ['gerai_id', 'user_id', 'tanggal', 'catatan', 'keterangan'];
    protected $casts = [
        'tanggal' => 'date',
    ];
}
