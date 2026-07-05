<?php

namespace App\Http\Controllers;

use App\Models\Gerai;
use App\Models\Category;
use App\Models\Result;
use App\Models\MonitoringReport;
use App\Models\MonitoringFinding;
use App\Models\PenjelasanFormulir;
use App\Models\SemesterPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use ZipArchive;
use DOMDocument;
use DOMXPath;
use Barryvdh\DomPDF\Facade\Pdf;

class MonitoringController extends Controller
{
    protected $type = 'monitoring';

    protected function prefix()
    {
        return $this->type === 'pra-monitoring' ? 'pra-monitoring' : 'monitoring';
    }

    public function selectGerai()
    {
        $pending = $this->pendingReport();
        if ($pending) {
            return redirect("/{$this->prefix()}/{$pending->id}/assessment")
                ->with('warning', 'Anda masih memiliki laporan yang belum diselesaikan. Selesaikan dulu.');
        }

        $gerais = Gerai::orderBy('kode_gerai')->get();

        $todayReportGeraiIds = MonitoringReport::where('user_id', Auth::id())
            ->where('type', $this->type)
            ->whereDate('checkin_at', now()->toDateString())
            ->pluck('gerai_id')
            ->toArray();

        return view('monitoring.select-gerai', compact('gerais', 'todayReportGeraiIds') + ['prefix' => $this->prefix()]);
    }

    public function checkinForm(Gerai $gerai)
    {
        $pending = $this->pendingReport();
        if ($pending) {
            return redirect("/{$this->prefix()}/{$pending->id}/assessment")
                ->with('warning', 'Anda masih memiliki laporan yang belum diselesaikan. Selesaikan dulu.');
        }

        $periods = SemesterPeriod::where('year', now()->year)->orderBy('start_month')->get();

        return view('monitoring.checkin', compact('gerai', 'periods') + ['prefix' => $this->prefix()]);
    }

