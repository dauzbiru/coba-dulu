<?php

namespace App\Models\Concerns;

use App\Models\Result;
use App\Models\MonitoringFinding;

trait Reportable
{
    public static function gradeFromScore(float $score): string
    {
        $bulat = round($score);
        if ($bulat >= 990) return 'A';
        if ($bulat >= 975) return 'B';
        if ($bulat >= 925) return 'C';
        if ($bulat >= 900) return 'D';
        return 'E';
    }

    public function results()
    {
        return $this->morphMany(Result::class, 'reportable');
    }

    public function finding()
    {
        return $this->morphOne(MonitoringFinding::class, 'reportable');
    }

    public function gerai()
    {
        return $this->belongsTo(\App\Models\Gerai::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
