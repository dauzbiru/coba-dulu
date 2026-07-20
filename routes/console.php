<?php

use App\Models\EvaluasiReport;
use App\Models\MonitoringReport;
use App\Models\PraMonitoringReport;
use App\Models\ReMonitoringReport;
use App\Models\Result;
use App\Models\MonitoringFinding;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('reports:cleanup', function () {
    $now = Carbon::now();
    $total = 0;

    // Monitoring: pending > 3 jam
    $cutoffMon = $now->copy()->subHours(3);
    $reports = MonitoringReport::whereNull('submit_at')
        ->where('checkin_at', '<', $cutoffMon)
        ->get();
    foreach ($reports as $r) {
        Result::where('reportable_type', MonitoringReport::class)->where('reportable_id', $r->id)->delete();
        MonitoringFinding::where('reportable_type', MonitoringReport::class)->where('reportable_id', $r->id)->delete();
        $r->delete();
        $total++;
    }
    $this->info("Monitoring: deleted {$reports->count()} report(s).");

    // Pra-Monitoring: pending > 24 jam
    $cutoffPra = $now->copy()->subHours(24);
    $reports = PraMonitoringReport::whereNull('submit_at')
        ->where('checkin_at', '<', $cutoffPra)
        ->get();
    foreach ($reports as $r) {
        Result::where('reportable_type', PraMonitoringReport::class)->where('reportable_id', $r->id)->delete();
        MonitoringFinding::where('reportable_type', PraMonitoringReport::class)->where('reportable_id', $r->id)->delete();
        $r->delete();
        $total++;
    }
    $this->info("Pra-Monitoring: deleted {$reports->count()} report(s).");

    // Re-Monitoring: pending > 3 jam
    $cutoffRe = $now->copy()->subHours(3);
    $reports = ReMonitoringReport::whereNull('submit_at')
        ->where('checkin_at', '<', $cutoffRe)
        ->get();
    foreach ($reports as $r) {
        Result::where('reportable_type', ReMonitoringReport::class)->where('reportable_id', $r->id)->delete();
        MonitoringFinding::where('reportable_type', ReMonitoringReport::class)->where('reportable_id', $r->id)->delete();
        $r->delete();
        $total++;
    }
    $this->info("Re-Monitoring: deleted {$reports->count()} report(s).");

    // Evaluasi: pending > 1 jam
    $cutoffEval = $now->copy()->subHours(1);
    $reports = EvaluasiReport::whereNull('tanggal')
        ->where('created_at', '<', $cutoffEval)
        ->get();
    foreach ($reports as $r) {
        $r->delete();
        $total++;
    }
    $this->info("Evaluasi: deleted {$reports->count()} report(s).");

    $this->info("Total deleted: {$total} report(s).");
})->purpose('Delete unsubmitted pending reports based on timeout rules');