    public function doCheckin(Request $request, Gerai $gerai)
    {
        $pending = $this->pendingReport();
        if ($pending) {
            return redirect("/{$this->prefix()}/{$pending->id}/assessment")
                ->with('warning', 'Anda masih memiliki laporan yang belum diselesaikan.');
        }

        $existing = MonitoringReport::where('gerai_id', $gerai->id)
            ->where('user_id', Auth::id())
            ->where('type', $this->type)
            ->whereDate('checkin_at', now()->toDateString())
            ->exists();

        if ($existing) {
            return redirect("/{$this->prefix()}")->with('warning', 'Laporan untuk gerai ini sudah dibuat hari ini.');
        }

        $data = $request->validate([
            'location' => 'required|string|max:255',
            'periode_label' => 'required|string|max:100',
            'checkin_at' => 'required|date',
        ]);

        $report = MonitoringReport::create([
            'gerai_id' => $gerai->id,
            'user_id' => Auth::id(),
            'type' => $this->type,
            'location' => $data['location'],
            'periode_label' => $data['periode_label'],
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

    public function assessment(MonitoringReport $report)
    {

        $categories = Category::whereNull('parent_id')
            ->with('items.criteria')
            ->orderBy('sort')
            ->get();

        $results = Result::where('monitoring_report_id', $report->id)
            ->get()
            ->keyBy('item_id');

        $totalScore = 0;
        $catScores = [];
        foreach ($categories as $cat) {
            $catScore = 0;
            $catMax = 0;
            foreach ($cat->items as $item) {
                $result = $results->get($item->id);
                if ($item->bobot) {
                    $catMax += $item->bobot;
                }
                if ($result && $result->criterion_id && $item->bobot) {
                    $criteria = $item->criteria;
                    $count = $criteria->count();
                    if ($count > 1) {
                        $interval = $item->bobot / ($count - 1);
                        $idx = $criteria->search(fn($c) => $c->id === $result->criterion_id);
                        if ($idx !== false) {
                            $val = $item->bobot - ($interval * $idx);
                            $catScore += $val;
                            $totalScore += $val;
                        }
                    }
                }
            }
            $catScores[$cat->id] = ['score' => $catScore, 'max' => $catMax];
        }

        // check temuan completeness
        $finding = $report->finding;
        $incomplete = [];
        $prefix = $this->prefix();

        if (!$finding) {
            $incomplete[] = 'Pengisian Temuan';
        } else {
            $temuanFields = ['pengawas', 'rata_rata_aj', 'mesin_ozon', 'peringatan_awal'];
            if ($prefix !== 'pra-monitoring') {
                $temuanFields[] = 'tds';
            }
            foreach ($temuanFields as $f) {
                if (empty(trim($finding->$f ?? ''))) {
                    $incomplete[] = 'Pengisian Temuan';
                    break;
                }
            }

            if (empty($finding->ttd_petugas)) {
                $incomplete[] = 'TTD Petugas';
            }
            if (empty($finding->ttd_pimpinan)) {
                $incomplete[] = 'TTD Pimpinan';
            }

            // check penjelasan formulir 2
            if (!empty($finding->penjelasan_isi) && is_array($finding->penjelasan_isi)) {
                foreach ($finding->penjelasan_isi as $val) {
                    if (empty(trim($val))) {
                        $incomplete[] = 'Penjelasan Formulir 2';
                        break;
                    }
                }
            }

            // check penjelasan formulir 3
            if (!empty($finding->penjelasan_isi_3) && is_array($finding->penjelasan_isi_3)) {
                foreach ($finding->penjelasan_isi_3 as $val) {
                    if (empty(trim($val))) {
                        $incomplete[] = 'Penjelasan Formulir 3';
                        break;
                    }
                }
            }
        }

        $snapshot = null;
        if ($report->submit_at) {
            $snapshotKey = 'assessment_snapshot_' . $report->id;
            if (!session()->has($snapshotKey)) {
                $snapshot = [
                    'results' => $results->map(fn($r) => ['item_id' => $r->item_id, 'criterion_id' => $r->criterion_id])->values()->toArray(),
                    'finding' => $finding ? $finding->toArray() : null,
                ];
                session()->put($snapshotKey, $snapshot);
            } else {
                $snapshot = session()->get($snapshotKey);
            }
        }

        return view('monitoring.assessment', compact('report', 'categories', 'results', 'totalScore', 'catScores', 'incomplete', 'snapshot') + ['prefix' => $prefix]);
    }

    public function cancelAssessment(Request $request, MonitoringReport $report)
    {
        $snapshotKey = 'assessment_snapshot_' . $report->id;
        $snapshot = session()->get($snapshotKey);

        if (!$snapshot) {
            return redirect("/{$this->prefix()}")->with('warning', 'Session snapshot tidak ditemukan. Mungkin sudah kedaluwarsa.');
        }

        if ($snapshot['results'] !== null) {
            Result::where('monitoring_report_id', $report->id)->delete();
            foreach ($snapshot['results'] as $resultData) {
                Result::create([
                    'monitoring_report_id' => $report->id,
                    'user_id' => Auth::id(),
                    'item_id' => $resultData['item_id'],
                    'criterion_id' => $resultData['criterion_id'],
                ]);
            }
        }

        if (array_key_exists('finding', $snapshot)) {
            if ($snapshot['finding']) {
                $findingData = $snapshot['finding'];
                unset($findingData['id'], $findingData['created_at'], $findingData['updated_at']);
                MonitoringFinding::updateOrCreate(
                    ['monitoring_report_id' => $report->id],
                    $findingData
                );
            } else {
                $report->finding?->delete();
            }
        }

        session()->forget($snapshotKey);

        return redirect("/{$this->prefix()}/{$report->id}")->with('success', 'Perubahan berhasil dibatalkan.');
    }

    public function itemForm(MonitoringReport $report, \App\Models\Item $item)
    {
        $result = Result::where('monitoring_report_id', $report->id)
            ->where('item_id', $item->id)
            ->where('user_id', Auth::id())
            ->first();
        return view('monitoring.item-form', compact('report', 'item', 'result') + ['prefix' => $this->prefix()]);
    }

    public function assessmentForm(MonitoringReport $report, Category $category)
    {

        $category->load('items.criteria');

        $results = Result::where('monitoring_report_id', $report->id)
            ->whereIn('item_id', $category->items->pluck('id'))
            ->get()
            ->keyBy('item_id');

        return view('monitoring.assessment-form', compact('report', 'category', 'results') + ['prefix' => $this->prefix()]);
    }

    public function saveAssessmentForm(Request $request, MonitoringReport $report, Category $category)
    {

        $category->load('items.criteria');

        foreach ($category->items as $item) {
            $criterionId = $request->input("criterion.{$item->id}");
            if ($criterionId) {
                Result::updateOrCreate(
                    [
                        'item_id' => $item->id,
                        'user_id' => Auth::id(),
                        'monitoring_report_id' => $report->id,
                    ],
                    ['criterion_id' => $criterionId]
                );
            }
        }

        return redirect("/{$this->prefix()}/{$report->id}/assessment")->with('success', 'Penilaian berhasil disimpan.');
    }

    public function temuanForm(MonitoringReport $report)
    {

        $finding = $report->finding;

        $results = Result::where('monitoring_report_id', $report->id)->get()->keyBy('item_id');

        $groups = $this->penjelasanGroups();
        $groupLabels = [];

        foreach ($groups as $group) {
            $achieved = 0;
            $maxBobot = 0;
            foreach ($group['item_ids'] as $itemId) {
                $result = $results->get($itemId);
                if (!$result) continue;
                $item = \App\Models\Item::with('criteria')->find($itemId);
                if (!$item || !$item->bobot) continue;
                $criteria = $item->criteria;
                $count = $criteria->count();
                if ($count <= 1) continue;
                $interval = $item->bobot / ($count - 1);
                $idx = $criteria->search(fn($c) => $c->id === $result->criterion_id);
                if ($idx !== false) {
                    $achieved += $item->bobot - ($interval * $idx);
                }
                $maxBobot += $item->bobot;
            }
            if ($maxBobot > 0) {
                $pct = ($achieved / $maxBobot) * 100;
                if ($pct <= 85) {
                    $groupLabels[] = $group['name'];
                }
            }
        }

        if (empty($groupLabels)) {
            $groupLabels[] = 'Non Temuan';
        }

        $penjelasanItems = PenjelasanFormulir::where('formulir', 2)->orderBy('sort')->get();
        $penjelasanItems3 = PenjelasanFormulir::where('formulir', 3)->orderBy('sort')->get();

        $zeroScoreItems = [];
        $allItems = \App\Models\Item::with('criteria')->get();
        foreach ($allItems as $item) {
            if (!$item->bobot || $item->criteria->count() <= 1) continue;
            $result = $results->get($item->id);
            if (!$result || !$result->criterion_id) continue;
            $criteria = $item->criteria->sortBy('sort')->values();
            $idx = $criteria->search(fn($c) => $c->id === $result->criterion_id);
            if ($idx === $criteria->count() - 1) {
                $zeroScoreItems[] = ['id' => $item->id, 'name' => $item->name];
            }
        }

        return view('monitoring.temuan', compact('report', 'finding', 'groupLabels', 'penjelasanItems', 'penjelasanItems3', 'zeroScoreItems') + ['prefix' => $this->prefix()]);
    }

    private function penjelasanGroups(): array
    {
        return [
            ['name' => 'Keramah-tamahan, Kesigapan, Konsistensi, Kerjasama Tim', 'item_ids' => [1,2,5,6, 3,7,8,9, 10, 11]],
            ['name' => 'Kedisiplinan', 'item_ids' => [12,13,14]],
            ['name' => 'Kebersihan diri, Sikap dan tingkah laku, Bahasa, tutur kata dan bahasa tubuh', 'item_ids' => [15,16,17, 18,19,20,21, 22,23,24]],
            ['name' => 'Keterlibatan Pimpinan Gerai', 'item_ids' => [25,26,27,28,29,30,31]],
            ['name' => 'Kebersihan dan kondisi pelataran parkir', 'item_ids' => [32,33,34,35]],
            ['name' => 'Kebersihan ruang toko', 'item_ids' => [36,37,38,39,40,41]],
            ['name' => 'Kebersihan lemari pengisian, barang dagangan, identitas perusahaan & merek', 'item_ids' => [42,43,44,45,46,47,48]],
            ['name' => 'Kebersihan ruang tandon', 'item_ids' => [49,50,51,52,53,54,55]],
            ['name' => 'Kebersihan peralatan kerja dan gerai', 'item_ids' => [56,57,58,59]],
            ['name' => 'Kualitas air baku', 'item_ids' => [60,61,62,63,64]],
            ['name' => 'Kualitas air minum', 'item_ids' => [65,66,67]],
            ['name' => 'Standar kegiatan di ruang toko', 'item_ids' => [68,69,70,71,80,72,73,74]],
            ['name' => 'Standar kegiatan di ruang tandon', 'item_ids' => [81,82,83]],
            ['name' => 'Kelengkapan formulir operasional & dokumen', 'item_ids' => [75,76,77,78,79]],
        ];
    }

    public function saveTemuan(Request $request, MonitoringReport $report)
    {

        $data = $request->validate([
            'major' => 'nullable|string',
            'minor' => 'nullable|string',
            'peringatan_awal' => 'nullable|string',
            'pengawas' => 'nullable|string',
            'rata_rata_aj' => 'nullable|string',
            'tds' => 'nullable|string',
            'mesin_ozon' => 'nullable|string',
            'note' => 'nullable|string',
            'kondisi_cat' => 'nullable|string',
            'kondisi_awning' => 'nullable|string',
            'kondisi_vinyl' => 'nullable|string',
            'kondisi_stiker_kaca' => 'nullable|string',
            'ttd_petugas' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'ttd_pimpinan' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'penjelasan_isi' => 'nullable|array',
            'penjelasan_isi.*' => 'nullable|string|max:5000',
            'penjelasan_isi_3' => 'nullable|array',
            'penjelasan_isi_3.*' => 'nullable|string|max:5000',
        ]);

        $data['penjelasan_isi'] = $request->penjelasan_isi ?? [];
        $data['penjelasan_isi_3'] = $request->penjelasan_isi_3 ?? [];

        if ($request->hasFile('ttd_petugas')) {
            $data['ttd_petugas'] = $request->file('ttd_petugas')->store('ttd', 'public');
        }
        if ($request->hasFile('ttd_pimpinan')) {
            $data['ttd_pimpinan'] = $request->file('ttd_pimpinan')->store('ttd', 'public');
        }

        if (isset($data['peringatan_awal'])) {
            $lines = preg_split('/\r?\n/', $data['peringatan_awal']);
            $counter = 1;
            foreach ($lines as &$line) {
                $trimmed = trim($line);
                if (preg_match('/^(\d+)\.\s*/', $trimmed)) {
                    $rest = preg_replace('/^(\d+)\.\s*/', '', $trimmed);
                    $indent = substr($line, 0, strpos($line, $trimmed[0]));
                    $line = $indent . $counter . '. ' . $rest;
                    $counter++;
                }
            }
            $data['peringatan_awal'] = implode("\n", $lines);
        }

        if ($this->type === 'pra-monitoring') {
            unset($data['tds']);
        }

        MonitoringFinding::updateOrCreate(
            ['monitoring_report_id' => $report->id],
            $data
        );

        return redirect("/{$this->prefix()}/{$report->id}/assessment")->with('success', 'Temuan monitoring berhasil disimpan.');
    }

    public function submit(Request $request, MonitoringReport $report)
    {

        $savedResults = Result::where('monitoring_report_id', $report->id)
            ->where('user_id', Auth::id())
            ->whereNotNull('criterion_id')
            ->pluck('item_id')
            ->toArray();

        $allItemIds = Category::whereNull('parent_id')
            ->with('items')
            ->get()
            ->pluck('items.*.id')
            ->flatten()
            ->toArray();

        $unfilled = array_diff($allItemIds, $savedResults);

        if (!empty($unfilled)) {
            $names = Category::whereNull('parent_id')->with('items')->get();
            $unfilledNames = [];
            foreach ($names as $cat) {
                foreach ($cat->items as $item) {
                    if (in_array($item->id, $unfilled)) {
                        $unfilledNames[] = $cat->name . ' → ' . $item->name;
                    }
                }
            }
            return back()->with('warning', 'Lengkapi semua penilaian terlebih dahulu:')
                ->with('unfilled', $unfilledNames);
        }

        // validate temuan completeness
        $finding = $report->finding;
        $prefix = $this->prefix();
        $incomplete = [];

        if (!$finding) {
            $incomplete[] = 'Pengisian Temuan';
        } else {
            $temuanFields = ['pengawas', 'rata_rata_aj', 'mesin_ozon', 'peringatan_awal'];
            if ($prefix !== 'pra-monitoring') {
                $temuanFields[] = 'tds';
            }
            foreach ($temuanFields as $f) {
                if (empty(trim($finding->$f ?? ''))) {
                    $incomplete[] = 'Pengisian Temuan';
                    break;
                }
            }

            if (empty($finding->ttd_petugas)) {
                $incomplete[] = 'TTD Petugas';
            }
            if (empty($finding->ttd_pimpinan)) {
                $incomplete[] = 'TTD Pimpinan';
            }

            if (!empty($finding->penjelasan_isi) && is_array($finding->penjelasan_isi)) {
                foreach ($finding->penjelasan_isi as $val) {
                    if (empty(trim($val))) {
                        $incomplete[] = 'Penjelasan Formulir 2';
                        break;
                    }
                }
            }

            if (!empty($finding->penjelasan_isi_3) && is_array($finding->penjelasan_isi_3)) {
                foreach ($finding->penjelasan_isi_3 as $val) {
                    if (empty(trim($val))) {
                        $incomplete[] = 'Penjelasan Formulir 3';
                        break;
                    }
                }
            }
        }

        if (!empty($incomplete)) {
            return back()->with('warning', 'Lengkapi bagian berikut sebelum submit:')
                ->with('incomplete', $incomplete);
        }

        $total = 0;
        $report->load('results.item.criteria');
        foreach ($report->results as $result) {
            $item = $result->item;
            if (!$item || !$item->bobot) continue;
            $criteriaCount = $item->criteria->count();
            if ($criteriaCount <= 1) continue;
            $interval = $item->bobot / ($criteriaCount - 1);
            $idx = $item->criteria->search(fn($c) => $c->id === $result->criterion_id);
            if ($idx !== false) {
                $total += $item->bobot - ($interval * $idx);
            }
        }

        $grade = \App\Models\MonitoringReport::gradeFromScore($total);

        $updateData = ['nilai' => $total, 'grade' => $grade];
        if (!$report->submit_at) {
            $updateData['submit_at'] = now();
        }
        $report->update($updateData);

        session()->forget('assessment_snapshot_' . $report->id);

        return redirect("/{$this->prefix()}/{$report->id}")->with('success', 'Laporan berhasil disubmit.');
    }

    public function show(MonitoringReport $report)
    {

        $categories = Category::whereNull('parent_id')
            ->with('items.criteria')
            ->orderBy('sort')
            ->get();

        $results = Result::where('monitoring_report_id', $report->id)
            ->with('criterion')
            ->get()
            ->keyBy('item_id');

        $totalScore = 0;
        foreach ($categories as $cat) {
            foreach ($cat->items as $item) {
                $r = $results->get($item->id);
                if (!$r || !$r->criterion_id) continue;
                $criteriaCount = $item->criteria->count();
                if (!$item->bobot || $criteriaCount <= 1) continue;
                $interval = $item->bobot / ($criteriaCount - 1);
                $idx = $item->criteria->search(fn($c) => $c->id === $r->criterion_id);
                if ($idx !== false) {
                    $totalScore += $item->bobot - ($interval * $idx);
                }
            }
        }

        $filteredCategories = $categories->map(function ($cat) use ($results) {
            $cat->items = $cat->items->filter(function ($item) use ($results) {
                $r = $results->get($item->id);
                if (!$r || !$r->criterion_id) return false;
                $firstCriterionId = $item->criteria->first()?->id;
                return $r->criterion_id !== $firstCriterionId;
            })->values();
            return $cat;
        })->filter(fn($cat) => $cat->items->isNotEmpty())->values();

        return view('monitoring.show', compact('report', 'filteredCategories', 'results', 'totalScore') + ['prefix' => $this->prefix()]);
    }

    public function pdf(MonitoringReport $report)
    {

        $categories = Category::whereNull('parent_id')
            ->with('items.criteria')
            ->orderBy('sort')
            ->get();

        $results = Result::where('monitoring_report_id', $report->id)
            ->with('criterion')
            ->get()
            ->keyBy('item_id');

        $totalScore = 0;
        foreach ($categories as $cat) {
            foreach ($cat->items as $item) {
                $r = $results->get($item->id);
                if (!$r || !$r->criterion_id) continue;
                $criteriaCount = $item->criteria->count();
                if (!$item->bobot || $criteriaCount <= 1) continue;
                $interval = $item->bobot / ($criteriaCount - 1);
                $idx = $item->criteria->search(fn($c) => $c->id === $r->criterion_id);
                if ($idx !== false) {
                    $totalScore += $item->bobot - ($interval * $idx);
                }
            }
        }

        $finding = $report->finding;

        // resize TTD images to base64 to reduce PDF size
        $ttdImages = [];
        if ($finding) {
            foreach (['ttd_petugas', 'ttd_pimpinan'] as $field) {
                $ttdImages[$field] = null;
                if (!empty($finding->$field)) {
                    $path = storage_path('app/public/' . $finding->$field);
                    if (file_exists($path)) {
                        $info = @getimagesize($path);
                        if ($info) {
                            $src = $info[2] === IMAGETYPE_JPEG ? @imagecreatefromjpeg($path) : (@imagecreatefrompng($path) ?: null);
                            if ($src) {
                                $w = $info[0];
                                $h = $info[1];
                                if ($w > 200 || $h > 200) {
                                    $ratio = min(200 / $w, 200 / $h);
                                    $w = round($w * $ratio);
                                    $h = round($h * $ratio);
                                    $thumb = imagecreatetruecolor($w, $h);
                                    imagecopyresampled($thumb, $src, 0, 0, 0, 0, $w, $h, $info[0], $info[1]);
                                    imagedestroy($src);
                                    $src = $thumb;
                                }
                                ob_start();
                                imagejpeg($src, null, 70);
                                $data = ob_get_clean();
                                imagedestroy($src);
                                $ttdImages[$field] = 'data:image/jpeg;base64,' . base64_encode($data);
                            }
                        }
                    }
                }
            }
        }

        // setup Roboto font
        $fontDir = storage_path('fonts');
        if (!is_dir($fontDir)) mkdir($fontDir, 0755, true);
        $regular = $fontDir . '/Roboto-Regular.ttf';
        $bold = $fontDir . '/Roboto-Bold.ttf';
        $fontLoaded = false;
        if (!file_exists($regular) || !file_exists($bold)) {
            try {
                $ctx = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
                if (!file_exists($regular)) {
                    $data = @file_get_contents('https://github.com/google/fonts/raw/main/apache/roboto/static/Roboto-Regular.ttf', false, $ctx);
                    if ($data) file_put_contents($regular, $data);
                }
                if (!file_exists($bold)) {
                    $data = @file_get_contents('https://github.com/google/fonts/raw/main/apache/roboto/static/Roboto-Bold.ttf', false, $ctx);
                    if ($data) file_put_contents($bold, $data);
                }
            } catch (\Exception $e) {}
        }
        if (file_exists($regular) && filesize($regular) > 1000) {
            try {
                $fontMetrics = app('dompdf')->getFontMetrics();
                $fontMetrics->registerFont(['family' => 'Roboto', 'style' => 'normal', 'weight' => 'normal'], $regular);
                if (file_exists($bold) && filesize($bold) > 1000) {
                    $fontMetrics->registerFont(['family' => 'Roboto', 'style' => 'normal', 'weight' => 'bold'], $bold);
                }
                $fontLoaded = true;
            } catch (\Exception $e) {}
        }

        $revisi = request()->boolean('revisi');
        $pdf = Pdf::loadView('monitoring.pdf', compact('report', 'categories', 'results', 'totalScore', 'finding', 'fontLoaded', 'ttdImages', 'revisi') + ['prefix' => $this->prefix()]);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions(['dpi' => 72, 'defaultFont' => 'sans-serif', 'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => false]);
        $filename = ($revisi ? 'revisi-' : '') . "laporan-{$this->type}-{$report->gerai->kode_gerai}-" . $report->checkin_at->setTimezone('Asia/Jakarta')->format('Y-m-d_H.i') . ".pdf";
        return $pdf->download($filename);
    }

    public function excel(MonitoringReport $report, $outputDir = null)
    {
        set_time_limit(120);

        $templateFile = 'excel-template-' . $this->type . '.xlsx';

        if (!Storage::exists($templateFile)) {
            return back()->with('error', 'Upload template Excel terlebih dahulu di menu Template Excel.');
        }

        // calculate total score & build data
        $categories = Category::whereNull('parent_id')->with('items.criteria')->orderBy('sort')->get();
        $results = Result::where('monitoring_report_id', $report->id)->with('criterion')->get()->keyBy('item_id');
        $totalScore = 0;
        $items = [];
        foreach ($categories as $cat) {
            foreach ($cat->items as $item) {
                $r = $results->get($item->id);
                if (!$r || !$r->criterion_id) continue;
                $criteriaCount = $item->criteria->count();
                if (!$item->bobot || $criteriaCount <= 1) continue;
                $interval = $item->bobot / ($criteriaCount - 1);
                $idx = $item->criteria->search(fn($c) => $c->id === $r->criterion_id);
                if ($idx !== false) {
                    $score = $item->bobot - ($interval * $idx);
                    $totalScore += $score;
                    $items[$item->name] = [
                        'category' => $cat->name,
                        'value'    => $r->criterion->description ?? '-',
                        'score'    => $score,
                        'bobot'    => $item->bobot,
                        'notes'    => $r->notes ?? '-',
                        'item_id'  => $item->id,
                    ];
                }
            }
        }

        // Calculate avg/min per item per gerai (matching analyticsExcel approach)
        $geraiScores = [];
        $geraiBobots = [];
        $periodReports = MonitoringReport::with('gerai', 'results.item.criteria', 'results.criterion')
            ->where('type', 'monitoring')
            ->whereNotNull('submit_at')
            ->where('periode_label', $report->periode_label)
            ->get();

        foreach ($periodReports as $pr) {
            $geraiKode = $pr->gerai->kode_gerai ?? 'unknown';
            foreach ($pr->results as $result) {
                $itemId = $result->item_id;
                $item = $result->item;
                if (!$item || !$item->bobot) continue;
                $criteriaCount = $item->criteria->count();
                if ($criteriaCount <= 1) continue;
                $interval = $item->bobot / ($criteriaCount - 1);
                $idx = $item->criteria->search(fn($c) => $c->id === $result->criterion_id);
                if ($idx !== false) {
                    $score = $item->bobot - ($interval * $idx);
                    $geraiScores[$itemId][$geraiKode] = $score;
                    $geraiBobots[$itemId] = $item->bobot;
                }
            }
        }



        $finding = $report->finding;
        $tz = 'Asia/Jakarta';

        // --- Calculate group labels ---
        $groupLabels = [];
        $penjelasanGroups = $this->penjelasanGroups();
        foreach ($penjelasanGroups as $group) {
            $achieved = 0;
            $maxBobot = 0;
            foreach ($group['item_ids'] as $itemId) {
                $gr = $results->get($itemId);
                if (!$gr) continue;
                $gItem = \App\Models\Item::with('criteria')->find($itemId);
                if (!$gItem || !$gItem->bobot) continue;
                $gCriteria = $gItem->criteria;
                $gCount = $gCriteria->count();
                if ($gCount <= 1) continue;
                $gInterval = $gItem->bobot / ($gCount - 1);
                $gIdx = $gCriteria->search(fn($c) => $c->id === $gr->criterion_id);
                if ($gIdx !== false) {
                    $achieved += $gItem->bobot - ($gInterval * $gIdx);
                }
                $maxBobot += $gItem->bobot;
            }
            if ($maxBobot > 0) {
                $pct = ($achieved / $maxBobot) * 100;
                if ($pct <= 85) {
                    $groupLabels[] = $group['name'];
                }
            }
        }
        if (empty($groupLabels)) {
            $groupLabels[] = 'Non Temuan';
        }

        $findingLines = [];
        $fieldModelMap = ['minor' => 'minor', 'mayor' => 'major', 'peringatan_awal' => 'peringatan_awal'];
        foreach (['minor', 'mayor', 'peringatan_awal'] as $field) {
            $modelField = $fieldModelMap[$field];
            $text = $finding?->$modelField ?? '';
            $findingLines[$field] = $text !== '' ? preg_split('/\r?\n/', $text) : [];
        }

        $headerReplacements = [
            '{nama_gerai}'       => $report->gerai->nama_gerai,
            '{kode_gerai}'       => $report->gerai->kode_gerai,
            '{franchisee}'       => $report->gerai->franchisee,
            '{lokasi}'           => $report->location,
            '{tanggal}'          => $report->checkin_at->setTimezone($tz)->format('d-m-Y'),
            '{tanggal_lengkap}'  => $report->checkin_at->setTimezone($tz)->locale('id')->isoFormat('D MMMM YYYY'),
            '{checkin}'          => $report->checkin_at->setTimezone($tz)->format('d-m-Y H:i:s'),
            '{submit}'           => $report->submit_at ? $report->submit_at->setTimezone($tz)->format('d-m-Y H:i:s') : '-',
            '{petugas}'          => $report->user?->name ?? '-',
            '{periode}'          => strtoupper($report->periode_label ?? '-'),
            '{total_score}'      => str_replace('.', ',', (string) $totalScore),
            '{minor}'            => $finding?->minor ?? '-',
            '{mayor}'            => $finding?->major ?? '-',
            '{peringatan_awal}'  => $finding?->peringatan_awal ?? '-',
            '{type}'             => str_replace('-', ' ', ucfirst($report->type)),
        ];

        // --- Build all replacements ---
        $replacements = $headerReplacements;

        // Per-sentence finding lines
        foreach ($findingLines as $field => $lines) {
            for ($i = 0; $i < count($lines); $i++) {
                $replacements['{' . $field . '_' . ($i + 1) . '}'] = $lines[$i];
            }
        }

        // Per-item replacements
        foreach ($items as $name => $data) {
            $replacements['{item_score:' . $name . '}'] = str_replace('.', ',', (string) $data['score']);
            $replacements['{item_value:' . $name . '}'] = $data['value'];
            $replacements['{item_notes:' . $name . '}'] = $data['notes'];
        }

        // Sort by placeholder length descending to avoid partial matches
        uksort($replacements, fn($a, $b) => strlen($b) - strlen($a));

        // Build a map of item_score placeholder → numeric value (with dot separator)
        $numericScores = [];
        foreach ($items as $name => $data) {
            $numericScores['{item_score:' . $name . '}'] = $data['score'];
        }

        $templatePath = Storage::path($templateFile);
        $filename = "laporan-{$this->type}-{$report->gerai->kode_gerai}-" . $report->checkin_at->setTimezone($tz)->format('Y-m-d_H.i') . '.xlsx';
        $outPath = $outputDir ? rtrim($outputDir, '\\/') . DIRECTORY_SEPARATOR . $filename : Storage::path($filename);

        // Copy template → output (preserves all charts, images, formulas, formatting)
        copy($templatePath, $outPath);

        // Open output as ZIP and replace placeholders directly in the XML
        $zip = new ZipArchive;
        if ($zip->open($outPath) !== true) {
            return back()->with('error', 'Gagal membuka file Excel.');
        }

        // Normalize item names for matching (Unicode quotes → ASCII, collapse whitespace)
        $norm = function($s) {
            $s = str_replace(["\xC2\xAB", "\xC2\xBB", "\xE2\x80\x98", "\xE2\x80\x99",
                "\xE2\x80\x9A", "\xE2\x80\x9B", "\xE2\x80\x9C", "\xE2\x80\x9D",
                "\xE2\x80\x9E", "\xE2\x80\x9F", "\xE2\x80\xB9", "\xE2\x80\xBA"], '"', $s);
            return trim(preg_replace('/\s+/', ' ', $s));
        };

        // Build normalized lookup for item_score placeholders
        $normNumericScores = [];
        foreach ($numericScores as $placeholder => $score) {
            $normNumericScores[$norm($placeholder)] = $score;
        }

        // --- Phase 1: parse sharedStrings.xml to find indices of item_score placeholders ---
        $ssContent = $zip->getFromName('xl/sharedStrings.xml');
        $ssIndexScore = []; // shared string index → numeric score
        $ssIndexText = [];  // shared string index → original text (for Phase 2 search key)
        if ($ssContent !== false) {
            $ssDom = new DOMDocument;
            $ssDom->loadXML($ssContent);
            $siNodes = $ssDom->getElementsByTagName('si');
            for ($i = 0; $i < $siNodes->length; $i++) {
                $t = $siNodes->item($i)->getElementsByTagName('t')->item(0);
                $val = $t ? $t->textContent : '';
                $normVal = $norm($val);
                if (isset($normNumericScores[$normVal])) {
                    $ssIndexScore[$i] = $normNumericScores[$normVal];
                    $ssIndexText[$i] = $val;
                }
            }
        }

        // --- Phase 2: str_replace on ALL XML files (simple text replacement) ---
        // Use database keys for general replacements
        $searchKeys = array_map(
            fn($k) => htmlspecialchars((string) $k, ENT_XML1 | ENT_NOQUOTES, 'UTF-8'),
            array_keys($replacements)
        );
        $replaceValues = array_map(
            fn($v) => htmlspecialchars((string) $v, ENT_XML1 | ENT_NOQUOTES, 'UTF-8'),
            array_values($replacements)
        );

        // For item_score in sharedStrings.xml, use the actual XML text as search key
        $ssSearchKeys = $searchKeys;
        $ssReplaceValues = $replaceValues;
        foreach ($ssIndexText as $idx => $text) {
            $score = $ssIndexScore[$idx];
            $ssSearchKeys[] = htmlspecialchars($text, ENT_XML1 | ENT_NOQUOTES, 'UTF-8');
            $ssReplaceValues[] = str_replace('.', ',', (string) $score);
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!str_starts_with($name, 'xl/')) continue;
            if (!str_ends_with($name, '.xml')) continue;

            $content = $zip->getFromIndex($i);
            if ($content === false || $content === '') continue;

            $keys = ($name === 'xl/sharedStrings.xml') ? $ssSearchKeys : $searchKeys;
            $vals = ($name === 'xl/sharedStrings.xml') ? $ssReplaceValues : $replaceValues;

            $newContent = str_replace($keys, $vals, $content);

            if ($newContent !== $content) {
                $zip->addFromString($name, $newContent);
            }
        }

        // Force full recalculation of formulas on open
        $wbContent = $zip->getFromName('xl/workbook.xml');
        if ($wbContent !== false) {
            $wbContent = preg_replace('/<calcPr\s[^>]*\/>/', '<calcPr calcId="181029" fullCalcOnLoad="1"/>', $wbContent, 1);
            $zip->addFromString('xl/workbook.xml', $wbContent);
        }

        // --- Fill B44 in FORMULIR HASIL 1 with grade text ---
        $grade = \App\Models\MonitoringReport::gradeFromScore((float) $totalScore);
        $sheet1Content = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheet1Content !== false) {
            $dom1 = new DOMDocument;
            $dom1->loadXML($sheet1Content);
            $xpath1 = new DOMXPath($dom1);
            $ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
            $xpath1->registerNamespace('s', $ns);

            $cells = $xpath1->query("//s:c[@r='B44']");
            if ($cells->length > 0) {
                $cell = $cells->item(0);
                $cell->setAttribute('t', 'inlineStr');

                foreach (['v', 'is'] as $tag) {
                    $existing = $cell->getElementsByTagNameNS($ns, $tag)->item(0);
                    if ($existing) $cell->removeChild($existing);
                }

                $is = $dom1->createElementNS($ns, 'is');

                $makeRun = function($text, $bold = false) use ($dom1, $ns) {
                    $r = $dom1->createElementNS($ns, 'r');
                    $rPr = $dom1->createElementNS($ns, 'rPr');
                    if ($bold) {
                        $rPr->appendChild($dom1->createElementNS($ns, 'b'));
                    }
                    $rFont = $dom1->createElementNS($ns, 'rFont');
                    $rFont->setAttribute('val', 'Arimo');
                    $rPr->appendChild($rFont);
                    $sz = $dom1->createElementNS($ns, 'sz');
                    $sz->setAttribute('val', '12');
                    $rPr->appendChild($sz);
                    $color = $dom1->createElementNS($ns, 'color');
                    $color->setAttribute('rgb', 'FF000000');
                    $rPr->appendChild($color);
                    $r->appendChild($rPr);
                    $t = $dom1->createElementNS($ns, 't');
                    $t->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
                    $t->appendChild($dom1->createTextNode($text));
                    $r->appendChild($t);
                    return $r;
                };

                $is->appendChild($makeRun('Gerai masuk dalam '));
                $is->appendChild($makeRun("Grade {$grade}", true));
                $is->appendChild($makeRun(' dengan kategori:'));

                $cell->appendChild($is);
            }

            // --- Fill E9 (previous period score) and G9 (current score) ---
            $prevTotalScore = null;
            $periods = MonitoringReport::where('gerai_id', $report->gerai_id)
                ->whereNotNull('submit_at')
                ->selectRaw('periode_label, MAX(checkin_at) as last_checkin')
                ->groupBy('periode_label')
                ->orderByRaw('MAX(checkin_at) desc')
                ->pluck('periode_label');
            $currentIdx = $periods->search($report->periode_label);

            if ($currentIdx !== false && $currentIdx < $periods->count() - 1) {
                $prevPeriodLabel = $periods->values()[$currentIdx + 1];
                $prevReport = MonitoringReport::where('gerai_id', $report->gerai_id)
                    ->where('periode_label', $prevPeriodLabel)
                    ->whereNotNull('submit_at')
                    ->latest('checkin_at')
                    ->first();
                if ($prevReport) {
                    if (is_numeric($prevReport->nilai)) {
                        $prevTotalScore = (float) $prevReport->nilai;
                    } else {
                        $prevResults = Result::where('monitoring_report_id', $prevReport->id)
                            ->get()->keyBy('item_id');
                        $prevCategories = Category::whereNull('parent_id')->with('items.criteria')->orderBy('sort')->get();
                        foreach ($prevCategories as $cat) {
                            foreach ($cat->items as $item) {
                                $r = $prevResults->get($item->id);
                                if (!$r || !$r->criterion_id) continue;
                                $criteriaCount = $item->criteria->count();
                                if (!$item->bobot || $criteriaCount <= 1) continue;
                                $interval = $item->bobot / ($criteriaCount - 1);
                                $idx = $item->criteria->search(fn($c) => $c->id === $r->criterion_id);
                                if ($idx !== false) {
                                    $prevTotalScore += $item->bobot - ($interval * $idx);
                                }
                            }
                        }
                    }
                }
            }

            foreach (['E' => $prevTotalScore, 'G' => $totalScore] as $col => $score) {
                if ($score === null) continue;
                $ref = $col . '9';
                $cells = $xpath1->query("//s:c[@r='$ref']");
                if ($cells->length > 0) {
                    $cell = $cells->item(0);
                    foreach (['v', 'is', 'f'] as $tag) {
                        $existing = $cell->getElementsByTagNameNS($ns, $tag)->item(0);
                        if ($existing) $cell->removeChild($existing);
                    }
                    $cell->removeAttribute('t');
                    $v = $dom1->createElementNS($ns, 'v');
                    $v->textContent = (string) round($score);
                    $cell->appendChild($v);
                }
            }

            $zip->addFromString('xl/worksheets/sheet1.xml', $dom1->saveXML());
        }

        // --- Phase 3: convert item_score shared string cells to number type ---
        $sheet3ZeroItems = [];
        if (!empty($ssIndexScore)) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (!str_starts_with($name, 'xl/worksheets/')) continue;
                if (!str_ends_with($name, '.xml')) continue;

                $content = $zip->getFromIndex($i);
                if ($content === false || $content === '') continue;

                $dom = new DOMDocument;
                $dom->loadXML($content);
                $changed = false;

                foreach ($dom->getElementsByTagName('c') as $cell) {
                    if ($cell->getAttribute('t') !== 's') continue;

                    $v = $cell->getElementsByTagName('v')->item(0);
                    if (!$v) continue;

                    $idx = (int) $v->textContent;
                    if (!isset($ssIndexScore[$idx])) continue;

                    // Convert to number cell
                    $cell->removeAttribute('t');
                    $v->textContent = $ssIndexScore[$idx]; // decimal dot separator
                    $changed = true;

                    // Track zero-score checkbox items in sheet3 (extract name from placeholder)
                    if ($name === 'xl/worksheets/sheet3.xml' && (int)$ssIndexScore[$idx] === 0) {
                        $placeholder = $ssIndexText[$idx] ?? '';
                        if (preg_match('/\{item_score:(.*?)\}/', $placeholder, $m)) {
                            $sheet3ZeroItems[] = trim($m[1]);
                        }
                    }
                }

                if ($changed) {
                    $newContent = $dom->saveXML();
                    $zip->addFromString($name, $newContent);
                }
            }
        }

        // --- Phase 0 (early): Build group→items mapping from original template ---
        $getItemRows = function($xpath3, $rowNum, &$visited) use (&$getItemRows) {
            if (in_array($rowNum, $visited)) return [];
            $visited[] = $rowNum;
            $iRef = 'I' . $rowNum;
            $cells = $xpath3->query("//s:c[@r='$iRef']");
            foreach ($cells as $cell) {
                $f = $cell->getElementsByTagName('f')->item(0);
                if (!$f) {
                    if ($cell->getAttribute('t') === 's') {
                        return [$rowNum];
                    }
                    return [];
                }
                $formula = $f->textContent;
                if (preg_match('/^I(\d+)$/', $formula, $m)) {
                    return $getItemRows($xpath3, (int)$m[1], $visited);
                }
                if (preg_match('/^SUM\(I(\d+):I(\d+)\)$/', $formula, $m)) {
                    $results = [];
                    for ($r = (int)$m[1]; $r <= (int)$m[2]; $r++) {
                        $results = array_merge($results, $getItemRows($xpath3, $r, $visited));
                    }
                    return $results;
                }
                if (preg_match_all('/I(\d+)(?!\d)/', $formula, $m)) {
                    $results = [];
                    foreach ($m[1] as $r) {
                        $r = (int)$r;
                        if ($r !== $rowNum) $results = array_merge($results, $getItemRows($xpath3, $r, $visited));
                    }
                    return array_unique($results);
                }
                return [];
            }
            return [];
        };

        $tmpZip = new ZipArchive;
        if ($tmpZip->open($templatePath) === true) {
            $tmpSS = $tmpZip->getFromName('xl/sharedStrings.xml');
            $tmpS3 = $tmpZip->getFromName('xl/worksheets/sheet3.xml');
            $tmpS2 = $tmpZip->getFromName('xl/worksheets/sheet2.xml');
            $tmpZip->close();

            if ($tmpSS && $tmpS3 && $tmpS2) {
                $ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

                $ssDom0 = new DOMDocument;
                $ssDom0->loadXML($tmpSS);
                $siNodes0 = $ssDom0->getElementsByTagName('si');
                $ssText0 = [];
                for ($i = 0; $i < $siNodes0->length; $i++) {
                    $t = $siNodes0->item($i)->getElementsByTagName('t')->item(0);
                    $ssText0[$i] = $t ? $t->textContent : '';
                }

                $dom3 = new DOMDocument;
                $dom3->loadXML($tmpS3);
                $xpath3 = new DOMXPath($dom3);
                $xpath3->registerNamespace('s', $ns);

                $dom2 = new DOMDocument;
                $dom2->loadXML($tmpS2);
                $xpath2 = new DOMXPath($dom2);
                $xpath2->registerNamespace('s', $ns);

                // Parse sheet2 H cells → J row mapping
                $hCells0 = [];
                foreach ($xpath2->query('//s:c[s:f]') as $cell) {
                    $ref = $cell->getAttribute('r');
                    if (!str_starts_with($ref, 'H')) continue;
                    $hRow = (int)substr($ref, 1);
                    $f = $cell->getElementsByTagName('f')->item(0);
                    if (preg_match('/J(\d+)/', $f->textContent, $m)) {
                        $jRow = (int)$m[1];
                        $cRef = 'C' . $hRow;
                        $cLabel = '';
                        foreach ($xpath2->query("//s:c[@r='$cRef']") as $cc) {
                            if ($cc->getAttribute('t') === 's') {
                                $v = $cc->getElementsByTagName('v')->item(0);
                                if ($v) {
                                    $idx = (int)$v->textContent;
                                    if (isset($ssText0[$idx])) $cLabel = trim($ssText0[$idx]);
                                }
                            }
                        }
                        $hCells0[$hRow] = ['jRow' => $jRow, 'cLabel' => $cLabel];
                    }
                }

                // Build normalized lookup from $items keyed by norm({item_score:name})
                $itemsByPlaceholder = [];
                foreach ($items as $name => $data) {
                    $key = $norm('{item_score:' . $name . '}');
                    $itemsByPlaceholder[$key] = $data['score'];
                }

                // For each H cell, resolve I formula recursively → get item rows
                foreach ($hCells0 as $hRow => &$info) {
                    $catRow = $info['jRow'];
                    $visited = [];
                    $itemRows = $getItemRows($xpath3, $catRow, $visited);
                    $info['itemRows'] = $itemRows;

                    // Get F value from template
                    $fRef = 'F' . $catRow;
                    $fValue = 0;
                    foreach ($xpath3->query("//s:c[@r='$fRef']") as $fc) {
                        $fv = $fc->getElementsByTagName('v')->item(0);
                        if ($fv) $fValue = (float)$fv->textContent;
                    }
                    $info['fValue'] = $fValue;

                    // Get item_score placeholder text from each terminal item row's I column
                    $itemPlaceholders = [];
                    foreach ($itemRows as $ir) {
                        $iRef = 'I' . $ir;
                        foreach ($xpath3->query("//s:c[@r='$iRef']") as $ic) {
                            if ($ic->getAttribute('t') === 's') {
                                $v = $ic->getElementsByTagName('v')->item(0);
                                if ($v) {
                                    $idx = (int)$v->textContent;
                                    if (isset($ssText0[$idx])) {
                                        $itemPlaceholders[] = $ssText0[$idx];
                                    }
                                }
                            }
                        }
                    }
                    $info['itemPlaceholders'] = $itemPlaceholders;
                }
                unset($info);

                // Build low-score list
                $lowItems = [];
                foreach ($hCells0 as $info) {
                    if (trim($info['cLabel']) === '') continue;
                    if ($info['fValue'] <= 0) continue;

                    $totalScore = 0;
                    $matchedAny = false;
                    foreach ($info['itemPlaceholders'] as $placeholder) {
                        $normKey = $norm($placeholder);
                        if (isset($itemsByPlaceholder[$normKey])) {
                            $totalScore += $itemsByPlaceholder[$normKey];
                            $matchedAny = true;
                        }
                    }
                    if ($matchedAny) {
                        $pct = ($totalScore / $info['fValue']) * 100;
                        if ($pct <= 85) $lowItems[] = $info['cLabel'];
                    }
                }

                // Write to sheet2
                $sheet2Content = $zip->getFromName('xl/worksheets/sheet2.xml');
                if ($sheet2Content !== false) {
                    $dom2 = new DOMDocument;
                    $dom2->preserveWhiteSpace = false;
                    $dom2->loadXML($sheet2Content);
                    $xpath2 = new DOMXPath($dom2);
                    $ns2 = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
                    $xpath2->registerNamespace('s', $ns2);

                    $isNonTemuan = count($groupLabels) === 1 && $groupLabels[0] === 'Non Temuan';
                    $penjelasanIsi = $finding ? ($finding->penjelasan_isi ?? []) : [];

                    if ($isNonTemuan) {
                        // === Non Temuan: template unchanged, paste below PENJELASAN ===
                        // Load shared strings to resolve cell text
                        $ssContent2 = $zip->getFromName('xl/sharedStrings.xml');
                        $ssTextMap = [];
                        if ($ssContent2 !== false) {
                            $ssDom2 = new DOMDocument;
                            $ssDom2->loadXML($ssContent2);
                            foreach ($ssDom2->getElementsByTagName('si') as $idx => $si) {
                                $t = $si->getElementsByTagName('t')->item(0);
                                $ssTextMap[$idx] = $t ? $t->textContent : '';
                            }
                        }

                        // Find PENJELASAN row
                        $penjelasanRowNum = 0;
                        foreach ($xpath2->query('//s:sheetData/s:row') as $row) {
                            $r = (int)$row->getAttribute('r');
                            foreach ($xpath2->query('s:c', $row) as $cell) {
                                $text = '';
                                $type = $cell->getAttribute('t');
                                if ($type === 's') {
                                    $v = $cell->getElementsByTagName('v')->item(0);
                                    if ($v) { $idx = (int)$v->textContent; $text = $ssTextMap[$idx] ?? ''; }
                                } elseif ($type === 'inlineStr') {
                                    $t = $cell->getElementsByTagName('t')->item(0);
                                    $text = $t ? $t->textContent : '';
                                }
                                if (str_contains($text, 'PENJELASAN')) { $penjelasanRowNum = $r; break 2; }
                            }
                        }

                         // Filter only non-temuan entries
                        $nonTemuanIsi = [];
                        foreach ($penjelasanIsi as $i => $teks) {
                            $label = $groupLabels[$i] ?? '';
                            if ($label === 'Non Temuan') {
                                $nonTemuanIsi[] = $teks;
                            }
                        }

                        if ($penjelasanRowNum > 0 && $nonTemuanIsi) {
                            $sheetData = $xpath2->query('//s:sheetData')->item(0);
                            // Remove rows after PENJELASAN
                            foreach ($xpath2->query('//s:sheetData/s:row[@r > ' . $penjelasanRowNum . ']') as $row) {
                                $sheetData->removeChild($row);
                            }
                            // Insert penjelasan in A after PENJELASAN row
                            $rn = $penjelasanRowNum + 1;
                            foreach ($nonTemuanIsi as $i => $teks) {
                                if (trim($teks) === '') continue;
                                $line = ($i + 1) . '. ' . trim($teks);
                                $newRow = $dom2->createElementNS($ns2, 'row');
                            $newRow->setAttribute('r', (string)$rn);
                            $newRow->setAttribute('spans', '1:13');
                            $newRow->setAttribute('ht', '15.5');
                                for ($col = 'A'; $col !== 'N'; $col++) {
                                    $ref = $col . $rn;
                                    $cell = $dom2->createElementNS($ns2, 'c');
                                    $cell->setAttribute('r', $ref);
                                    if ($col === 'A') {
                                        $cell->setAttribute('s', '1');
                                        $cell->setAttribute('t', 'inlineStr');
                                        $is = $dom2->createElementNS($ns2, 'is');
                                        $t = $dom2->createElementNS($ns2, 't');
                                        $t->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
                                        $t->appendChild($dom2->createTextNode($line));
                                        $is->appendChild($t);
                                        $cell->appendChild($is);
                                    } else { $cell->setAttribute('s', '1'); }
                                    $newRow->appendChild($cell);
                                }
                                $sheetData->appendChild($newRow);
                                $rn++;
                            }
                        }
                    } else {
                        // === Bukan Non Temuan: delete + rewrite rows 30+ ===
                        $sheetData = $xpath2->query('//s:sheetData')->item(0);

                        // Remove all rows from row 30 onwards
                        foreach ($xpath2->query('//s:sheetData/s:row[@r >= 30]') as $row) {
                            $sheetData->removeChild($row);
                        }

                        // Helper: create data row
                        $makeDataRow = function($rowNum, $colLetter, $text = null) use ($dom2, $ns2) {
                            $row = $dom2->createElementNS($ns2, 'row');
                            $row->setAttribute('r', (string)$rowNum);
                            $row->setAttribute('spans', '1:13');
                            $row->setAttribute('ht', '15.5');
                            for ($col = 'A'; $col !== 'N'; $col++) {
                                $ref = $col . $rowNum;
                                $cell = $dom2->createElementNS($ns2, 'c');
                                $cell->setAttribute('r', $ref);
                                if ($col === $colLetter && $text !== null) {
                                    $cell->setAttribute('s', '1');
                                    $cell->setAttribute('t', 'inlineStr');
                                    $is = $dom2->createElementNS($ns2, 'is');
                                    $t = $dom2->createElementNS($ns2, 't');
                                    $t->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
                                    $t->appendChild($dom2->createTextNode($text));
                                    $is->appendChild($t);
                                    $cell->appendChild($is);
                                } else { $cell->setAttribute('s', '1'); }
                                $row->appendChild($cell);
                            }
                            return $row;
                        };

                        $rn = 30;
                        // --- Low-score list (B column) ---
                        if (!empty($lowItems)) {
                            foreach ($lowItems as $i => $label) {
                                $line = ($i + 1) . '. ' . $label;
                                $sheetData->appendChild($makeDataRow($rn, 'B', $line));
                                $rn++;
                            }
                            $sheetData->appendChild($makeDataRow($rn, 'A'));
                            $rn++;
                        }
                        // PENJELASAN row
                        $penjelasanRow = $dom2->createElementNS($ns2, 'row');
                        $penjelasanRow->setAttribute('r', (string)$rn);
                        $penjelasanRow->setAttribute('spans', '1:13');
                        $penjelasanRow->setAttribute('ht', '15.5');
                        for ($col = 'A'; $col !== 'N'; $col++) {
                            $ref = $col . $rn;
                            $cell = $dom2->createElementNS($ns2, 'c');
                            $cell->setAttribute('r', $ref);
                            if ($col === 'A') {
                                $cell->setAttribute('s', '12');
                                $cell->setAttribute('t', 'inlineStr');
                                $is = $dom2->createElementNS($ns2, 'is');
                                $t = $dom2->createElementNS($ns2, 't');
                                $t->appendChild($dom2->createTextNode('PENJELASAN:'));
                                $is->appendChild($t);
                                $cell->appendChild($is);
                            } else { $cell->setAttribute('s', '1'); }
                            $penjelasanRow->appendChild($cell);
                        }
                        $sheetData->appendChild($penjelasanRow);
                        $rn++;
                        // --- Penjelasan items in B ---
                            if ($penjelasanIsi) {
                                foreach ($penjelasanIsi as $i => $teks) {
                                    if (trim($teks) === '') continue;
                                    $line = ($i + 1) . '. ' . trim($teks);
                                    $sheetData->appendChild($makeDataRow($rn, 'B', $line));
                                    $rn++;
                                }
                            }
                        }

                        $zip->addFromString('xl/worksheets/sheet2.xml', $dom2->saveXML());
                }

                // --- Sheet3: Zero-score items ---
                $sheet3Content = $zip->getFromName('xl/worksheets/sheet3.xml');
                if ($sheet3Content !== false) {
                    $dom3 = new DOMDocument;
                    $dom3->preserveWhiteSpace = false;
                    $dom3->loadXML($sheet3Content);
                    $xpath3 = new DOMXPath($dom3);
                    $ns3 = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
                    $xpath3->registerNamespace('s', $ns3);

                    $sheetData3 = $xpath3->query('//s:sheetData')->item(0);

                    // --- Sheet3: Info block rows (before zero-score, inserted between PA and NOTE) ---
                    // Load shared strings for PA/NOTE text lookup
                    $ssContent3 = $zip->getFromName('xl/sharedStrings.xml');
                    $ssTextByIndex = [];
                    if ($ssContent3 !== false) {
                        $ssDom3 = new DOMDocument;
                        $ssDom3->loadXML($ssContent3);
                        $ssXpath3 = new DOMXPath($ssDom3);
                        $ssXpath3->registerNamespace('s', $ns3);
                        foreach ($ssXpath3->query('//s:si') as $idx => $si) {
                            $t = $ssXpath3->query('.//s:t', $si)->item(0);
                            $ssTextByIndex[$idx] = $t ? $t->textContent : '';
                        }
                    }

                    $paRn = 0;
                    $noteRn = 0;
                    $paRow = null;
                    $noteRow = null;
                    foreach ($xpath3->query('//s:sheetData/s:row') as $row) {
                        $r = (int)$row->getAttribute('r');
                        foreach ($xpath3->query('s:c', $row) as $cell) {
                            $type = $cell->getAttribute('t');
                            $text = '';
                            if ($type === 's') {
                                $v = $cell->getElementsByTagName('v')->item(0);
                                if ($v) {
                                    $idx = (int)$v->textContent;
                                    $text = $ssTextByIndex[$idx] ?? '';
                                }
                            } elseif ($type === 'inlineStr') {
                                $t = $cell->getElementsByTagName('t')->item(0);
                                $text = $t ? $t->textContent : '';
                            }
                            if (str_contains($text, 'Temuan dengan kategori Peringatan Awal')) {
                                $paRn = $r;
                                $paRow = $row;
                            } elseif ($text === 'NOTE:' && $paRn > 0 && $noteRn === 0) {
                                $noteRn = $r;
                                $noteRow = $row;
                            }
                        }
                    }

                    if ($paRow && $noteRow && $finding) {
                        // Remove gap rows between PA header and NOTE
                        $rowsToRemove = [];
                        foreach ($xpath3->query('//s:sheetData/s:row[@r > ' . $paRn . ' and @r < ' . $noteRn . ']') as $row) {
                            $rowsToRemove[] = $row;
                        }
                        foreach ($rowsToRemove as $row) {
                            $sheetData3->removeChild($row);
                        }

                        // Build info block rows
                        $infoRn = $paRn + 1;
                        $makeBRow = function($text) use ($dom3, $ns3, &$infoRn) {
                            $rn = $infoRn++;
                            $row = $dom3->createElementNS($ns3, 'row');
                            $row->setAttribute('r', (string)$rn);
                            $row->setAttribute('spans', '1:15');
                            $cell = $dom3->createElementNS($ns3, 'c');
                            $cell->setAttribute('r', 'B' . $rn);
                            $cell->setAttribute('t', 'inlineStr');
                            $cell->setAttribute('s', '1');
                            $is = $dom3->createElementNS($ns3, 'is');
                            $t = $dom3->createElementNS($ns3, 't');
                            $t->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
                            $t->appendChild($dom3->createTextNode($text));
                            $is->appendChild($t);
                            $cell->appendChild($is);
                            $row->appendChild($cell);
                            return $row;
                        };

                        $infoRows = [];
                        $pengawas = $finding->pengawas ?? '';
                        if ($pengawas !== '') {
                            foreach (preg_split('/\r?\n/', $pengawas) as $line) {
                                if (trim($line) !== '') $infoRows[] = $makeBRow(trim($line));
                            }
                        }
                        $aj = $finding->rata_rata_aj ?? '';
                        if ($aj !== '') {
                            $infoRows[] = $makeBRow('Rerata AJ ± ' . $aj . ' gln/hr');
                        }
                        if ($this->type !== 'pra-monitoring') {
                            $tds = $finding->tds ?? '';
                            if ($tds !== '') {
                                $tdsDisplay = str_replace('/', ' ppm/', $tds);
                                if (str_contains($tds, '/')) $tdsDisplay .= '°C';
                                $infoRows[] = $makeBRow('TDS: ' . $tdsDisplay);
                            }
                        }
                        $mo = $finding->mesin_ozon ?? '';
                        if ($mo !== '') {
                            $infoRows[] = $makeBRow('MO: ' . $mo);
                        }
                        $infoRows[] = $makeBRow('');
                        $paLines = $findingLines['peringatan_awal'] ?? [];
                        foreach ($paLines as $line) {
                            if (trim($line) !== '') $infoRows[] = $makeBRow($line);
                        }
                        $infoRows[] = $makeBRow('');
                        $noteContent = $finding->note ?? '';
                        if ($noteContent !== '') {
                            $infoRows[] = $makeBRow('Note:');
                            foreach (preg_split('/\r?\n/', $noteContent) as $line) {
                                if (trim($line) !== '') $infoRows[] = $makeBRow(trim($line));
                            }
                        }
                        $infoRows[] = $makeBRow('');
                        $infoRows[] = $makeBRow('Checklist tampilan gerai:');
                        $infoRows[] = $makeBRow('Kondisi cat: ' . ($finding->kondisi_cat ?: 'Baik'));
                        $infoRows[] = $makeBRow('Kondisi awning: ' . ($finding->kondisi_awning ?: 'Baik'));
                        $infoRows[] = $makeBRow('Kondisi vinyl reklame dinding/jalan: ' . ($finding->kondisi_vinyl ?: 'Baik'));
                        $infoRows[] = $makeBRow('Kondisi stiker kaca: ' . ($finding->kondisi_stiker_kaca ?: 'Baik'));
                        $infoRows[] = $makeBRow('');

                        // Insert all info rows before NOTE
                        foreach ($infoRows as $row) {
                            $sheetData3->insertBefore($row, $noteRow);
                        }

                        // Renumber rows AFTER info block (skip info rows, renumber NOTE+rest)
                        $allRows = [];
                        foreach ($sheetData3->childNodes as $child) {
                            if ($child->nodeType === XML_ELEMENT_NODE && $child->localName === 'row') {
                                $allRows[] = $child;
                            }
                        }
                        $renumberRn = $infoRn;
                        $pastPa = false;
                        $infoIdx = 0;
                        foreach ($allRows as $row) {
                            $rAttr = (int)$row->getAttribute('r');
                            if (!$pastPa) {
                                if ($rAttr === $paRn) $pastPa = true;
                                continue;
                            }
                            if ($infoIdx < count($infoRows)) {
                                $infoIdx++;
                                continue;
                            }
                            $row->setAttribute('r', (string)$renumberRn);
                            foreach ($xpath3->query('s:c', $row) as $cell) {
                                $ref = $cell->getAttribute('r');
                                $cell->setAttribute('r', preg_replace('/\d+$/', (string)$renumberRn, $ref));
                            }
                            $renumberRn++;
                        }
                    }

                    // --- Sheet3: Zero-score items ---
                    if (!empty($sheet3ZeroItems)) {
                        $n = count($sheet3ZeroItems);

                        $makeRow = function($rn, $bText = null) use ($dom3, $ns3) {
                            $row = $dom3->createElementNS($ns3, 'row');
                            $row->setAttribute('r', (string)$rn);
                            $row->setAttribute('spans', '1:13');
                            for ($col = 'A'; $col !== 'N'; $col++) {
                                $ref = $col . $rn;
                                $cell = $dom3->createElementNS($ns3, 'c');
                                $cell->setAttribute('r', $ref);
                                $cell->setAttribute('s', '1');
                                if ($col === 'B' && $bText !== null) {
                                    $cell->setAttribute('t', 'inlineStr');
                                    $is = $dom3->createElementNS($ns3, 'is');
                                    $t = $dom3->createElementNS($ns3, 't');
                                    $t->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
                                    $t->appendChild($dom3->createTextNode($bText));
                                    $is->appendChild($t);
                                    $cell->appendChild($is);
                                }
                                $row->appendChild($cell);
                            }
                            return $row;
                        };

                        $firstLine = '1. ' . $sheet3ZeroItems[0];
                        foreach ($xpath3->query('//s:sheetData/s:row[@r=120]/s:c[@r="B120"]') as $cell) {
                            while ($cell->firstChild) $cell->removeChild($cell->firstChild);
                            $cell->setAttribute('s', '1');
                            $cell->setAttribute('t', 'inlineStr');
                            $is = $dom3->createElementNS($ns3, 'is');
                            $t = $dom3->createElementNS($ns3, 't');
                            $t->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
                            $t->appendChild($dom3->createTextNode($firstLine));
                            $is->appendChild($t);
                            $cell->appendChild($is);
                        }

                        if ($n >= 2) {
                            $refRow121 = null;
                            foreach ($xpath3->query('//s:sheetData/s:row[@r=121]') as $row) {
                                $refRow121 = $row;
                            }

                            $delta = $n - 1;
                            $rowsToShift = [];
                            foreach ($xpath3->query('//s:sheetData/s:row[@r >= 121]') as $row) {
                                $rowsToShift[] = $row;
                            }
                            foreach ($rowsToShift as $row) {
                                $oldR = (int)$row->getAttribute('r');
                                $newR = $oldR + $delta;
                                $row->setAttribute('r', (string)$newR);
                                foreach ($xpath3->query('s:c', $row) as $cell) {
                                    $ref = $cell->getAttribute('r');
                                    $cell->setAttribute('r', preg_replace('/\d+$/', (string)$newR, $ref));
                                }
                            }

                            $rn = 121;
                            for ($i = 1; $i < $n; $i++) {
                                $line = ($i + 1) . '. ' . $sheet3ZeroItems[$i];
                                $sheetData3->insertBefore($makeRow($rn, $line), $refRow121);
                                $rn++;
                            }
                        }
                    } else {
                        $rowsToRemove = [];
                        foreach ($xpath3->query('//s:sheetData/s:row[@r >= 121 and @r <= 122]') as $row) {
                            $rowsToRemove[] = $row;
                        }
                        foreach ($rowsToRemove as $row) {
                            $sheetData3->removeChild($row);
                        }

                        $rowsToShift = [];
                        foreach ($xpath3->query('//s:sheetData/s:row[@r >= 123]') as $row) {
                            $rowsToShift[] = $row;
                        }
                        foreach ($rowsToShift as $row) {
                            $oldR = (int)$row->getAttribute('r');
                            $newR = $oldR - 2;
                            $row->setAttribute('r', (string)$newR);
                            foreach ($xpath3->query('s:c', $row) as $cell) {
                                $ref = $cell->getAttribute('r');
                                $cell->setAttribute('r', preg_replace('/\d+$/', (string)$newR, $ref));
                            }
                        }
                    }



                    // --- Sheet3: MINOR and MAJOR sections ---
                    $minorRn = 0;
                    $majorRn = 0;
                    foreach ($xpath3->query('//s:sheetData/s:row') as $row) {
                        $r = (int)$row->getAttribute('r');
                        foreach ($xpath3->query('s:c', $row) as $cell) {
                            $ref = $cell->getAttribute('r');
                            if (!str_starts_with($ref, 'B')) continue;
                            $type = $cell->getAttribute('t');
                            $text = '';
                            if ($type === 's') {
                                $v = $cell->getElementsByTagName('v')->item(0);
                                if ($v) {
                                    $idx = (int)$v->textContent;
                                    $text = $ssTextByIndex[$idx] ?? '';
                                }
                            } elseif ($type === 'inlineStr') {
                                $t = $cell->getElementsByTagName('t')->item(0);
                                $text = $t ? $t->textContent : '';
                            }
                            if (str_contains($text, 'Temuan dengan kategori MINOR')) {
                                $minorRn = $r;
                            } elseif (str_contains($text, 'Temuan dengan kategori MAJOR')) {
                                $majorRn = $r;
                            }
                        }
                    }

                    if ($minorRn > 0 && $majorRn > 0 && $majorRn > $minorRn) {
                        $minorPlaceholders = [];
                        $majorPlaceholders = [];
                        $foundMinor = false;
                        $foundMajor = false;
                        foreach ($sheetData3->childNodes as $child) {
                            if ($child->nodeType !== XML_ELEMENT_NODE || $child->localName !== 'row') continue;
                            $r = (int)$child->getAttribute('r');
                            if ($r === $minorRn) { $foundMinor = true; continue; }
                            if ($r === $majorRn) { $foundMajor = true; continue; }
                            if ($foundMajor) {
                                $majorPlaceholders[] = $child;
                            } elseif ($foundMinor) {
                                $minorPlaceholders[] = $child;
                            }
                        }

                        foreach ($minorPlaceholders as $row) $sheetData3->removeChild($row);
                        foreach ($majorPlaceholders as $row) $sheetData3->removeChild($row);

                        $majorRowElement = null;
                        foreach ($sheetData3->childNodes as $child) {
                            if ($child->nodeType !== XML_ELEMENT_NODE || $child->localName !== 'row') continue;
                            if ((int)$child->getAttribute('r') === $majorRn) {
                                $majorRowElement = $child;
                                break;
                            }
                        }

                        if ($majorRowElement) {
                            $makeDataRow = function ($rn, $bText = null) use ($dom3, $ns3) {
                                $row = $dom3->createElementNS($ns3, 'row');
                                $row->setAttribute('r', (string)$rn);
                                $row->setAttribute('spans', '1:15');
                                $cell = $dom3->createElementNS($ns3, 'c');
                                $cell->setAttribute('r', 'B' . $rn);
                                $cell->setAttribute('s', '1');
                                if ($bText !== null) {
                                    $cell->setAttribute('t', 'inlineStr');
                                    $is = $dom3->createElementNS($ns3, 'is');
                                    $t = $dom3->createElementNS($ns3, 't');
                                    $t->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
                                    $t->appendChild($dom3->createTextNode($bText));
                                    $is->appendChild($t);
                                    $cell->appendChild($is);
                                }
                                $row->appendChild($cell);
                                return $row;
                            };

                            $rn = $minorRn + 1;

                            foreach (($findingLines['minor'] ?? []) as $line) {
                                if (trim($line) === '') continue;
                                $sheetData3->insertBefore($makeDataRow($rn++, trim($line)), $majorRowElement);
                            }

                            $sheetData3->insertBefore($makeDataRow($rn++), $majorRowElement);

                            $majorRowElement->setAttribute('r', (string)$rn);
                            foreach ($xpath3->query('s:c', $majorRowElement) as $cell) {
                                $ref = $cell->getAttribute('r');
                                $cell->setAttribute('r', preg_replace('/\d+$/', (string)$rn, $ref));
                            }
                            $rn++;

                            foreach (($findingLines['mayor'] ?? []) as $line) {
                                if (trim($line) === '') continue;
                                $sheetData3->appendChild($makeDataRow($rn++, trim($line)));
                            }
                        }
                    }

                    // --- Paste avg/min dari min max hierarchy (sequential, tanpa match nama) ---
                    $allCats = Category::with('items.criteria')->get()->keyBy('id');

                    $aggregateFn = function(array $catIds) use ($geraiScores, $geraiBobots, $allCats) {
                        $pcts = [];
                        $byGerai = [];
                        foreach ($geraiScores as $itemId => $geraiScoreMap) {
                            foreach ($geraiScoreMap as $kode => $score) {
                                $byGerai[$kode][$itemId] = $score;
                            }
                        }
                        $geraiKodes = array_keys($byGerai);
                        foreach ($geraiKodes as $kode) {
                            $totalScore = 0;
                            $totalBobot = 0;
                            foreach ($catIds as $catId) {
                                $cat = $allCats->get($catId);
                                if (!$cat) continue;
                                foreach ($cat->items as $item) {
                                    $score = $geraiScores[$item->id][$kode] ?? null;
                                    if ($score !== null) {
                                        $totalScore += $score;
                                        $totalBobot += $geraiBobots[$item->id];
                                    }
                                }
                            }
                            if ($totalBobot > 0) {
                                $pcts[] = ($totalScore / $totalBobot) * 100;
                            }
                        }
                        return $pcts;
                    };

                    $itemAggregateFn = function($itemId) use ($geraiScores, $geraiBobots) {
                        $bobot = $geraiBobots[$itemId] ?? 0;
                        if ($bobot <= 0) return [];
                        $pcts = [];
                        foreach (($geraiScores[$itemId] ?? []) as $score) {
                            $pcts[] = ($score / $bobot) * 100;
                        }
                        return $pcts;
                    };

                    $sections = [
                        ['name' => 'Karyawan & Pimpinan Gerai', 'groups' => [
                            ['name' => 'Pelayanan', 'category_ids' => [1, 2, 3, 4]],
                            ['name' => 'Penampilan & Tingkah Laku Karyawan', 'category_ids' => [6, 7, 8]],
                        ], 'category_ids' => [5, 9]],
                        ['name' => 'Tampilan Gerai', 'category_ids' => [10, 11, 12, 13, 14]],
                        ['name' => 'Produk Operasional', 'category_ids' => [15, 16, 17, 19, 18]],
                    ];

                    $hierarchyValues = []; // [['avg'=>..., 'min'=>...], ...]

                    $allCategoryIds = [];
                    foreach ($sections as $section) {
                        $secCatIds = $section['category_ids'] ?? [];
                        foreach ($section['groups'] ?? [] as $group) {
                            $secCatIds = array_merge($secCatIds, $group['category_ids']);
                        }
                        $allCategoryIds = array_merge($allCategoryIds, $secCatIds);

                        // Section aggregate
                        $pcts = $aggregateFn($secCatIds);
                        $hierarchyValues[] = [
                            'avg' => !empty($pcts) ? round(array_sum($pcts) / count($pcts)) : 0,
                            'min' => !empty($pcts) ? round(min($pcts)) : 0,
                        ];

                        foreach ($section['groups'] ?? [] as $group) {
                            // Group aggregate
                            $pcts = $aggregateFn($group['category_ids']);
                            $hierarchyValues[] = [
                                'avg' => !empty($pcts) ? round(array_sum($pcts) / count($pcts)) : 0,
                                'min' => !empty($pcts) ? round(min($pcts)) : 0,
                            ];

                            // Each category and its items
                            foreach ($group['category_ids'] as $catId) {
                                $pcts = $aggregateFn([$catId]);
                                $hierarchyValues[] = [
                                    'avg' => !empty($pcts) ? round(array_sum($pcts) / count($pcts)) : 0,
                                    'min' => !empty($pcts) ? round(min($pcts)) : 0,
                                ];
                                $cat = $allCats->get($catId);
                                if ($cat) {
                                    foreach ($cat->items as $item) {
                                        $ipcts = $itemAggregateFn($item->id);
                                        $hierarchyValues[] = [
                                            'avg' => !empty($ipcts) ? round(array_sum($ipcts) / count($ipcts)) : 0,
                                            'min' => !empty($ipcts) ? round(min($ipcts)) : 0,
                                        ];
                                    }
                                }
                            }
                        }

                        // Ungrouped categories and their items
                        foreach ($section['category_ids'] ?? [] as $catId) {
                            $pcts = $aggregateFn([$catId]);
                            $hierarchyValues[] = [
                                'avg' => !empty($pcts) ? round(array_sum($pcts) / count($pcts)) : 0,
                                'min' => !empty($pcts) ? round(min($pcts)) : 0,
                            ];
                            $cat = $allCats->get($catId);
                            if ($cat) {
                                foreach ($cat->items as $item) {
                                    $ipcts = $itemAggregateFn($item->id);
                                    $hierarchyValues[] = [
                                        'avg' => !empty($ipcts) ? round(array_sum($ipcts) / count($ipcts)) : 0,
                                        'min' => !empty($ipcts) ? round(min($ipcts)) : 0,
                                    ];
                                }
                            }
                        }
                    }

                    // Total
                    $pcts = $aggregateFn($allCategoryIds);
                    $hierarchyValues[] = [
                        'avg' => !empty($pcts) ? round(array_sum($pcts) / count($pcts)) : 0,
                        'min' => !empty($pcts) ? round(min($pcts)) : 0,
                    ];

                    // Paste sequential ke sheet3 L9 dan M9
                    $setPctCell = function($targetDom, $targetRow, $targetCol, $pctValue) use ($ns) {
                        $ref = $targetCol . $targetRow;
                        $xpath = new DOMXPath($targetDom);
                        $xpath->registerNamespace('s', $ns);
                        $cells = $xpath->query("//s:c[@r='$ref']");
                        if ($cells->length > 0) {
                            $cell = $cells->item(0);
                            foreach (['v', 'is', 'f'] as $tag) {
                                $existing = $cell->getElementsByTagNameNS($ns, $tag)->item(0);
                                if ($existing) $cell->removeChild($existing);
                            }
                        } else {
                            $rowEls = $xpath->query("//s:row[@r='$targetRow']");
                            if ($rowEls->length === 0) return;
                            $rowEl = $rowEls->item(0);
                            $cell = $targetDom->createElementNS($ns, 'c');
                            $cell->setAttribute('r', $ref);
                            $rowEl->appendChild($cell);
                        }
                        $cell->removeAttribute('t');
                        $vEl = $targetDom->createElementNS($ns, 'v');
                        $vEl->textContent = (string) $pctValue;
                        $cell->appendChild($vEl);
                    };

                    $pasteRow = 9;
                    foreach ($hierarchyValues as $hv) {
                        if ($pasteRow > 115) break;
                        $setPctCell($dom3, $pasteRow, 'L', $hv['avg']);
                        $setPctCell($dom3, $pasteRow, 'M', $hv['min']);
                        $pasteRow++;
                    }

                    // --- Sheet3: Penjelasan Formulir 3 ---
                    $penjelasanIsi3 = $finding ? ($finding->penjelasan_isi_3 ?? []) : [];
                    $penjelasanIsi3 = array_filter($penjelasanIsi3, fn($v) => trim($v) !== '');

                    if (!empty($penjelasanIsi3)) {
                        $ssContent3b = $zip->getFromName('xl/sharedStrings.xml');
                        $ssTextByIndex3 = [];
                        if ($ssContent3b !== false) {
                            $ssDom3b = new DOMDocument;
                            $ssDom3b->loadXML($ssContent3b);
                            foreach ($ssDom3b->getElementsByTagName('si') as $idx => $si) {
                                $t = $si->getElementsByTagName('t')->item(0);
                                $ssTextByIndex3[$idx] = $t ? $t->textContent : '';
                            }
                        }

                        // Find PENJELASAN row
                        $penjelasanRn3 = 0;
                        $penjelasanRow3 = null;
                        foreach ($xpath3->query('//s:sheetData/s:row') as $row) {
                            $r = (int)$row->getAttribute('r');
                            foreach ($xpath3->query('s:c', $row) as $cell) {
                                $text = '';
                                $type = $cell->getAttribute('t');
                                if ($type === 's') {
                                    $v = $cell->getElementsByTagName('v')->item(0);
                                    if ($v) { $idx = (int)$v->textContent; $text = $ssTextByIndex3[$idx] ?? ''; }
                                } elseif ($type === 'inlineStr') {
                                    $t = $cell->getElementsByTagName('t')->item(0);
                                    $text = $t ? $t->textContent : '';
                                }
                                if (str_contains($text, 'PENJELASAN')) {
                                    $penjelasanRn3 = $r;
                                    $penjelasanRow3 = $row;
                                    break 2;
                                }
                            }
                        }

                        if (!$penjelasanRow3) {
                            // Create PENJELASAN row at end of sheet data
                            $lastRn = 0;
                            foreach ($xpath3->query('//s:sheetData/s:row') as $row) {
                                $rr = (int)$row->getAttribute('r');
                                if ($rr > $lastRn) $lastRn = $rr;
                            }
                            $penjelasanRn3 = $lastRn + 1;
                            $penjelasanRow3 = $dom3->createElementNS($ns3, 'row');
                            $penjelasanRow3->setAttribute('r', (string)$penjelasanRn3);
                            $penjelasanRow3->setAttribute('spans', '1:15');
                            $cell = $dom3->createElementNS($ns3, 'c');
                            $cell->setAttribute('r', 'A' . $penjelasanRn3);
                            $cell->setAttribute('t', 'inlineStr');
                            $cell->setAttribute('s', '12');
                            $is = $dom3->createElementNS($ns3, 'is');
                            $t = $dom3->createElementNS($ns3, 't');
                            $t->appendChild($dom3->createTextNode('PENJELASAN:'));
                            $is->appendChild($t);
                            $cell->appendChild($is);
                            $penjelasanRow3->appendChild($cell);
                            $sheetData3->appendChild($penjelasanRow3);
                        }

                        // Insert Formulir 3 data below PENJELASAN row
                        $rn = $penjelasanRn3 + 1;
                        $newRows = [];
                        $i = 1;
                        foreach ($penjelasanIsi3 as $teks) {
                            $row = $dom3->createElementNS($ns3, 'row');
                            $row->setAttribute('r', (string)$rn);
                            $row->setAttribute('spans', '1:15');
                            $cell = $dom3->createElementNS($ns3, 'c');
                            $cell->setAttribute('r', 'B' . $rn);
                            $cell->setAttribute('t', 'inlineStr');
                            $cell->setAttribute('s', '1');
                            $is = $dom3->createElementNS($ns3, 'is');
                            $t = $dom3->createElementNS($ns3, 't');
                            $t->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
                            $t->appendChild($dom3->createTextNode(($i++) . '. ' . trim($teks)));
                            $is->appendChild($t);
                            $cell->appendChild($is);
                            $row->appendChild($cell);
                            $newRows[] = $row;
                            $rn++;
                        }
                        $ref = $penjelasanRow3->nextSibling;
                        foreach ($newRows as $row) {
                            $sheetData3->insertBefore($row, $ref);
                        }

                        // Renumber rows after section
                        $allRows = [];
                        foreach ($sheetData3->childNodes as $child) {
                            if ($child->nodeType === XML_ELEMENT_NODE && $child->localName === 'row') {
                                $allRows[] = $child;
                            }
                        }
                        $pastSection = false;
                        $inserted = count($newRows);
                        $skipped = 0;
                        foreach ($allRows as $row) {
                            $rAttr = (int)$row->getAttribute('r');
                            if (!$pastSection) {
                                if ($rAttr === $penjelasanRn3) $pastSection = true;
                                continue;
                            }
                            if ($skipped < $inserted) { $skipped++; continue; }
                            $row->setAttribute('r', (string)$rn);
                            foreach ($xpath3->query('s:c', $row) as $cell) {
                                $ref = $cell->getAttribute('r');
                                $cell->setAttribute('r', preg_replace('/\d+$/', (string)$rn, $ref));
                            }
                            $rn++;
                        }
                    }

                    $zip->addFromString('xl/worksheets/sheet3.xml', $dom3->saveXML());
                }
            }
        }

        // --- Fill datachart sheet (sheet4) with period data ---
        $sheet4Content = $zip->getFromName('xl/worksheets/sheet4.xml');
        if ($sheet4Content !== false) {
            $dom4 = new DOMDocument;
            $dom4->loadXML($sheet4Content);
            $xpath4 = new DOMXPath($dom4);
            $ns4 = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
            $xpath4->registerNamespace('s', $ns4);

            // Get periods for this gerai ascending (oldest first)
            $chartPeriodLabels = MonitoringReport::where('gerai_id', $report->gerai_id)
                ->whereNotNull('submit_at')
                ->selectRaw('periode_label, MAX(checkin_at) as last_checkin')
                ->groupBy('periode_label')
                ->orderByRaw('MAX(checkin_at) asc')
                ->pluck('periode_label')
                ->values();

            // Take the most recent 9 periods (reversed to fill B-J left to right)
            $chartPeriodLabels = $chartPeriodLabels->slice(-9, 9)->values();

            // Fetch all reports for those periods (latest checkin per period)
            $chartAllReports = MonitoringReport::where('gerai_id', $report->gerai_id)
                ->whereNotNull('submit_at')
                ->whereIn('periode_label', $chartPeriodLabels)
                ->orderBy('checkin_at')
                ->get()
                ->groupBy('periode_label')
                ->map(fn($group) => $group->last());

            $columns = ['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
            $filledCols = []; // track columns that got data

            foreach ($chartPeriodLabels as $colIdx => $periodLabel) {
                $col = $columns[$colIdx];
                $pReport = $chartAllReports->get($periodLabel);
                if (!$pReport) continue;
                $filledCols[] = $col;

                // Get rounded score
                $periodScore = 0;
                if (is_numeric($pReport->nilai)) {
                    $periodScore = round((float) $pReport->nilai);
                }

                // Date string for row 1 (dd-mmm-yy format)
                $dateStr = $pReport->checkin_at->setTimezone($tz)->format('d-M-y');

                foreach ([1 => $dateStr, 2 => (string) $periodScore] as $rowNum => $cellValue) {
                    $ref = $col . $rowNum;
                    $cells = $xpath4->query("//s:c[@r='$ref']");
                    if ($cells->length === 0) continue;
                    $cell = $cells->item(0);

                    // Remove existing f, v, is elements
                    foreach (['v', 'is', 'f'] as $tag) {
                        $existing = $cell->getElementsByTagNameNS($ns4, $tag)->item(0);
                        if ($existing) $cell->removeChild($existing);
                    }

                    if ($rowNum === 1) {
                        // Date as inline string
                        $cell->setAttribute('t', 'inlineStr');
                        $is = $dom4->createElementNS($ns4, 'is');
                        $t = $dom4->createElementNS($ns4, 't');
                        $t->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
                        $t->textContent = $cellValue;
                        $is->appendChild($t);
                        $cell->appendChild($is);
                    } else {
                        // Score as numeric value
                        $cell->removeAttribute('t');
                        $v = $dom4->createElementNS($ns4, 'v');
                        $v->textContent = $cellValue;
                        $cell->appendChild($v);
                    }
                }
            }

            // Set row 3 (RERATA KINERJA) to 975 for filled columns only
            foreach ($filledCols as $col) {
                $ref = $col . '3';
                $cells = $xpath4->query("//s:c[@r='$ref']");
                if ($cells->length === 0) continue;
                $cell = $cells->item(0);
                foreach (['v', 'is', 'f'] as $tag) {
                    $existing = $cell->getElementsByTagNameNS($ns4, $tag)->item(0);
                    if ($existing) $cell->removeChild($existing);
                }
                $cell->removeAttribute('t');
                $v = $dom4->createElementNS($ns4, 'v');
                $v->textContent = '975';
                $cell->appendChild($v);
            }

            // Delete unused columns (rows 1-3) after last filled column
            $lastFilledIdx = array_search(end($filledCols), $columns, true);
            if ($lastFilledIdx !== false && $lastFilledIdx < 8) {
                $delCols = array_slice($columns, $lastFilledIdx + 1);
                foreach ($delCols as $delCol) {
                    foreach ([1, 2, 3] as $delRow) {
                        $ref = $delCol . $delRow;
                        $cells = $xpath4->query("//s:c[@r='$ref']");
                        for ($i = $cells->length - 1; $i >= 0; $i--) {
                            $cells->item($i)->parentNode->removeChild($cells->item($i));
                        }
                    }
                }

                // Update chart1.xml range references to match
                $chartLastCol = end($filledCols);
                $chartContent = $zip->getFromName('xl/charts/chart1.xml');
                if ($chartContent !== false) {
                    $chartContent = str_replace(
                        ['datachart!$B$1:$J$1', 'datachart!$B$2:$J$2', 'datachart!$B$3:$J$3'],
                        ["datachart!\$B\$1:\${$chartLastCol}\$1", "datachart!\$B\$2:\${$chartLastCol}\$2", "datachart!\$B\$3:\${$chartLastCol}\$3"],
                        $chartContent
                    );
                    $zip->addFromString('xl/charts/chart1.xml', $chartContent);
                }
            }

            $zip->addFromString('xl/worksheets/sheet4.xml', $dom4->saveXML());
        }

        // Remove calcChain to prevent "Removed Records: Formula" repair warning
        if ($zip->locateName('xl/calcChain.xml') !== false) {
            $zip->deleteName('xl/calcChain.xml');
        }

        $zip->close();

        if ($outputDir) {
            return $outPath;
        }
        return response()->download($outPath)->deleteFileAfterSend(true);
    }

    public static function uploadTemplate(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'template' => 'required|file|mimes:xlsx',
            'type' => 'required|in:monitoring,pra-monitoring',
        ]);

        $filename = 'excel-template-' . $request->type . '.xlsx';
        $request->file('template')->storeAs('', $filename);

        $label = $request->type === 'pra-monitoring' ? 'Pra-Monitoring' : 'Monitoring';
        return back()->with('success', "Template Excel {$label} berhasil diupload.");
    }

