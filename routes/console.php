<?php

use App\Models\MonitoringReport;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('reports:cleanup', function () {
    $cutoff = Carbon::now()->subHours(5);
    $deleted = MonitoringReport::whereNull('submit_at')
        ->where('checkin_at', '<', $cutoff)
        ->delete();
    $this->info("Deleted {$deleted} expired report(s).");
})->purpose('Delete unsubmitted reports older than 5 hours');

