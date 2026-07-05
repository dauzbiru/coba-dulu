<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SemesterPeriod extends Model
{
    protected $fillable = ['start_month', 'end_month', 'year'];

    protected $appends = ['label'];

    public function getLabelAttribute(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $start = $months[$this->start_month] ?? $this->start_month;
        $end = $months[$this->end_month] ?? $this->end_month;

        return "{$start} - {$end} {$this->year}";
    }
}
