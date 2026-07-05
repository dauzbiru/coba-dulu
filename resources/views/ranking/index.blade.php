@extends('layouts.admin')

@section('title', 'Daftar Nilai - Monapps')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Daftar Nilai</h1>
    </div>

    {{-- Filter Periode & Search --}}
    <form method="GET" action="/ranking" class="bg-white rounded-xl shadow-sm border p-4 mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 items-end gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Periode Semester</label>
            <select name="periode_label" onchange="this.form.submit()"
                class="border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full">
                <option value="">Semua Periode</option>
                @foreach ($periodeLabels as $label)
                    <option value="{{ $label }}" {{ $periodeLabel == $label ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <a href="/ranking/excel?periode_label={{ $periodeLabel }}"
                class="inline-block px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                Download Excel
            </a>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Cari Gerai</label>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Kode atau nama gerai..."
                onchange="this.form.submit()"
                class="border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full">
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        @if ($reports->isEmpty())
            <div class="p-6 text-sm text-gray-400">Belum ada data monitoring yang selesai.</div>
        @else
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-500">
                        <th class="px-5 py-3 font-medium">Gerai</th>
                        <th class="px-5 py-3 font-medium">Kode</th>
                        <th class="px-5 py-3 font-medium">Franchisee</th>
                        <th class="px-5 py-3 font-medium">Petugas</th>
                        <th class="px-5 py-3 font-medium">Tanggal</th>
                        <th class="px-5 py-3 font-medium">Periode</th>
                        <th class="px-5 py-3 font-medium text-right">Skor</th>
                        <th class="px-5 py-3 font-medium text-right">Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $i => $r)
                        <tr class="border-t">
                            <td class="px-5 py-3 font-medium text-gray-800">{{ $r['gerai']->nama_gerai }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $r['gerai']->kode_gerai }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $r['gerai']->franchisee }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $r['petugas'] }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $r['tanggal']->format('d M Y') }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $r['periode_label'] ?? '-' }}</td>
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
