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
        $data = $this->loadRanking($request);

        return view('ranking.index', $data);
    }

    public function excel(Request $request)
    {
        $data = $this->loadRanking($request);
        $reports = $data['reports'];

        $writer = new Writer();
        $filename = storage_path('app/peringkat-monitoring.xlsx');
        $writer->openToFile($filename);

        $writer->addRow(Row::fromValues(['Peringkat', 'Gerai', 'Kode', 'Franchisee', 'Petugas', 'Tanggal', 'Periode', 'Skor']));

        foreach ($reports as $i => $r) {
            $writer->addRow(Row::fromValues([
                $i + 1,
                $r['gerai']->nama_gerai,
                $r['gerai']->kode_gerai,
                $r['gerai']->franchisee,
                $r['petugas'],
                $r['tanggal']->format('d/m/Y'),
                $r['periode_label'] ?? '-',
                $r['skor'],
            ]));
        }

        $writer->close();
        return response()->download($filename)->deleteFileAfterSend(true);
    }

    public function performa(Request $request)
    {
        $gerais = Gerai::orderBy('kode_gerai')->get();
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
                $chartLabels[] = $report->submit_at->format('d/m/Y');
                $chartData[] = round($total, 2);
                $reportData[] = [
                    'tanggal' => $report->submit_at->format('d/m/Y'),
                    'skor' => round($total, 2),
                ];
            }
        }

        return view('ranking.performa', compact('gerais', 'geraiId', 'geraiNama', 'chartLabels', 'chartData', 'reportData'));
    }

    public function praMonitoring(Request $request)
    {
        $search = $request->input('search');

        $query = MonitoringReport::with('gerai', 'user', 'results.item.criteria')
            ->where('type', 'pra-monitoring')
            ->whereNotNull('submit_at');

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
        })->sort(function ($a, $b) {
            $cmp = strcmp($a['gerai']->kode_gerai, $b['gerai']->kode_gerai);
            if ($cmp !== 0) return $cmp;
            return $b['tanggal']->timestamp <=> $a['tanggal']->timestamp;
        })->values();

        return view('ranking.pra-monitoring', compact('reports', 'search'));
    }

    public function peringkat()
    {
        $data = $this->loadPeringkat();
        return view('ranking.peringkat', $data);
    }

    public function peringkatExcel()
    {
        $data = $this->loadPeringkat();
        $rows = $data['rows'];
        $colLabels = $data['colLabels'];

        $writer = new Writer();
        $filename = storage_path('app/peringkat-monitoring.xlsx');
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

    private function loadPeringkat()
    {
        $gerais = Gerai::orderBy('kode_gerai')->get();
        $rows = [];
        $periodCounts = [0 => [], 1 => [], 2 => []];

        foreach ($gerais as $gerai) {
            $reports = MonitoringReport::where('gerai_id', $gerai->id)
                ->whereIn('type', ['monitoring', 'import'])
                ->whereNotNull('submit_at')
                ->orderBy('submit_at', 'desc')
                ->take(3)
                ->get();

            if ($reports->isEmpty()) continue;

            $scores = [];
            foreach ($reports as $i => $r) {
                $periode = $r->periode_label ?? $r->submit_at->format('M Y');
                $skor = $r->nilai !== null ? (float) $r->nilai : 0;
                $scores[] = [
                    'periode' => $periode,
                    'skor' => $skor,
                ];
                if (isset($periodCounts[$i][$periode])) {
                    $periodCounts[$i][$periode]++;
                } else {
                    $periodCounts[$i][$periode] = 1;
                }
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

        $colLabels = [];
        foreach ($periodCounts as $i => $counts) {
            arsort($counts);
            $colLabels[] = array_key_first($counts) ?? "Periode " . ($i + 1);
        }

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

        return compact('rows', 'colLabels', 'pctGe975', 'pctLe974', 'totalLatest', 'gradeCounts', 'gradePcts');
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
            $gerai = Gerai::where('kode_gerai', $values[0])->first();
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

            MonitoringReport::create([
                'gerai_id' => $v['gerai']->id,
                'user_id' => $v['petugas']->id,
                'type' => 'import',
                'nilai' => $v['skor'],
                'periode_label' => $periodeLabel,
                'checkin_at' => $v['tanggal'],
                'submit_at' => $v['tanggal'],
            ]);

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

        $existingLabels = MonitoringReport::whereIn('type', ['monitoring', 'import'])
            ->whereNotNull('submit_at')
            ->whereNotNull('periode_label')
            ->distinct()
            ->pluck('periode_label');

        $periodeLabels = SemesterPeriod::orderByDesc('year')->orderByDesc('start_month')
            ->get()
            ->filter(fn($p) => $existingLabels->contains($p->label))
            ->pluck('label');

        $query = MonitoringReport::with('gerai', 'user', 'results.item.criteria')
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
}
