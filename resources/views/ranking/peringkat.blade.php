@extends('layouts.admin')

@section('title', 'Peringkat Monitoring - MARS')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Peringkat Monitoring</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1 break-words">
                <span class="font-semibold">{{ $colLabels[0] ?? 'Periode terbaru' }}</span>:
                <span class="text-green-600 font-semibold">{{ number_format($pctGe975, 2) }}%</span> ≥ 975,
                <span class="text-red-500 font-semibold">{{ number_format($pctLe974, 2) }}%</span> ≤ 974
            </p>
            <p class="text-xs text-gray-400 mt-1">
                A:{{ $gradeCounts['A'] ?? 0 }} B:{{ $gradeCounts['B'] ?? 0 }} C:{{ $gradeCounts['C'] ?? 0 }} D:{{ $gradeCounts['D'] ?? 0 }} E:{{ $gradeCounts['E'] ?? 0 }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <form method="GET" action="/ranking/peringkat" class="flex-1 sm:flex-none">
                <select name="periode" onchange="this.form.submit()"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Pilih Periode</option>
                    @foreach ($periodeLabels as $label)
                        <option value="{{ $label }}" {{ ($selectedPeriode ?? '') === $label ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
            <a href="/ranking/peringkat/excel{{ $selectedPeriode ? '?periode=' . urlencode($selectedPeriode) : '' }}"
               class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Excel
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        @if (empty($rows))
            <div class="p-6 text-sm text-gray-400">Belum ada data monitoring.</div>
        @else
            <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-500">
                        <th class="px-2 sm:px-5 py-3 font-medium w-8 sm:w-12 whitespace-nowrap">No</th>
                        <th class="px-2 sm:px-5 py-3 font-medium whitespace-nowrap">Kode</th>
                        <th class="px-2 sm:px-5 py-3 font-medium whitespace-nowrap">Gerai</th>
                        <th class="px-2 sm:px-5 py-3 font-medium text-right whitespace-nowrap">{{ $colLabels[2] ?? 'Terlama' }}</th>
                        <th class="px-2 sm:px-5 py-3 font-medium text-right whitespace-nowrap">{{ $colLabels[1] ?? 'Sebelumnya' }}</th>
                        <th class="px-2 sm:px-5 py-3 font-medium text-right whitespace-nowrap">{{ $colLabels[0] ?? 'Terbaru' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $i => $r)
                        <tr class="border-t">
                            <td class="px-2 sm:px-5 py-3 text-gray-400 font-medium whitespace-nowrap">{{ $i + 1 }}</td>
                            <td class="px-2 sm:px-5 py-3 font-medium text-gray-800 whitespace-nowrap">{{ $r['gerai']->kode_gerai }}</td>
                            <td class="px-2 sm:px-5 py-3 text-gray-800 whitespace-nowrap truncate max-w-[120px] sm:max-w-none">{{ $r['gerai']->nama_gerai }}</td>
                            <td class="px-2 sm:px-5 py-3 text-right font-semibold whitespace-nowrap @if ($r['p1'] && $r['p1']['skor']) text-blue-600 @else text-gray-300 @endif">
                                {{ $r['p1']['skor'] ?? '-' }}
                            </td>
                            <td class="px-2 sm:px-5 py-3 text-right font-semibold whitespace-nowrap @if ($r['p2'] && $r['p2']['skor']) text-blue-600 @else text-gray-300 @endif">
                                {{ $r['p2']['skor'] ?? '-' }}
                            </td>
                            <td class="px-2 sm:px-5 py-3 text-right font-semibold whitespace-nowrap @if ($r['p3'] && $r['p3']['skor']) text-blue-600 @else text-gray-300 @endif">
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