    public static function deleteTemplate(\Illuminate\Http\Request $request)
    {
        $request->validate(['type' => 'required|in:monitoring,pra-monitoring']);

        $filename = 'excel-template-' . $request->type . '.xlsx';
        Storage::delete($filename);

        $label = $request->type === 'pra-monitoring' ? 'Pra-Monitoring' : 'Monitoring';
        return back()->with('success', "Template Excel {$label} berhasil dihapus.");
    }

    public static function downloadExampleTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan');

        // header
        $sheet->setCellValue('A1', 'LAPORAN MONITORING');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells('A1:E1');

        $sheet->setCellValue('A3', 'Nama Gerai');
        $sheet->setCellValue('B3', '{nama_gerai}');
        $sheet->setCellValue('D3', 'Kode Gerai');
        $sheet->setCellValue('E3', '{kode_gerai}');

        $sheet->setCellValue('A4', 'Tanggal');
        $sheet->setCellValue('B4', '{tanggal}');
        $sheet->setCellValue('D4', 'Tanggal Lengkap');
        $sheet->setCellValue('E4', '{tanggal_lengkap}');

        $sheet->setCellValue('A5', 'Petugas');
        $sheet->setCellValue('B5', '{petugas}');

        $sheet->setCellValue('A7', 'Checkin');
        $sheet->setCellValue('B7', '{checkin}');
        $sheet->setCellValue('D7', 'Submit');
        $sheet->setCellValue('E7', '{submit}');

