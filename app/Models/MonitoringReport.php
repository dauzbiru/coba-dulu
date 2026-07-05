<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonitoringReport extends Model
{
    protected $fillable = ['gerai_id', 'user_id', 'type', 'location', 'nilai', 'grade', 'periode_start', 'periode_end', 'periode_label', 'checkin_at', 'submit_at'];

    protected $casts = [
        'checkin_at' => 'datetime',
        'submit_at' => 'datetime',
        'periode_start' => 'date',
        'periode_end' => 'date',
    ];

    public static function gradeFromScore(float $score): string
    {
        $bulat = round($score);
        if ($bulat >= 990) return 'A';
        if ($bulat >= 975) return 'B';
        if ($bulat >= 925) return 'C';
        if ($bulat >= 900) return 'D';
        return 'E';
    }

    public function gerai()
    {
        return $this->belongsTo(Gerai::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function finding()
    {
        return $this->hasOne(MonitoringFinding::class, 'monitoring_report_id');
    }
}
