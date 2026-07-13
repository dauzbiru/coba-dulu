<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Gerai;
use App\Models\MonitoringReport;
use App\Models\SemesterPeriod;

class DashboardController extends Controller
{
    public function index()
    {
        if (Auth::user()->role === 'guest') {
            return redirect('/monitoring');
        }

        $totalGerai = Gerai::active()->count();

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

        $periods = SemesterPeriod::orderBy('year', 'desc')->orderBy('start_month', 'desc')->get();

        $chartPeriods = $periods->take(3);
        $chartData = [];
        $chartLabels = [];
        foreach ($chartPeriods as $p) {
            $chartLabels[] = $p->label;
            $nilaiList = MonitoringReport::whereIn('type', ['monitoring', 'import'])
                ->where('periode_label', $p->label)
                ->whereNotNull('nilai')
                ->pluck('nilai');

            $counts = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0];
            foreach ($nilaiList as $nilai) {
                $grade = MonitoringReport::gradeFromScore((float) $nilai);
                $counts[$grade]++;
            }

            $chartData[] = $counts;
        }

        $chartLabels = array_reverse($chartLabels);
        $chartData = array_reverse($chartData);

        $selectedPeriod = $chartLabels[count($chartLabels) - 1] ?? null;

        return view('dashboard', compact(
            'totalGerai',
            'monitoringPeriode',
            'praMonitoringBulanIni',
            'periodeLabel',
            'monitoringTerbaru',
            'praMonitoringTerbaru',
            'periods',
            'chartLabels',
            'chartData',
            'selectedPeriod'
        ));
    }

    public function chartData(Request $request)
    {
        $periodLabel = $request->input('period');

        $periods = SemesterPeriod::orderBy('year', 'desc')->orderBy('start_month', 'desc')->get();

        $selectedIndex = $periods->search(fn($p) => $p->label === $periodLabel);
        if ($selectedIndex === false) {
            $selectedIndex = 0;
        }

        $takePeriods = $periods->slice($selectedIndex, 3);
        if ($takePeriods->count() < 3) {
            $takePeriods = $periods->take(3);
        }

        $labels = [];
        $data = [];
        foreach ($takePeriods as $p) {
            $labels[] = $p->label;
            $nilaiList = MonitoringReport::whereIn('type', ['monitoring', 'import'])
                ->where('periode_label', $p->label)
                ->whereNotNull('nilai')
                ->pluck('nilai');

            $counts = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0];
            foreach ($nilaiList as $nilai) {
                $grade = MonitoringReport::gradeFromScore((float) $nilai);
                $counts[$grade]++;
            }

            $data[] = $counts;
        }

        $labels = array_reverse($labels);
        $data = array_reverse($data);

        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }
}
