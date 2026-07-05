<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $fillable = ['item_id', 'user_id', 'criterion_id', 'notes', 'monitoring_report_id'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function criterion()
    {
        return $this->belongsTo(Criterion::class);
    }

    public function monitoringReport()
    {
        return $this->belongsTo(MonitoringReport::class);
    }
}
