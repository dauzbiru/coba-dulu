<?php

namespace App\Models;

use App\Models\Concerns\Reportable;
use Illuminate\Database\Eloquent\Model;

class PraMonitoringReport extends Model
{
    use Reportable;

    protected $table = 'pra_monitoring_reports';
    protected $fillable = ['gerai_id', 'user_id', 'location', 'nilai', 'grade', 'checkin_at', 'submit_at'];
    protected $casts = [
        'checkin_at' => 'datetime',
        'submit_at' => 'datetime',
    ];
}
