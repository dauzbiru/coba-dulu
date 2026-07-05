@extends('layouts.admin')

@section('title', 'Laporan - Monapps')

@section('content')
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h2 class="text-base sm:text-lg font-semibold text-gray-800">{{ $title }}</h2>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            <button type="button" onclick="document.getElementById('modalAmbilData').classList.remove('hidden')"
                class="text-center px-3 py-2 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 whitespace-nowrap">
                Checkin
            </button>
            @if ($type !== 'pra-monitoring')
            <button type="button" onclick="document.getElementById('modalChecklistTidakSempurna').classList.remove('hidden')"
                class="text-center px-3 py-2 text-xs font-medium text-orange-700 bg-orange-50 border border-orange-200 rounded-lg hover:bg-orange-100 whitespace-nowrap">
                Checklist
            </button>
            @endif
            <button type="button" onclick="document.getElementById('modalExportAllExcel').classList.remove('hidden')"
                class="text-center px-3 py-2 text-xs font-medium text-purple-700 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 whitespace-nowrap">
                Export All Excel
            </button>
            <input type="text" id="searchLaporan" placeholder="Cari gerai atau petugas..." class="w-full sm:w-64 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" autocomplete="off" oninput="filterLaporan(this.value)">
        </div>
    </div>

    @if ($reports->isNotEmpty())
    <div class="overflow-x-auto">
        <table class="w-full min-w-[600px]">
            <thead>
                <tr class="bg-gray-50 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider">
                    <th class="px-4 sm:px-6 py-3">Gerai</th>
                    <th class="px-4 sm:px-6 py-3">Petugas</th>
                    @if ($type !== 'pra-monitoring')
                    <th class="px-4 sm:px-6 py-3">Periode</th>
                    @endif
                    <th class="px-4 sm:px-6 py-3">Checkin</th>
                    <th class="px-4 sm:px-6 py-3">Submit</th>
                    <th class="px-4 sm:px-6 py-3">Total</th>
                    <th class="px-4 sm:px-6 py-3">Grade</th>
                    <th class="px-4 sm:px-6 py-3"></th>
                </tr>
            </thead>
            <tbody id="laporanTableBody" class="divide-y divide-gray-200">
                @foreach ($reports as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-800">
                            <span class="font-medium">{{ $r->gerai->kode_gerai }}</span> - {{ $r->gerai->nama_gerai }}
                        </td>
                        <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-600">{{ $r->user?->name ?? '-' }}</td>
                        @if ($type !== 'pra-monitoring')
                        <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-600">{{ $r->periode_label ?? '-' }}</td>
                        @endif
                        <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-600">{{ $r->checkin_at->format('d-m-Y H:i') }}</td>
                        <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-600">{{ $r->submit_at ? $r->submit_at->format('d-m-Y H:i') : '-' }}</td>
                        <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-blue-600 font-semibold">{{ $r->total_score ?? '-' }}</td>
                        <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm font-semibold {{ $r->grade === 'A' ? 'text-green-600' : ($r->grade === 'B' ? 'text-blue-600' : ($r->grade === 'C' ? 'text-yellow-600' : ($r->grade === 'D' ? 'text-orange-500' : ($r->grade === 'E' ? 'text-red-600' : '')))) }}">{{ $r->grade ?? '-' }}</td>
                        <td class="px-4 sm:px-6 py-3 text-right whitespace-nowrap">
                            <a href="/{{ $r->type }}/{{ $r->id }}"
                                class="inline-block px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100">Detail</a>
                            <form method="POST" action="/{{ $r->type }}/{{ $r->id }}" onsubmit="showConfirm('Hapus laporan ini?', function(){ this.submit(); }.bind(this)); return false;" class="inline">
                                @csrf @method('DELETE')
                                <button class="inline-block px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="p-8 text-center text-sm text-gray-500">Belum ada {{ $title }}.</div>
    @endif
</div>

<script>
function filterLaporan(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#laporanTableBody tr').forEach(function(row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
    });
}
</script>

<div id="modalAmbilData" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-sm mx-4">
        <h3 class="text-base font-semibold text-gray-800 mb-4">Data Checkin & Submit</h3>
        <form method="GET" action="/report/ambil-data">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Periode</label>
                <select name="periode_label" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Periode --</option>
                    @foreach ($periods as $p)
                        <option value="{{ $p->label }}">{{ $p->label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="document.getElementById('modalAmbilData').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</button>
                <button type="submit" onclick="this.closest('[id^=modal]').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Ambil</button>
            </div>
        </form>
    </div>
</div>


<div id="modalChecklistTidakSempurna" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-sm mx-4">
        <h3 class="text-base font-semibold text-gray-800 mb-4">Checklist Tidak Sempurna</h3>
        <p class="text-xs text-gray-500 mb-4">Pilih periode untuk mengekspor data checklist yang nilainya tidak sempurna (tidak mencapai kriteria terbaik).</p>
        <form method="GET" action="/report/checklist-tidak-sempurna">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Periode Semester</label>
                <select name="periode_label" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="">-- Pilih Periode --</option>
                    @foreach ($periods as $p)
                        <option value="{{ $p->label }}">{{ $p->label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="document.getElementById('modalChecklistTidakSempurna').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</button>
                <button type="submit" onclick="this.closest('[id^=modal]').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700">Export Excel</button>
            </div>
        </form>
    </div>
</div>

<div id="modalExportAllExcel" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-sm mx-4">
        <h3 class="text-base font-semibold text-gray-800 mb-4">Export Semua Laporan Excel</h3>
        <p class="text-xs text-gray-500 mb-4">Pilih {{ $type === 'pra-monitoring' ? 'bulan' : 'periode' }} untuk mendownload semua laporan Excel dalam satu file ZIP.</p>
        <form method="GET" action="/report/export-all-excel">
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="mb-4">
                @if ($type === 'pra-monitoring')
                <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                <input type="month" name="month" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                @else
                <label class="block text-sm font-medium text-gray-700 mb-1">Periode Semester</label>
                <select name="periode_label" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">-- Pilih Periode --</option>
                    @foreach ($periods as $p)
                        <option value="{{ $p->label }}">{{ $p->label }}</option>
                    @endforeach
                </select>
                @endif
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="document.getElementById('modalExportAllExcel').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</button>
                <button type="submit" onclick="this.closest('[id^=modal]').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">Download ZIP</button>
            </div>
        </form>
    </div>
</div>
@endsection
