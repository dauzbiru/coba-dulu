@extends('layouts.admin')

@section('title', 'Daftar Nilai Pra-Monitoring - Monapps')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Daftar Nilai Pra-Monitoring</h1>
        <form method="GET" action="/ranking/pra-monitoring">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari gerai..."
                onchange="this.form.submit()"
                class="border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-56">
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        @if ($reports->isEmpty())
            <div class="p-6 text-sm text-gray-400">Belum ada data pra-monitoring yang selesai.</div>
        @else
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-500">
                        <th class="px-5 py-3 font-medium">Gerai</th>
                        <th class="px-5 py-3 font-medium">Kode</th>
                        <th class="px-5 py-3 font-medium">Petugas</th>
                        <th class="px-5 py-3 font-medium">Tanggal</th>
                        <th class="px-5 py-3 font-medium text-right">Skor</th>
                        <th class="px-5 py-3 font-medium text-right">Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $r)
                        <tr class="border-t">
                            <td class="px-5 py-3 font-medium text-gray-800">{{ $r['gerai']->nama_gerai }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $r['gerai']->kode_gerai }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $r['petugas'] }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $r['tanggal']->format('d M Y') }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-blue-600">{{ $r['skor'] }}</td>
                            @php $grade = \App\Models\MonitoringReport::gradeFromScore((float) $r['skor']); @endphp
                            <td class="px-5 py-3 text-right font-semibold {{ $grade === 'A' ? 'text-green-600' : ($grade === 'B' ? 'text-blue-600' : ($grade === 'C' ? 'text-yellow-600' : ($grade === 'D' ? 'text-orange-500' : 'text-red-600'))) }}">{{ $grade }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
@endsection