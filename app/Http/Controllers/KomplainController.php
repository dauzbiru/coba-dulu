<?php

namespace App\Http\Controllers;

use App\Models\Komplain;
use Illuminate\Http\Request;

use Barryvdh\DomPDF\Facade\Pdf;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class KomplainController extends Controller
{
public function index()
    {
        $komplains = Komplain::orderByRaw("
            CASE 
                WHEN status = 'Open' THEN 1
                WHEN status = 'On Progress' THEN 2
                ELSE 3
            END
        ")
        ->orderByDesc('tanggal_komplain')
        ->orderByDesc('id')
        ->get();
        
        $openCount = $komplains->where('status', 'Open')->count();
        $onProgressCount = $komplains->where('status', 'On Progress')->count();
        $closedCount = $komplains->where('status', 'Closed')->count();
        
        $gerais = \App\Models\Gerai::active()->orderBy('kode_gerai')->get(['kode_gerai', 'nama_gerai']);
        $years = Komplain::whereNotNull('tanggal_komplain')
            ->get()
            ->pluck('tanggal_komplain')
            ->map(fn($d) => $d->format('Y'))
            ->unique()
            ->sortDesc()
            ->values();
        return view('komplains.index', compact('komplains', 'gerais', 'openCount', 'onProgressCount', 'closedCount', 'years'));
    }

    public function show(Komplain $komplain)
    {
        $franchisees = \App\Models\Gerai::active()
            ->where('kode_gerai', '!=', $komplain->kode_gerai)
            ->distinct()->whereNotNull('franchisee')->where('franchisee', '!=', '')
            ->pluck('franchisee')->sort()->values();
        $pgs = \App\Models\Pg::orderBy('nama_pg')->pluck('nama_pg');
        $allContacts = \App\Models\Gerai::active()
            ->whereNotNull('no_telepon')->where('no_telepon', '!=', '')
            ->get(['nama_gerai', 'franchisee', 'no_telepon']);
        $pgContacts = \App\Models\Pg::whereNotNull('no_telepon')->where('no_telepon', '!=', '')
            ->get(['nama_pg', 'no_telepon']);
        return view('komplains.show', compact('komplain', 'franchisees', 'pgs', 'allContacts', 'pgContacts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'periode' => 'nullable|string|max:100',
            'tanggal_komplain' => 'required|date',
            'kode_gerai' => 'required|string|max:50',
            'nama_gerai' => 'required|string|max:255',
            'uraian' => 'required|string',
            'media_laporan' => 'nullable|string|max:255',
            'kategori_laporan' => 'nullable|string|max:255',
            'prioritas' => 'nullable|string|max:50',
            'pic_penanganan' => 'nullable|string|max:255',
            'tindak_lanjut' => 'nullable|string',
            'status' => 'nullable|string|max:50|in:Open,On Progress,Closed',
            'tanggal_follow_up' => 'nullable|date',
            'tanggal_close' => 'nullable|date',
        ]);

        $data['periode'] = \Carbon\Carbon::parse($data['tanggal_komplain'])->locale('id')->isoFormat('MMMM YYYY');

        Komplain::create($data);

        return redirect('/komplain')->with('success', 'Komplain berhasil ditambahkan.');
    }

    public function update(Request $request, Komplain $komplain)
    {
        $data = $request->validate([
            'periode' => 'nullable|string|max:100',
            'tanggal_komplain' => 'required|date',
            'kode_gerai' => 'required|string|max:50',
            'nama_gerai' => 'required|string|max:255',
            'uraian' => 'required|string',
            'media_laporan' => 'nullable|string|max:255',
            'kategori_laporan' => 'nullable|string|max:255',
            'prioritas' => 'nullable|string|max:50',
            'pic_penanganan' => 'nullable|string|max:255',
            'tindak_lanjut' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'tanggal_follow_up' => 'nullable|date',
            'tanggal_close' => 'nullable|date',
        ]);

        $komplain->update($data);

        return redirect()->back()->with('success', 'Komplain berhasil diperbarui.');
    }

    public function destroy(Komplain $komplain)
    {
        $komplain->delete();

        return redirect('/komplain')->with('success', 'Komplain berhasil dihapus.');
    }

    public function pdf(Komplain $komplain)
    {
        $pdf = Pdf::loadView('komplains.pdf', compact('komplain'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Komplain-' . $komplain->kode_gerai . '-' . $komplain->tanggal_komplain->format('d-m-Y') . '.pdf');
    }

    public function pdfAll(Request $request)
    {
        $query = Komplain::orderBy('tanggal_komplain')->orderBy('id');
        if ($request->filled('year')) {
            $query->whereYear('tanggal_komplain', $request->year);
        }
        $komplains = $query->get();
        $yearLabel = $request->filled('year') ? '-' . $request->year : '';
        $pdf = Pdf::loadView('komplains.pdf-all', compact('komplains'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Semua-Komplain' . $yearLabel . '-' . now()->format('d-m-Y') . '.pdf');
    }

    public function excelAll(Request $request)
    {
        $query = Komplain::orderBy('tanggal_komplain')->orderBy('id');
        if ($request->filled('year')) {
            $query->whereYear('tanggal_komplain', $request->year);
        }
        $komplains = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Komplain');

        $headers = [
            'Periode', 'Tanggal Komplain', 'Kode Gerai', 'Nama Gerai', 'Uraian Komplain',
            'Media Laporan', 'PIC Penanganan', 'Tindak Lanjut', 'Tgl Follow Up', 'Tgl Close',
        ];

        $sheet->fromArray([$headers], null, 'A1');
        $headerRange = 'A1:J1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('1E3A5F');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $row = 2;
        foreach ($komplains as $k) {
            $data = [
                $k->tanggal_komplain ? $k->tanggal_komplain->locale('id')->isoFormat('MMMM') : '-',
                $k->tanggal_komplain ? $k->tanggal_komplain->format('d M Y') : '-',
                $k->kode_gerai,
                $k->nama_gerai,
                $k->uraian,
                $k->media_laporan ?? '-',
                $k->pic_penanganan ?? '-',
                $k->tindak_lanjut ?? '-',
                $k->tanggal_follow_up ? $k->tanggal_follow_up->format('d M Y') : '-',
                $k->tanggal_close ? $k->tanggal_close->format('d M Y') : '-',
            ];
            $sheet->fromArray([$data], null, 'A' . $row);
            // wrap text untuk kolom panjang
            $sheet->getStyle('E' . $row)->getAlignment()->setWrapText(true)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('H' . $row)->getAlignment()->setWrapText(true)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            // center untuk kolom lain
            $sheet->getStyle('A' . $row . ':D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('F' . $row . ':G' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('I' . $row . ':J' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $row++;
        }

        $lastRow = $row - 1;
        $allRange = 'A1:J' . $lastRow;
        $sheet->getStyle($allRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        // lebar kolom tetap untuk kolom wrap, autoSize untuk yang lain
        $sheet->getColumnDimension('E')->setWidth(50);
        $sheet->getColumnDimension('H')->setWidth(40);
        foreach (['A','B','C','D','F','G','I','J'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A2');

        $writer = new Xlsx($spreadsheet);
        $yearLabel = $request->filled('year') ? '-' . $request->year : '';
        $filename = 'Semua-Komplain' . $yearLabel . '-' . now()->format('d-m-Y') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function updatePenanganan(Request $request, Komplain $komplain)
    {
        $data = $request->validate([
            'pic_penanganan' => 'nullable|string|max:255',
            'tindak_lanjut' => 'nullable|string',
            'tanggal_follow_up' => 'nullable|date',
            'tanggal_close' => 'nullable|date',
        ]);

        if (!empty($data['tanggal_close'])) {
            $data['status'] = 'Closed';
        } elseif (!empty($data['pic_penanganan'])) {
            $data['status'] = 'On Progress';
        } else {
            $data['status'] = 'Open';
        }

        $komplain->update($data);

        return redirect()->back()->with('success', 'Penanganan berhasil diperbarui.');
    }

    public function saveTemplate(Request $request, Komplain $komplain)
    {
        $request->validate([
            'wa_template' => 'nullable|string',
        ]);

        $komplain->update(['wa_template' => $request->wa_template]);

        return response()->json(['success' => true]);
    }
}
