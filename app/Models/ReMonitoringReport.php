<?php

namespace App\Models;

use App\Models\Concerns\Reportable;
use Illuminate\Database\Eloquent\Model;

class ReMonitoringReport extends Model
{
    use Reportable;

    protected $table = 're_monitoring_reports';
    protected $fillable = ['gerai_id', 'user_id', 'location', 'nilai', 'grade', 'checkin_at', 'submit_at'];
    protected $casts = [
        'checkin_at' => 'datetime',
        'submit_at' => 'datetime',
    ];
}
