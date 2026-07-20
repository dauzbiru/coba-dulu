@extends('layouts.admin')

@section('title', 'Daftar Nilai Pra-Monitoring - MARS')

@section('content')
    <div class="bg-white rounded-xl shadow-md">
        <div class="sticky top-0 bg-white z-10 px-4 sm:px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-3">
            <h2 class="text-base sm:text-lg font-semibold text-gray-800 truncate">Daftar Nilai Pra-Monitoring</h2>
            <div class="relative flex items-center gap-1 sm:gap-2 shrink-0">
                <input type="text" id="searchPraMonitoring" placeholder="Cari gerai..."
                    class="absolute right-full mr-2 w-0 px-0 py-2 border-0 text-sm focus:outline-none transition-all duration-200 ease-in-out rounded-lg opacity-0 pointer-events-none"
                    autocomplete="off" value="{{ request('search') }}"
                    oninput="filterPraMonitoring(this.value)">
                <button type="button" onclick="toggleSearch('searchPraMonitoring', this)" class="shrink-0 p-2 text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
                <ul id="praMonitorSuggest" class="hidden mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-[9999] max-h-60 overflow-y-auto list-none p-0 w-64"></ul>
            </div>
        </div>

        @if ($reports->isEmpty())
            <div class="p-6 text-center text-sm text-gray-500">Belum ada data pra-monitoring yang selesai.</div>
        @else
            <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky top-0 z-10">
                        <th class="px-3 sm:px-5 py-3 whitespace-nowrap">Gerai</th>
                        <th class="px-3 sm:px-5 py-3 whitespace-nowrap">Kode</th>
                        <th class="px-3 sm:px-5 py-3 whitespace-nowrap">Petugas</th>
                        <th class="px-3 sm:px-5 py-3 whitespace-nowrap hidden sm:table-cell">Tanggal</th>
                        <th class="px-3 sm:px-5 py-3 text-right whitespace-nowrap">Skor</th>
                        <th class="px-3 sm:px-5 py-3 text-right whitespace-nowrap">Grade</th>
                    </tr>
                </thead>
                <tbody id="praMonitoringTableBody" class="divide-y divide-gray-200">
                    @foreach ($reports as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 sm:px-5 py-3 font-medium text-gray-800 whitespace-nowrap">{{ $r['gerai']->nama_gerai }}</td>
                            <td class="px-3 sm:px-5 py-3 text-gray-500 whitespace-nowrap">{{ $r['gerai']->kode_gerai }}</td>
                            <td class="px-3 sm:px-5 py-3 text-gray-700 whitespace-nowrap">{{ $r['petugas'] }}</td>
                            <td class="px-3 sm:px-5 py-3 text-gray-500 whitespace-nowrap hidden sm:table-cell">{{ $r['tanggal']->format('d-m-Y') }}</td>
                            <td class="px-3 sm:px-5 py-3 text-right font-semibold text-blue-600 whitespace-nowrap">{{ $r['skor'] }}</td>
                            @php $grade = \App\Models\MonitoringReport::gradeFromScore((float) $r['skor']); @endphp
                            <td class="px-3 sm:px-5 py-3 text-right font-semibold whitespace-nowrap {{ $grade === 'A' ? 'text-green-600' : ($grade === 'B' ? 'text-blue-600' : ($grade === 'C' ? 'text-yellow-600' : ($grade === 'D' ? 'text-orange-500' : 'text-red-600'))) }}">{{ $grade }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif

        <div class="px-4 sm:px-6 py-3 border-t border-gray-200">
            {{ $reports->appends(request()->query())->links('pagination::tailwind') }}
        </div>
    </div>
<script>
var suggestData = {!! json_encode($gerais->map(fn($g) => ['search' => $g->kode_gerai . ' ' . $g->nama_gerai, 'primary' => $g->kode_gerai, 'secondary' => $g->nama_gerai]), JSON_HEX_TAG) !!};

function filterPraMonitoring(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#praMonitoringTableBody tr').forEach(function(row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
    });
    var list = document.getElementById('praMonitorSuggest');
    list.innerHTML = '';
    if (!q) { list.classList.add('hidden'); return; }
    var matches = suggestData.filter(function(item) {
        return item.search.toLowerCase().includes(q);
    }).slice(0, 8);
    if (matches.length === 0) { list.classList.add('hidden'); return; }
    matches.forEach(function(item) {
        var li = document.createElement('li');
        li.className = 'px-3 py-2 cursor-pointer hover:bg-blue-50 text-sm';
        li.innerHTML = '<span class="font-medium text-gray-800">' + item.primary + '</span>' + (item.secondary ? '<span class="text-gray-500"> - ' + item.secondary + '</span>' : '');
        li.addEventListener('mousedown', function(e) {
            e.preventDefault();
            document.getElementById('searchPraMonitoring').value = item.primary;
            list.classList.add('hidden');
            filterPraMonitoring(item.primary);
        });
        list.appendChild(li);
    });
    var btn = document.getElementById('searchPraMonitoring').parentElement.querySelector('button');
    positionSuggest(btn, 'praMonitorSuggest');
    list.classList.remove('hidden');
}

document.getElementById('searchPraMonitoring').addEventListener('blur', function() {
    setTimeout(function() { document.getElementById('praMonitorSuggest').classList.add('hidden'); }, 200);
});
</script>
@endsection