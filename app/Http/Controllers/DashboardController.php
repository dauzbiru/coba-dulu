<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Gerai;
use App\Models\MonitoringReport;
use App\Models\SemesterPeriod;

class DashboardController extends Controller
{
    public function index()
    {
        $totalGerai = Gerai::count();

        $latestPeriod = SemesterPeriod::orderBy('year', 'desc')->orderBy('start_month', 'desc')->first();

        $monitoringPeriode = 0;
        $periodeLabel = '';
        if ($latestPeriod) {
            $periodeLabel = $latestPeriod->label;
            $monitoringPeriode = MonitoringReport::where('type', 'monitoring')
                ->where('periode_label', $latestPeriod->label)
                ->count();
        }

        $praMonitoringBulanIni = MonitoringReport::where('type', 'pra-monitoring')
            ->whereMonth('checkin_at', now()->month)
            ->whereYear('checkin_at', now()->year)
            ->count();

        $monitoringTerbaru = MonitoringReport::with('gerai')
            ->where('type', 'monitoring')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $praMonitoringTerbaru = MonitoringReport::with('gerai')
            ->where('type', 'pra-monitoring')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalGerai',
            'monitoringPeriode',
            'praMonitoringBulanIni',
            'periodeLabel',
            'monitoringTerbaru',
            'praMonitoringTerbaru'
        ));
    }
}
