@extends('layouts.admin')

@section('title', 'Detail Evaluasi - ' . $report->gerai->nama_gerai)

@section('content')
<div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
    <div>
        <a href="/report/evaluasi" class="text-sm text-blue-600 hover:underline">&larr; Kembali ke Daftar Laporan</a>
        <h2 class="text-lg sm:text-xl font-bold text-gray-800 mt-1">{{ $report->gerai->kode_gerai }} - {{ $report->gerai->nama_gerai }}</h2>
    </div>
</div>

@if ($historyData->isNotEmpty())
@php
    $groups = [];
    $i = 0;
    $n = count($historyData);
    while ($i < $n) {
        $current = $historyData[$i];
        $year = $current['year'];
        $isRemon = $current['type'] === 're-monitoring';
        $colspan = 1;
        $j = $i + 1;
        while ($j < $n) {
            $next = $historyData[$j];
            $nextRemon = $next['type'] === 're-monitoring';
            if ($next['year'] === $year && $nextRemon === $isRemon) {
                $colspan++;
                $j++;
            } else {
                break;
            }
        }
        $groups[] = ['year' => $year, 'colspan' => $colspan, 'isRemon' => $isRemon];
        $i = $j;
    }
@endphp
<div class="bg-white rounded-xl shadow-md overflow-hidden mb-4">
    <div class="px-4 sm:px-6 py-3 border-b border-gray-200 bg-gray-50">
        <h3 class="text-sm font-semibold text-gray-700">Riwayat Nilai</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs sm:text-sm border border-gray-300" style="min-width: {{ count($historyData) * 70 }}px; border-collapse: collapse;">
            <thead>
                <tr class="bg-gray-50">
                    @foreach ($groups as $g)
                    <th class="px-2 py-2 text-center border border-gray-300 whitespace-nowrap" colspan="{{ $g['colspan'] }}">
                        <div class="font-bold text-gray-800">{{ $g['year'] }}</div>
                    </th>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($historyData as $h)
                    <th class="px-2 py-1 text-center border border-gray-300 whitespace-nowrap font-normal text-gray-500 text-[0.65rem]">
                        {{ $h['periode_short'] ?? '-' }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    @foreach ($historyData as $h)
                    <td class="px-2 py-2 text-center border border-gray-300 whitespace-nowrap">
                        <div class="font-bold">975</div>
                    </td>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($historyData as $h)
                    <td class="px-2 py-2 text-center border border-gray-300 whitespace-nowrap">
                        <span class="font-bold text-base {{ $h['nilai'] >= 975 ? 'text-green-600' : 'text-red-600' }}">{{ round($h['nilai']) }}</span>
                    </td>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($historyData as $h)
                    <td class="px-2 py-2 text-center border border-gray-300 whitespace-nowrap">
                        @if ($h['type'] === 're-monitoring')
                            <span class="font-semibold text-purple-600">REMON</span>
                        @elseif ($h['rank'] && $h['total'])
                            <span class="font-semibold">{{ $h['rank'] }}</span><span class="text-gray-400">-{{ $h['total'] }}</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endif

@php $lastFinding = $historyData->isNotEmpty() ? $historyData->last()['finding'] : null; @endphp

@if ($lastFinding)
<div class="bg-white rounded-xl shadow-md overflow-hidden mb-4">
    <div class="px-4 sm:px-6 py-3 border-b border-gray-200 bg-gray-50">
        <h3 class="text-sm font-semibold text-gray-700">Laporan Terakhir</h3>
    </div>
    <div class="p-4 sm:p-5 space-y-3">
        @if ($lastFinding->major)
            <div>
                <p class="text-xs font-medium text-gray-500">Major</p>
                <p class="text-sm text-gray-800 whitespace-pre-wrap" style="overflow-wrap: break-word; word-break: break-word;">{{ $lastFinding->major }}</p>
            </div>
        @endif
        @if ($lastFinding->minor)
            <div>
                <p class="text-xs font-medium text-gray-500">Minor</p>
                <p class="text-sm text-gray-800 whitespace-pre-wrap" style="overflow-wrap: break-word; word-break: break-word;">{{ $lastFinding->minor }}</p>
            </div>
        @endif
        @if ($lastFinding->peringatan_awal)
            <div>
                <p class="text-xs font-medium text-gray-500">Peringatan Awal</p>
                <p class="text-sm text-gray-800 whitespace-pre-wrap" style="overflow-wrap: break-word; word-break: break-word;">{{ $lastFinding->peringatan_awal }}</p>
            </div>
        @endif
        @if ($lastFinding->note)
            <div>
                <p class="text-xs font-medium text-gray-500">Note</p>
                <p class="text-sm text-gray-800 whitespace-pre-wrap" style="overflow-wrap: break-word; word-break: break-word;">{{ $lastFinding->note }}</p>
            </div>
        @endif
        @if ($lastFinding->kondisi_cat || $lastFinding->kondisi_awning || $lastFinding->kondisi_vinyl || $lastFinding->kondisi_stiker_kaca)
            <div>
                <p class="text-xs font-medium text-gray-500">Checklist Kondisi Gerai</p>
                <p class="text-sm text-gray-800">Kondisi cat: {{ $lastFinding->kondisi_cat ?: 'Baik' }}</p>
                <p class="text-sm text-gray-800">Kondisi awning: {{ $lastFinding->kondisi_awning ?: 'Baik' }}</p>
                <p class="text-sm text-gray-800">Kondisi vinyl reklame dinding/jalan: {{ $lastFinding->kondisi_vinyl ?: 'Baik' }}</p>
                <p class="text-sm text-gray-800">Kondisi stiker kaca: {{ $lastFinding->kondisi_stiker_kaca ?: 'Baik' }}</p>
            </div>
        @endif
    </div>
</div>
@endif

@if ($report->catatan || $report->keterangan)
<div class="bg-white rounded-xl shadow-md overflow-hidden mb-4">
    <div class="px-4 sm:px-6 py-3 border-b border-gray-200 bg-gray-50">
        <h3 class="text-sm font-semibold text-gray-700">Evaluasi</h3>
    </div>
    <div class="p-4 sm:p-5 space-y-3">
        @if ($report->catatan)
            <div>
                <p class="text-xs font-medium text-gray-500">Catatan</p>
                <p class="text-sm text-gray-800 whitespace-pre-wrap" style="overflow-wrap: break-word; word-break: break-word;">{{ $report->catatan }}</p>
            </div>
        @endif
        @if ($report->keterangan)
            <div>
                <p class="text-xs font-medium text-gray-500">Keterangan</p>
                <p class="text-sm text-gray-800 whitespace-pre-wrap" style="overflow-wrap: break-word; word-break: break-word;">{{ $report->keterangan }}</p>
            </div>
        @endif
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
window.history.replaceState(null, '', window.location.href);
window.addEventListener('popstate', function() {
    window.location.href = '/evaluasi';
});
</script>
@endpush