        $sheet->setCellValue('A8', 'Lokasi');
        $sheet->setCellValue('B8', '{lokasi}');
        $sheet->setCellValue('D8', 'Periode');
        $sheet->setCellValue('E8', '{periode}');

        $sheet->setCellValue('A10', 'Total Nilai');
        $sheet->setCellValue('B10', '{total_score}');

        // finding
        $sheet->setCellValue('A12', 'Minor');
        $sheet->setCellValue('B12', '{minor}');
        $sheet->setCellValue('D12', 'Mayor');
        $sheet->setCellValue('E12', '{mayor}');
        $sheet->setCellValue('A13', 'Peringatan Awal');
        $sheet->setCellValue('B13', '{peringatan_awal}');

        // item table header
        $sheet->setCellValue('A15', 'No');
        $sheet->setCellValue('B15', 'Kategori');
        $sheet->setCellValue('C15', 'Item');
        $sheet->setCellValue('D15', 'Nilai');
        $sheet->setCellValue('E15', 'Bobot');
        $sheet->setCellValue('F15', 'Catatan');
        $sheet->getStyle('A15:F15')->getFont()->setBold(true);

        // item template row
        $sheet->setCellValue('A16', 1);
        $sheet->setCellValue('B16', '{item_category}');
        $sheet->setCellValue('C16', '{item_name}');
        $sheet->setCellValue('D16', '{item_value}');
        $sheet->setCellValue('E16', '{item_score}');
        $sheet->setCellValue('F16', '{item_notes}');

