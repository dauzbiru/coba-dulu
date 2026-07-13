<?php

namespace App\Http\Controllers;

use App\Models\Gerai;
use App\Models\MonitoringReport;
use App\Models\SemesterPeriod;
use App\Models\User;
use Illuminate\Http\Request;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader as XLSXReader;

class RankingController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        if ($search) $search = str_replace(['%', '_'], '', $search);

        $existingLabels = MonitoringReport::whereIn('type', ['monitoring', 'import'])
            ->whereNotNull('submit_at')
            ->whereNotNull('periode_label')
            ->distinct()
            ->pluck('periode_label');

        $periodeLabels = SemesterPeriod::orderByDesc('year')->orderByDesc('start_month')
            ->get()
            ->filter(fn($p) => $existingLabels->contains($p->label))
            ->pluck('label');

        $query = MonitoringReport::with('gerai', 'user')
            ->whereIn('type', ['monitoring', 'import'])
            ->whereNotNull('submit_at');

        if ($search) {
            $query->whereHas('gerai', function ($q) use ($search) {
                $q->where('kode_gerai', 'like', "%{$search}%")
                  ->orWhere('nama_gerai', 'like', "%{$search}%");
            });
        }

        $reports = $query
            ->select('monitoring_reports.*')
            ->join('gerais', 'monitoring_reports.gerai_id', '=', 'gerais.id')
            ->orderBy('gerais.kode_gerai')
            ->orderBy('monitoring_reports.submit_at', 'desc')
            ->paginate(50)
            ->through(function ($report) {
                if ($report->nilai !== null) {
                    $total = (float) $report->nilai;
                } else {
                    $report->load('results.item.criteria');
                    $total = 0;
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
                }
                return [
                    'id' => $report->id,
                    'gerai' => $report->gerai,
                    'petugas' => $report->user?->name ?? '-',
                    'tanggal' => $report->submit_at,
                    'skor' => $total,
                    'periode_label' => $report->periode_label,
                ];
            });

        $gerais = \App\Models\Gerai::orderBy('kode_gerai')->get(['kode_gerai', 'nama_gerai']);

        return view('ranking.index', compact('reports', 'periodeLabels', 'search', 'gerais'));
    }

    public function excel(Request $request)
    {
        $data = $this->loadRanking($request);
        $reports = $data['reports'];

        $writer = new Writer();

        $periodes = $reports->pluck('periode_label')->filter()->unique()->sort()->values();
        if ($periodes->count() > 1) {
            $periodeSuffix = $periodes->first() . ' s/d ' . $periodes->last();
        } elseif ($periodes->count() === 1) {
            $periodeSuffix = $periodes->first();
        } else {
            $periodeSuffix = 'Semua';
        }
        $filename = storage_path('app/peringkat-monitoring-' . $periodeSuffix . '.xlsx');
        $writer->openToFile($filename);

        $writer->addRow(Row::fromValues(['Peringkat', 'Gerai', 'Kode', 'Franchisee', 'Petugas', 'Tanggal', 'Periode', 'Skor']));

        foreach ($reports as $i => $r) {
            $writer->addRow(Row::fromValues([
                $i + 1,
                $r['gerai']->nama_gerai,
                $r['gerai']->kode_gerai,
                $r['gerai']->franchisee,
                $r['petugas'],
                $r['tanggal']->format('d-m-Y'),
                $r['periode_label'] ?? '-',
                $r['skor'],
            ]));
        }

        $writer->close();
        return response()->download($filename)->deleteFileAfterSend(true);
    }

    public function performa(Request $request)
    {
        $gerais = Gerai::active()->orderBy('kode_gerai')->get();
        $geraiId = $request->input('gerai_id');

        $chartLabels = [];
        $chartData = [];
        $reportData = [];
        $geraiNama = '';

        if ($geraiId) {
            $gerai = Gerai::find($geraiId);
            $geraiNama = $gerai ? $gerai->nama_gerai : '';

            $reports = MonitoringReport::with('results.item.criteria')
                ->where('gerai_id', $geraiId)
                ->whereIn('type', ['monitoring', 'import'])
                ->whereNotNull('submit_at')
                ->orderBy('submit_at', 'asc')
                ->take(10)
                ->get();

            foreach ($reports as $report) {
                if ($report->nilai !== null) {
                    $total = (float) $report->nilai;
                } else {
                    $total = 0;
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
                }
                $chartLabels[] = $report->submit_at->format('d-m-Y');
                $chartData[] = round($total, 2);
                $reportData[] = [
                    'tanggal' => $report->submit_at->format('d-m-Y'),
                    'skor' => round($total, 2),
                ];
            }
        }

        return view('ranking.performa', compact('gerais', 'geraiId', 'geraiNama', 'chartLabels', 'chartData', 'reportData'));
    }

    public function praMonitoring(Request $request)
    {
        $search = $request->input('search');
        if ($search) $search = str_replace(['%', '_'], '', $search);

        $query = MonitoringReport::with('gerai', 'user')
            ->where('type', 'pra-monitoring')
            ->whereNotNull('submit_at');

        if ($search) {
            $query->whereHas('gerai', function ($q) use ($search) {
                $q->where('kode_gerai', 'like', "%{$search}%")
                  ->orWhere('nama_gerai', 'like', "%{$search}%");
            });
        }

        $reports = $query
            ->select('monitoring_reports.*')
            ->join('gerais', 'monitoring_reports.gerai_id', '=', 'gerais.id')
            ->orderBy('gerais.kode_gerai')
            ->orderBy('monitoring_reports.submit_at', 'desc')
            ->paginate(50)
            ->through(function ($report) {
                if ($report->nilai !== null) {
                    $total = (float) $report->nilai;
                } else {
                    $report->load('results.item.criteria');
                    $total = 0;
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
                }
                return [
                    'gerai' => $report->gerai,
                    'petugas' => $report->user?->name ?? '-',
                    'tanggal' => $report->submit_at,
                    'skor' => $total,
                ];
            });

        return view('ranking.pra-monitoring', compact('reports', 'search'));
    }

    public function peringkat(Request $request)
    {
        $data = $this->loadPeringkat($request->input('periode'));
        return view('ranking.peringkat', $data);
    }

    public function peringkatExcel(Request $request)
    {
        $selectedPeriode = $request->input('periode');
        $data = $this->loadPeringkat($selectedPeriode);
        $rows = $data['rows'];
        $colLabels = $data['colLabels'];

        $writer = new Writer();
        $periodeSuffix = $selectedPeriode ?? 'Semua';
        $filename = storage_path('app/Peringkat Monitoring Gabungan (' . $periodeSuffix . ').xlsx');
        $writer->openToFile($filename);

        $headers = ['No', 'Kode Gerai', 'Nama Gerai'];
        $headers[] = $colLabels[2] ?? 'Terlama';
        $headers[] = $colLabels[1] ?? 'Sebelumnya';
        $headers[] = $colLabels[0] ?? 'Terbaru';
        $writer->addRow(Row::fromValues($headers));

        foreach ($rows as $i => $r) {
            $writer->addRow(Row::fromValues([
                $i + 1,
                $r['gerai']->kode_gerai,
                $r['gerai']->nama_gerai,
                isset($r['p1']['skor']) ? round($r['p1']['skor']) : '-',
                isset($r['p2']['skor']) ? round($r['p2']['skor']) : '-',
                isset($r['p3']['skor']) ? round($r['p3']['skor']) : '-',
            ]));
        }

        $writer->close();
        return response()->download($filename)->deleteFileAfterSend(true);
    }

    private function loadPeringkat($selectedPeriode = null)
    {
        $existingLabels = MonitoringReport::whereIn('type', ['monitoring', 'import'])
            ->whereNotNull('submit_at')
            ->whereNotNull('periode_label')
            ->distinct()
            ->pluck('periode_label');

        $periodeLabels = SemesterPeriod::orderByDesc('year')->orderByDesc('start_month')
            ->get()
            ->filter(fn($p) => $existingLabels->contains($p->label))
            ->pluck('label')
            ->values();

        if (!$selectedPeriode && $periodeLabels->isNotEmpty()) {
            $selectedPeriode = $periodeLabels->first();
        }

        if ($selectedPeriode && $periodeLabels->contains($selectedPeriode)) {
            $idx = $periodeLabels->search($selectedPeriode);
            $periodKeys = [
                $periodeLabels[$idx] ?? null,
                $periodeLabels[$idx + 1] ?? null,
                $periodeLabels[$idx + 2] ?? null,
            ];
        } else {
            $periodKeys = [null, null, null];
        }

        $periodKeys = array_filter($periodKeys);
        $selectedKey = $periodKeys[0] ?? null;

        $geraiIds = $selectedKey
            ? MonitoringReport::whereIn('type', ['monitoring', 'import'])
                ->whereNotNull('submit_at')
                ->where('periode_label', $selectedKey)
                ->distinct()
                ->pluck('gerai_id')
            : collect();

        $gerais = $geraiIds->isNotEmpty()
            ? Gerai::whereIn('id', $geraiIds)->orderBy('kode_gerai')->get()
            : collect();

        $rows = [];
        foreach ($gerais as $gerai) {
            $reports = MonitoringReport::where('gerai_id', $gerai->id)
                ->whereIn('type', ['monitoring', 'import'])
                ->whereNotNull('submit_at')
                ->whereIn('periode_label', $periodKeys)
                ->get()
                ->keyBy('periode_label');

            $scores = [];
            foreach ($periodKeys as $k) {
                $r = $k && isset($reports[$k]) ? $reports[$k] : null;
                $scores[] = $r ? [
                    'periode' => $k,
                    'skor' => $r->nilai !== null ? round((float) $r->nilai) : 0,
                ] : null;
            }

            $rows[] = [
                'gerai' => $gerai,
                'p3' => $scores[0] ?? null,
                'p2' => $scores[1] ?? null,
                'p1' => $scores[2] ?? null,
            ];
        }

        usort($rows, function ($a, $b) {
            $sa = $a['p3']['skor'] ?? 0;
            $sb = $b['p3']['skor'] ?? 0;
            if ($sb !== $sa) return $sb <=> $sa;
            $sa = $a['p2']['skor'] ?? 0;
            $sb = $b['p2']['skor'] ?? 0;
            if ($sb !== $sa) return $sb <=> $sa;
            $sa = $a['p1']['skor'] ?? 0;
            $sb = $b['p1']['skor'] ?? 0;
            if ($sb !== $sa) return $sb <=> $sa;
            $ta = $a['gerai']->opening_at?->timestamp ?? 0;
            $tb = $b['gerai']->opening_at?->timestamp ?? 0;
            return $ta <=> $tb;
        });

        $colLabels = [
            $periodKeys[0] ?? 'Terbaru',
            $periodKeys[1] ?? 'Sebelumnya',
            $periodKeys[2] ?? 'Terlama',
        ];

        $latestScores = array_filter(array_column(array_column($rows, 'p3'), 'skor'), fn($v) => $v !== null);
        $totalLatest = count($latestScores);
        $countGe975 = count(array_filter($latestScores, fn($s) => round($s) >= 975));
        $countLe974 = $totalLatest - $countGe975;
        $pctGe975 = $totalLatest > 0 ? round($countGe975 / $totalLatest * 100, 2) : 0;
        $pctLe974 = $totalLatest > 0 ? round($countLe974 / $totalLatest * 100, 2) : 0;

        $gradeCounts = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0];
        foreach ($latestScores as $skor) {
            $grade = \App\Models\MonitoringReport::gradeFromScore($skor);
            $gradeCounts[$grade]++;
        }
        $gradePcts = [];
        foreach ($gradeCounts as $grade => $count) {
            $gradePcts[$grade] = $totalLatest > 0 ? round($count / $totalLatest * 100, 2) : 0;
        }

        return compact('rows', 'colLabels', 'pctGe975', 'pctLe974', 'totalLatest', 'gradeCounts', 'gradePcts', 'periodeLabels', 'selectedPeriode');
    }

    public function importForm()
    {
        return view('ranking.import');
    }

    public function template()
    {
        $writer = new Writer();
        $filename = storage_path('app/template-import-nilai.xlsx');
        $writer->openToFile($filename);

        $writer->addRow(Row::fromValues(['Kode Gerai', 'Nama Gerai', 'Tanggal', 'Petugas', 'Skor']));
        $writer->addRow(Row::fromValues(['G001', 'Gerai A', '15-01-2022', 'username', '85.5']));
        $writer->addRow(Row::fromValues(['G002', 'Gerai B', '20-02-2022', 'username', '72']));

        $writer->close();
        return response()->download($filename)->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx']);

        $reader = new XLSXReader();
        $reader->open($request->file('file'));

        $rows = [];
        $errors = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            $isFirst = true;
            foreach ($sheet->getRowIterator() as $rowObj) {
                $cells = $rowObj->cells;
                $values = array_map(fn($c) => trim((string) $c->getValue()), $cells);

                if ($isFirst) {
                    $isFirst = false;
                    continue;
                }

                if (empty($values[0])) continue;

                $rows[] = $values;
            }
        }

        $reader->close();

        // Validate all rows first, reject all if any error
        $validatedRows = [];
        foreach ($rows as $values) {
            $namaGerai = $values[1] ?? '';
            $gerai = null;
            if ($namaGerai) {
                $gerai = Gerai::where('kode_gerai', $values[0])->where('nama_gerai', $namaGerai)->first();
            }
            if (!$gerai) {
                $gerai = Gerai::active()->where('kode_gerai', $values[0])->first()
                    ?? Gerai::where('kode_gerai', $values[0])->latest()->first();
            }
            if (!$gerai) {
                $errors[] = "Kode gerai '{$values[0]}' tidak ditemukan";
                continue;
            }

            $tanggalRaw = $values[2] ?? null;
            if (!$tanggalRaw) {
                $errors[] = "Tanggal tidak valid untuk gerai {$values[0]}";
                continue;
            }
            try {
                $tanggal = \Carbon\Carbon::createFromFormat('d-m-Y', $tanggalRaw);
            } catch (\Exception $e) {
                try {
                    $tanggal = \Carbon\Carbon::parse($tanggalRaw);
                } catch (\Exception $e2) {
                    $errors[] = "Format tanggal salah untuk gerai {$values[0]}: {$tanggalRaw} (harus DD-MM-YYYY)";
                    continue;
                }
            }

            $petugas = User::where('name', $values[3] ?? '')->orWhere('username', $values[3] ?? '')->first();
            if (!$petugas) {
                $errors[] = "Petugas '{$values[3]}' tidak ditemukan (gerai {$values[0]})";
                continue;
            }

            $skor = is_numeric($values[4] ?? '') ? (float) $values[4] : null;

            $matched = SemesterPeriod::where('year', $tanggal->year)
                ->where('start_month', '<=', $tanggal->month)
                ->where('end_month', '>=', $tanggal->month)
                ->first();

            $validatedRows[] = compact('gerai', 'petugas', 'skor', 'tanggal', 'matched');
        }

        if (!empty($errors)) {
            return redirect('/ranking/import')->with('error',
                'Import dibatalkan. ' . count($errors) . ' error ditemukan:<br>' . implode('<br>', $errors));
        }

        // All valid — import
        $unmatchedDates = [];
        foreach ($validatedRows as $v) {
            $periodeLabel = $v['matched'] ? $v['matched']->label : null;

            $existing = $periodeLabel
                ? MonitoringReport::where('gerai_id', $v['gerai']->id)
                    ->where('type', 'import')
                    ->where('periode_label', $periodeLabel)
                    ->whereNotNull('submit_at')
                    ->first()
                : null;

            if ($existing) {
                $existing->update([
                    'user_id' => $v['petugas']->id,
                    'nilai' => $v['skor'],
                    'grade' => $v['skor'] !== null ? \App\Models\MonitoringReport::gradeFromScore($v['skor']) : null,
                    'submit_at' => $v['tanggal'],
                ]);
            } else {
                MonitoringReport::create([
                    'gerai_id' => $v['gerai']->id,
                    'user_id' => $v['petugas']->id,
                    'type' => 'import',
                    'nilai' => $v['skor'],
                    'grade' => $v['skor'] !== null ? \App\Models\MonitoringReport::gradeFromScore($v['skor']) : null,
                    'periode_label' => $periodeLabel,
                    'checkin_at' => $v['tanggal'],
                    'submit_at' => $v['tanggal'],
                ]);
            }

            if (!$v['matched']) {
                $unmatchedDates[] = $v['tanggal']->copy();
            }
        }

        if ($unmatchedDates) {
            $minDate = min($unmatchedDates);
            $maxDate = max($unmatchedDates);

            $period = SemesterPeriod::firstOrCreate([
                'start_month' => $minDate->month,
                'end_month' => $maxDate->month,
                'year' => $minDate->year,
            ]);

            MonitoringReport::where('type', 'import')
                ->whereNull('periode_label')
                ->whereDate('submit_at', '>=', $minDate->startOfMonth())
                ->whereDate('submit_at', '<=', $maxDate->endOfMonth())
                ->update(['periode_label' => $period->label]);
        }

        $total = count($validatedRows);
        return redirect('/ranking/import')->with('success', "Berhasil import {$total} data.");
    }

    private function loadRanking(Request $request)
    {
        $periodeLabel = $request->input('periode_label');
        $search = $request->input('search');
        if ($search) $search = str_replace(['%', '_'], '', $search);

        $existingLabels = MonitoringReport::whereIn('type', ['monitoring', 'import'])
            ->whereNotNull('submit_at')
            ->whereNotNull('periode_label')
            ->distinct()
            ->pluck('periode_label');

        $periodeLabels = SemesterPeriod::orderByDesc('year')->orderByDesc('start_month')
            ->get()
            ->filter(fn($p) => $existingLabels->contains($p->label))
            ->pluck('label');

        $query = MonitoringReport::with('gerai', 'user')
            ->whereIn('type', ['monitoring', 'import'])
            ->whereNotNull('submit_at');

        if ($periodeLabel) {
            $query->where('periode_label', $periodeLabel);
        }

        if ($search) {
            $query->whereHas('gerai', function ($q) use ($search) {
                $q->where('kode_gerai', 'like', "%{$search}%")
                  ->orWhere('nama_gerai', 'like', "%{$search}%");
            });
        }

        $reports = $query->get()->map(function ($report) {
            if ($report->nilai !== null) {
                $total = (float) $report->nilai;
            } else {
                $report->load('results.item.criteria');
                $total = 0;
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
            }
            return [
                'id' => $report->id,
                'gerai' => $report->gerai,
                'petugas' => $report->user?->name ?? '-',
                'tanggal' => $report->submit_at,
                'skor' => $total,
                'periode_label' => $report->periode_label,
            ];
        });

        $reports = $reports->sort(function ($a, $b) {
            $cmp = strcmp($a['gerai']->kode_gerai, $b['gerai']->kode_gerai);
            if ($cmp !== 0) return $cmp;
            return $b['tanggal']->timestamp <=> $a['tanggal']->timestamp;
        })->values();

        return compact('reports', 'periodeLabels', 'periodeLabel', 'search');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nilai' => 'required|numeric|min:0|max:1000',
            'checkin_at' => 'required|date',
            'petugas' => 'required|string|max:255',
        ]);

        $report = MonitoringReport::findOrFail($id);

        $petugas = User::where('name', $request->input('petugas'))->orWhere('username', $request->input('petugas'))->first();
        if ($petugas) {
            $report->user_id = $petugas->id;
        }

        $nilai = (float) $request->input('nilai');
        $report->nilai = $nilai;
        $report->grade = MonitoringReport::gradeFromScore($nilai);
        $report->checkin_at = \Carbon\Carbon::parse($request->input('checkin_at'));
        $report->submit_at = \Carbon\Carbon::parse($request->input('checkin_at'));
        $report->save();

        return redirect('/ranking')->with('success', 'Nilai berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $report = MonitoringReport::findOrFail($id);
        $report->results()->delete();
        $report->finding()?->delete();
        $report->delete();

        return redirect('/ranking')->with('success', 'Nilai berhasil dihapus.');
    }

    public function hapusPeriode(Request $request)
    {
        $periodeLabel = $request->input('periode_label');

        if (!$periodeLabel) {
            return redirect('/ranking')->with('error', 'Pilih periode terlebih dahulu.');
        }

        $reports = MonitoringReport::whereIn('type', ['monitoring', 'import'])
            ->where('periode_label', $periodeLabel)
            ->whereNotNull('submit_at')
            ->get();

        $count = 0;
        foreach ($reports as $report) {
            $report->results()->delete();
            $report->finding()?->delete();
            $report->delete();
            $count++;
        }

        return redirect('/ranking')->with('success', "Berhasil menghapus {$count} data nilai periode {$periodeLabel}.");
    }
}
