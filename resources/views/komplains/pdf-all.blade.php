<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 9px; color: #333; margin: 15px; }
        h2 { text-align: center; margin-bottom: 2px; font-size: 13px; }
        .subtitle { text-align: center; color: #888; font-size: 9px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th { background: #1e3a5f; color: #fff; font-size: 8px; padding: 4px 5px; text-align: center; border: 1px solid #ccc; }
        td { font-size: 8px; padding: 4px 5px; border: 1px solid #ddd; vertical-align: middle; text-align: left; }
        tr:nth-child(even) td { background: #f9fafb; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

    @php
        $grouped = $komplains->groupBy(function($k) {
            return $k->tanggal_komplain ? $k->tanggal_komplain->locale('id')->isoFormat('MMMM') : 'Tidak Diketahui';
        });
    @endphp

    @foreach($grouped as $periode => $items)
        @if(!$loop->first)
            <div class="page-break"></div>
        @endif

        <table>
            <thead>
                <tr>
                    <th colspan="2">MONITORING</th>
                    <th colspan="3">KEY PERFORMANCE INDICATOR (KPI)</th>
                    <th colspan="4" style="line-height:1.4;">PIC: Elyas<br>Periode: {{ $periode }} {{ $komplains->first()->tanggal_komplain ? $komplains->first()->tanggal_komplain->locale('id')->isoFormat('YYYY') : now()->locale('id')->isoFormat('YYYY') }}</th>
                </tr>
                <tr>
                    <th style="width:65px">Periode</th>
                    <th style="width:55px">Tanggal Komplain</th>
                    <th style="width:50px">Kode Gerai</th>
                    <th style="width:75px">Nama Gerai</th>
                    <th>Uraian Komplain</th>
                    <th style="width:55px">Media Laporan</th>
                    <th>Tindak Lanjut</th>
                    <th style="width:55px">Tgl Follow Up</th>
                    <th style="width:55px">Tgl Close</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $k)
                    <tr>
                        @if($loop->first)
                            <td rowspan="{{ $items->count() }}" style="vertical-align:middle; text-align:center; font-weight:600;">{{ $periode }}</td>
                        @endif
                        <td>{{ $k->tanggal_komplain ? $k->tanggal_komplain->format('d M Y') : '-' }}</td>
                        <td>{{ $k->kode_gerai }}</td>
                        <td>{{ $k->nama_gerai }}</td>
                        <td>{{ $k->uraian }}</td>
                        <td>{{ $k->media_laporan ?? '-' }}</td>
                        <td>{{ $k->tindak_lanjut ?? '-' }}</td>
                        <td>{{ $k->tanggal_follow_up ? $k->tanggal_follow_up->format('d M Y') : '-' }}</td>
                        <td>{{ $k->tanggal_close ? $k->tanggal_close->format('d M Y') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endforeach
</body>
</html>
