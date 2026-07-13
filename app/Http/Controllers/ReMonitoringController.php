<?php

namespace App\Http\Controllers;

use App\Models\Gerai;
use App\Models\MonitoringReport;
use App\Models\Result;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReMonitoringController extends MonitoringController
{
    protected $type = 're-monitoring';

    public function checkinForm(Gerai $gerai)
    {
        $pending = $this->pendingReport();
        if ($pending) {
            return redirect("/{$this->prefix()}/{$pending->id}/assessment")
                ->with('warning', 'Anda masih memiliki laporan yang belum diselesaikan. Selesaikan dulu.');
        }

        return view('monitoring.checkin', compact('gerai') + ['prefix' => $this->prefix(), 'periods' => collect()]);
    }

    public function doCheckin(Request $request, Gerai $gerai)
    {
        $pending = $this->pendingReport();
        if ($pending) {
            return redirect("/{$this->prefix()}/{$pending->id}/assessment")
                ->with('warning', 'Anda masih memiliki laporan yang belum diselesaikan.');
        }

        $checkinDate = $request->input('checkin_at', now()->toDateString());

        $existing = MonitoringReport::where('gerai_id', $gerai->id)
            ->where('user_id', Auth::id())
            ->where('type', $this->type)
            ->whereDate('checkin_at', $checkinDate)
            ->exists();

        if ($existing) {
            return redirect("/{$this->prefix()}")->with('warning', 'Laporan untuk gerai ini sudah dibuat pada tanggal ini.');
        }

        $data = $request->validate([
            'location' => 'required|string|max:255',
            'checkin_at' => 'required|date',
        ]);

        $report = MonitoringReport::create([
            'gerai_id' => $gerai->id,
            'user_id' => Auth::id(),
            'type' => $this->type,
            'location' => $data['location'],
            'checkin_at' => \Carbon\Carbon::parse($data['checkin_at'] . ' ' . now()->format('H:i:s')),
        ]);

        $categories = Category::whereNull('parent_id')->with('items.criteria')->get();
        foreach ($categories as $cat) {
            foreach ($cat->items as $item) {
                if ($item->criteria->isNotEmpty()) {
                    Result::create([
                        'item_id' => $item->id,
                        'user_id' => Auth::id(),
                        'monitoring_report_id' => $report->id,
                        'criterion_id' => $item->criteria->first()->id,
                    ]);
                }
            }
        }

        return redirect("/{$this->prefix()}/{$report->id}/assessment");
    }
}
