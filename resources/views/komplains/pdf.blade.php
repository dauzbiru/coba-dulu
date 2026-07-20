<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; margin: 30px; }
        h2 { text-align: center; margin-bottom: 5px; font-size: 16px; }
        .subtitle { text-align: center; color: #888; font-size: 11px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        td { padding: 6px 8px; vertical-align: top; font-size: 11px; }
        .label { width: 140px; color: #888; font-weight: normal; }
        .value { font-weight: 600; }
        .section-title { font-weight: bold; font-size: 12px; margin-top: 10px; margin-bottom: 5px; border-bottom: 1px solid #ddd; padding-bottom: 3px; }
        .uraian { white-space: pre-wrap; }
    </style>
</head>
<body>
    <h2>Laporan Komplain</h2>
    <p class="subtitle">{{ $komplain->periode ?? '-' }}</p>

    <p class="section-title">Informasi Komplain</p>
    <table>
        <tr>
            <td class="label">Kode Gerai</td>
            <td class="value">{{ $komplain->kode_gerai }}</td>
        </tr>
        <tr>
            <td class="label">Nama Gerai</td>
            <td class="value">{{ $komplain->nama_gerai }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Komplain</td>
            <td class="value">{{ $komplain->tanggal_komplain ? $komplain->tanggal_komplain->format('d M Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Media Laporan</td>
            <td class="value">{{ $komplain->media_laporan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Uraian Komplain</td>
            <td class="value uraian">{{ $komplain->uraian ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Prioritas</td>
            <td class="value">{{ $komplain->prioritas ?? 'Normal' }}</td>
        </tr>
        <tr>
            <td class="label">Status</td>
            <td class="value">{{ $komplain->status ?? '-' }}</td>
        </tr>
    </table>

    <p class="section-title">Penanganan</p>
    <table>
        <tr>
            <td class="label">PIC Penanganan</td>
            <td class="value">{{ $komplain->pic_penanganan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tindak Lanjut</td>
            <td class="value uraian">{{ $komplain->tindak_lanjut ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Follow Up</td>
            <td class="value">{{ $komplain->tanggal_follow_up ? $komplain->tanggal_follow_up->format('d M Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Close</td>
            <td class="value">{{ $komplain->tanggal_close ? $komplain->tanggal_close->format('d M Y') : '-' }}</td>
        </tr>
    </table>
</body>
</html>