        // additional sheets
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Data Tambahan');
        $sheet2->setCellValue('A1', 'Franchisee');
        $sheet2->setCellValue('B1', '{franchisee}');
        $sheet2->setCellValue('A2', 'Tipe');
        $sheet2->setCellValue('B2', '{type}');

        // sheet 3: contoh per-item & per-sentence
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Contoh Per-Item');
        $sheet3->setCellValue('A1', 'CONTOH FORMAT PER-ITEM & PER-SENTENCE');
        $sheet3->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $sheet3->mergeCells('A1:C1');

        $sheet3->setCellValue('A3', 'Minor:');
        $sheet3->setCellValue('B3', '{minor_1}');
        $sheet3->setCellValue('C3', '(minor baris 1)');
        $sheet3->setCellValue('B4', '{minor_2}');
        $sheet3->setCellValue('C4', '(minor baris 2)');

        $sheet3->setCellValue('A6', 'Mayor:');
        $sheet3->setCellValue('B6', '{mayor_1}');
        $sheet3->setCellValue('C6', '(mayor baris 1)');

        $sheet3->setCellValue('A8', 'Peringatan Awal:');
        $sheet3->setCellValue('B8', '{peringatan_awal_1}');
        $sheet3->setCellValue('C8', '(peringatan awal baris 1)');
        $sheet3->setCellValue('B9', '{peringatan_awal_2}');
        $sheet3->setCellValue('C9', '(peringatan awal baris 2)');

