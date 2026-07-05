@extends('layouts.admin')

@section('title', 'Peringkat Monitoring - Monapps')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Peringkat Monitoring</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $colLabels[0] ?? 'Periode terbaru' }}: 
                <span class="text-green-600 font-semibold">{{ number_format($pctGe975, 2) }}%</span> gerai skor ≥ 975, 
                <span class="text-red-500 font-semibold">{{ number_format($pctLe974, 2) }}%</span> gerai skor ≤ 974
            </p>
            <p class="text-xs text-gray-400 mt-1">
                A: {{ $gradeCounts['A'] ?? 0 }} ({{ number_format($gradePcts['A'] ?? 0, 2) }}%) &middot;
                B: {{ $gradeCounts['B'] ?? 0 }} ({{ number_format($gradePcts['B'] ?? 0, 2) }}%) &middot;
                C: {{ $gradeCounts['C'] ?? 0 }} ({{ number_format($gradePcts['C'] ?? 0, 2) }}%) &middot;
                D: {{ $gradeCounts['D'] ?? 0 }} ({{ number_format($gradePcts['D'] ?? 0, 2) }}%) &middot;
                E: {{ $gradeCounts['E'] ?? 0 }} ({{ number_format($gradePcts['E'] ?? 0, 2) }}%)
            </p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="/ranking/peringkat"
               class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Muat Ulang
            </a>
            <a href="/ranking/peringkat/excel"
               class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download Excel
            </a>
        </div>
    </div>



    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        @if (empty($rows))
            <div class="p-6 text-sm text-gray-400">Belum ada data monitoring.</div>
        @else
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-500">
                        <th class="px-5 py-3 font-medium w-12">No</th>
                        <th class="px-5 py-3 font-medium">Kode Gerai</th>
                        <th class="px-5 py-3 font-medium">Nama Gerai</th>
                        <th class="px-5 py-3 font-medium text-right">{{ $colLabels[2] ?? 'Terlama' }}</th>
                        <th class="px-5 py-3 font-medium text-right">{{ $colLabels[1] ?? 'Sebelumnya' }}</th>
                        <th class="px-5 py-3 font-medium text-right">{{ $colLabels[0] ?? 'Terbaru' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $i => $r)
                        <tr class="border-t">
                            <td class="px-5 py-3 text-gray-400 font-medium">{{ $i + 1 }}</td>
                            <td class="px-5 py-3 font-medium text-gray-800">{{ $r['gerai']->kode_gerai }}</td>
                            <td class="px-5 py-3 text-gray-800">{{ $r['gerai']->nama_gerai }}</td>
                            <td class="px-5 py-3 text-right font-semibold @if ($r['p1'] && $r['p1']['skor']) text-blue-600 @else text-gray-300 @endif">
                                {{ $r['p1']['skor'] ?? '-' }}
                            </td>
                            <td class="px-5 py-3 text-right font-semibold @if ($r['p2'] && $r['p2']['skor']) text-blue-600 @else text-gray-300 @endif">
                                {{ $r['p2']['skor'] ?? '-' }}
                            </td>
                            <td class="px-5 py-3 text-right font-semibold @if ($r['p3'] && $r['p3']['skor']) text-blue-600 @else text-gray-300 @endif">
                                {{ $r['p3']['skor'] ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
@endsection