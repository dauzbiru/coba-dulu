@extends('layouts.admin')

@section('title', 'Gerai - Monapps')

@section('content')
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="sticky top-0 bg-white z-10 px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="text-base sm:text-lg font-semibold text-gray-800">Data Gerai <span class="text-sm font-normal text-gray-400">({{ $gerais->count() }})</span></h2>
            <div class="flex items-center gap-2">
                <input type="text" id="searchGerai" placeholder="Cari gerai..." class="w-full sm:w-64 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" autocomplete="off" oninput="filterGerai(this.value)">
                <a href="/gerais/import"
                    class="inline-block text-center shrink-0 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Upload Excel</a>
                <a href="/gerais/create"
                    class="inline-block text-center shrink-0 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">+ Gerai</a>
            </div>
        </div>

        <div class="max-h-[calc(100vh-200px)] overflow-auto">
            <table class="w-full min-w-[500px]">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider sticky top-0 z-10">
                        <th class="px-4 sm:px-6 py-3">Kode</th>
                        <th class="px-4 sm:px-6 py-3">Nama Gerai</th>
                        <th class="px-4 sm:px-6 py-3">Franchisee</th>
                        <th class="px-4 sm:px-6 py-3">Opening</th>
                        <th class="px-4 sm:px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody id="geraiTableBody" class="divide-y divide-gray-200">
                    @forelse ($gerais as $g)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-800">{{ $g->kode_gerai }}</td>
                            <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-600">{{ $g->nama_gerai }}</td>
                            <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-600">{{ $g->franchisee }}</td>
                            <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-600">{{ $g->opening_at?->format('d-m-Y') ?? '-' }}</td>
                            <td class="px-4 sm:px-6 py-3 text-right whitespace-nowrap">
                                <a href="/gerais/{{ $g->id }}/edit"
                                    class="inline-block px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100">Edit</a>
                                <form method="POST" action="/gerais/{{ $g->id }}" onsubmit="showConfirm('Hapus gerai ini?', function(){ this.submit(); }.bind(this)); return false;" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="inline-block px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 sm:px-6 py-8 text-center text-sm text-gray-500">Belum ada data gerai.</td>
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