        $sheet3->setCellValue('A11', 'Item Checklist (per-item):');
        $sheet3->getStyle('A11')->getFont()->setBold(true);
        $sheet3->setCellValue('A12', 'Score');
        $sheet3->setCellValue('B12', 'Nilai');
        $sheet3->setCellValue('C12', 'Catatan');
        $sheet3->getStyle('A12:C12')->getFont()->setBold(true);

        $sheet3->setCellValue('A13', '{item_score:Kebersihan Lantai}');
        $sheet3->setCellValue('B13', '{item_value:Kebersihan Lantai}');
        $sheet3->setCellValue('C13', '{item_notes:Kebersihan Lantai}');

        $sheet3->setCellValue('A14', '{item_score:Rapikan Meja}');
        $sheet3->setCellValue('B14', '{item_value:Rapikan Meja}');
        $sheet3->setCellValue('C14', '{item_notes:Rapikan Meja}');

        foreach (range('A', 'C') as $col) {
            $sheet3->getColumnDimension($col)->setAutoSize(true);
        }

        // style
        foreach (range(1, 6) as $col) {
            $sheet->getColumnDimension(chr(64 + $col))->setAutoSize(true);
        }

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        $spreadsheet->disconnectWorksheets();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="contoh-template-excel.xlsx"',
        ]);
    }

    public function destroy(MonitoringReport $report)
    {
        session()->forget('assessment_snapshot_' . $report->id);

        $report->results()->delete();
        $report->delete();

        $redirect = $this->type === 'pra-monitoring' ? '/report/pre-monitoring' : '/report';

        return redirect($redirect)->with('success', 'Laporan berhasil dihapus.');
    }

    protected function pendingReport()
    {
        return MonitoringReport::where('user_id', Auth::id())
            ->where('type', $this->type)
            ->whereNotNull('checkin_at')
            ->whereNull('submit_at')
            ->first();
    }
}
