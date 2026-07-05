<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Audit</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #999; padding: 6px 8px; text-align: left; }
        th { background: #eee; }
        h1 { font-size: 18px; margin: 0; }
        h2 { font-size: 14px; margin: 4px 0; }
        .cat-header { background: #f5f5f5; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Laporan Audit</h1>
    @if ($user)
        <h2>User: {{ $user->name }} ({{ $user->username }})</h2>
    @else
        <h2>Semua User</h2>
    @endif

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Tugas</th>
                <th>Checklist</th>
                <th>Nilai</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $cat)
                @foreach ($cat->items as $i => $item)
                    @php $r = $results->get($item->id); @endphp
                    <tr>
                        <td>{{ $loop->parent->iteration }}.{{ $i + 1 }}</td>
                        <td>{{ $cat->name }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $r && $r->criterion ? $r->criterion->description : '-' }}</td>
                        <td>{{ $r->notes ?? '-' }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>
