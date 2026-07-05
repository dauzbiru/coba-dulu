<?php

namespace App\Http\Controllers;

use App\Models\Gerai;
use Illuminate\Http\Request;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Reader\XLSX\Reader as XLSXReader;
use OpenSpout\Common\Entity\Row;

class GeraiController extends Controller
{
    public function index()
    {
        $gerais = Gerai::orderBy('kode_gerai')->get();
        return view('gerais.index', compact('gerais'));
    }

    public function create()
    {
        return view('gerais.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode_gerai' => 'required|string|max:50|unique:gerais,kode_gerai',
            'nama_gerai' => 'required|string|max:255',
            'franchisee' => 'required|string|max:255',
            'opening_at' => 'nullable|date',
        ]);

        Gerai::create($data);

        return redirect('/gerais')->with('success', 'Gerai berhasil ditambahkan.');
    }

    public function show(Gerai $gerai)
    {
        return view('gerais.show', compact('gerai'));
    }

    public function edit(Gerai $gerai)
    {
        return view('gerais.edit', compact('gerai'));
    }

    public function update(Request $request, Gerai $gerai)
    {
        $data = $request->validate([
            'kode_gerai' => 'required|string|max:50|unique:gerais,kode_gerai,' . $gerai->id,
            'nama_gerai' => 'required|string|max:255',
            'franchisee' => 'required|string|max:255',
            'opening_at' => 'nullable|date',
        ]);

        $gerai->update($data);

        return redirect('/gerais')->with('success', 'Gerai berhasil diperbarui.');
    }

    public function destroy(Gerai $gerai)
    {
        $gerai->delete();

        return redirect('/gerais')->with('success', 'Gerai berhasil dihapus.');
    }

    public function importForm()
    {
        return view('gerais.import');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');
        $reader = new XLSXReader();
        $reader->open($file->getPathname());

        $count = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                if ($rowIndex === 1) continue;

                $getCell = function ($cells, $idx) {
                    $val = isset($cells[$idx]) ? $cells[$idx]->getValue() : '';
                    if ($val instanceof \DateTimeInterface) return $val->format('d-m-Y');
                    return is_string($val) ? $val : '';
                };
                $kode = trim($getCell($row->cells, 0));
                $nama = trim($getCell($row->cells, 1));
                $franchisee = trim($getCell($row->cells, 2));
                $openingRaw = trim($getCell($row->cells, 3));

                if (empty($kode) && empty($nama)) continue;
                if (empty($kode)) continue;

                $data = ['nama_gerai' => $nama, 'franchisee' => $franchisee];
                if (!empty($openingRaw)) {
                    try {
                        $data['opening_at'] = \Carbon\Carbon::createFromFormat('d-m-Y', $openingRaw)->format('Y-m-d');
                    } catch (\Exception $e) {
                        try {
                            $data['opening_at'] = \Carbon\Carbon::parse($openingRaw)->format('Y-m-d');
                        } catch (\Exception $e2) {
                            // skip invalid date
                        }
                    }
                }

                Gerai::updateOrCreate(
                    ['kode_gerai' => $kode],
                    $data
                );
                $count++;
            }
        }

        $reader->close();

        return redirect('/gerais')->with('success', "Berhasil import $count data gerai.");
    }

    public function exportExcel()
    {
        $writer = new Writer();
        $filename = storage_path('app/export-gerai.xlsx');

        $writer->openToFile($filename);

        $writer->addRow(Row::fromValues(['Kode Gerai', 'Nama Gerai', 'Franchisee', 'Opening']));

        $gerais = Gerai::orderBy('kode_gerai')->get();
        foreach ($gerais as $g) {
            $writer->addRow(Row::fromValues([$g->kode_gerai, $g->nama_gerai, $g->franchisee, $g->opening_at?->format('d-m-Y')]));
        }

        $writer->close();

        return response()->download($filename)->deleteFileAfterSend(true);
    }

    public function template()
    {
        $writer = new Writer();
        $filename = storage_path('app/template-gerai.xlsx');

        $writer->openToFile($filename);

        $writer->addRow(Row::fromValues(['Kode Gerai', 'Nama Gerai', 'Franchisee', 'Opening']));
        $writer->addRow(Row::fromValues(['GR001', 'Gerai Contoh 1', 'Franchisee A', '15-01-2024']));
        $writer->addRow(Row::fromValues(['GR002', 'Gerai Contoh 2', 'Franchisee B', '01-06-2024']));

        $writer->close();

        return response()->download($filename)->deleteFileAfterSend(true);
    }
}
