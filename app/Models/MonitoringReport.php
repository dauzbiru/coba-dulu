<?php

namespace App\Models;

use App\Models\Concerns\Reportable;
use Illuminate\Database\Eloquent\Model;

class MonitoringReport extends Model
{
    use Reportable;

    protected $fillable = ['gerai_id', 'user_id', 'type', 'location', 'nilai', 'grade', 'periode_label', 'checkin_at', 'submit_at', 'catatan', 'keterangan'];
    protected $casts = [
        'checkin_at' => 'datetime',
        'submit_at' => 'datetime',
    ];
}
