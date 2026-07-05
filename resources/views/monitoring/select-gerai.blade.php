@extends('layouts.admin')

@section('title', 'Buat Laporan - Pilih Gerai')

@section('content')
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="sticky top-0 bg-white z-10 px-4 sm:px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-3">
        <h2 class="text-base sm:text-lg font-semibold text-gray-800">Buat {{ $prefix === 'pra-monitoring' ? 'Pra-Monitoring' : 'Monitoring' }}</h2>
        <input type="text" id="searchGerai" placeholder="Cari gerai..." class="w-full sm:w-64 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" autocomplete="off" oninput="filterGerai(this.value)">
    </div>

    <div class="max-h-[calc(100vh-200px)] overflow-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider sticky top-0 z-10">
                    <th class="px-4 sm:px-6 py-3">Kode</th>
                    <th class="px-4 sm:px-6 py-3">Nama Gerai</th>
                    <th class="px-4 sm:px-6 py-3"></th>
                </tr>
            </thead>
            <tbody id="geraiTableBody" class="divide-y divide-gray-200">
                @forelse ($gerais as $g)
                    @if (in_array($g->id, $todayReportGeraiIds))
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-400">{{ $g->kode_gerai }}</td>
                            <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-400">{{ $g->nama_gerai }}</td>
                            <td class="px-4 sm:px-6 py-3 text-right"><span class="text-xs text-gray-400">Sudah</span></td>
                        </tr>
                    @else
                        <tr class="hover:bg-blue-50 cursor-pointer" onclick="window.location='/{{ $prefix }}/checkin/{{ $g->id }}'">
                            <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-800">{{ $g->kode_gerai }}</td>
                            <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-600">{{ $g->nama_gerai }}</td>
                            <td class="px-4 sm:px-6 py-3 text-right"><span class="text-blue-600 text-xs font-medium">&rarr;</span></td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="3" class="px-4 sm:px-6 py-8 text-center text-sm text-gray-500">Belum ada data gerai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function filterGerai(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#geraiTableBody tr').forEach(function(row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
    });
}
</script>
@endsection
