@extends('layouts.admin')

@section('title', 'Gerai - MARS')

@section('content')
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="sticky top-0 bg-white z-10 px-4 sm:px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-3">
            <h2 class="text-base sm:text-lg font-semibold text-gray-800 truncate">Data Gerai <span class="text-sm font-normal text-gray-400">({{ $gerais->where('is_active', true)->count() }} aktif, {{ $gerais->where('is_active', false)->count() }} tutup)</span></h2>
            <div class="flex items-center gap-1 sm:gap-2 shrink-0">
                <div class="relative flex items-center">
                    <input type="text" id="searchGerai" placeholder="Cari gerai..."
                        class="w-0 sm:w-0 px-0 py-2 border-0 text-sm focus:outline-none transition-all duration-200 ease-in-out"
                        autocomplete="off" oninput="filterGerai(this.value)">
                    <button type="button" onclick="toggleSearch('searchGerai', this)" class="shrink-0 p-2 text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </div>

            </div>
        </div>

        <div class="max-h-[calc(100vh-200px)] overflow-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider sticky top-0 z-10">
                        <th class="px-3 sm:px-6 py-3 whitespace-nowrap">Kode</th>
                        <th class="px-3 sm:px-6 py-3 whitespace-nowrap">Nama</th>
                        <th class="px-3 sm:px-6 py-3 whitespace-nowrap hidden sm:table-cell">Franchisee</th>
                        <th class="px-3 sm:px-6 py-3 whitespace-nowrap hidden sm:table-cell">Alamat</th>
                        <th class="px-3 sm:px-6 py-3 whitespace-nowrap hidden sm:table-cell">Email</th>
                        <th class="px-3 sm:px-6 py-3 whitespace-nowrap hidden sm:table-cell">No Telepon</th>
                        <th class="px-3 sm:px-6 py-3 whitespace-nowrap hidden sm:table-cell">Opening</th>
                        <th class="px-3 sm:px-6 py-3 whitespace-nowrap">Status</th>
                        <th class="px-3 sm:px-6 py-3 whitespace-nowrap"></th>
                    </tr>
                </thead>
                <tbody id="geraiTableBody" class="divide-y divide-gray-200">
                    @forelse ($gerais as $g)
                        <tr class="hover:bg-gray-50 {{ !$g->is_active ? 'bg-red-50' : '' }}">
                            <td class="px-3 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-800 whitespace-nowrap">{{ $g->kode_gerai }}</td>
                            <td class="px-3 sm:px-6 py-3 text-xs sm:text-sm text-gray-600 truncate max-w-[120px] sm:max-w-none">{{ $g->nama_gerai }}</td>
                            <td class="px-3 sm:px-6 py-3 text-xs sm:text-sm text-gray-600 whitespace-nowrap hidden sm:table-cell">{{ $g->franchisee }}</td>
                            <td class="px-3 sm:px-6 py-3 text-xs sm:text-sm text-gray-600 truncate max-w-[120px] sm:max-w-none hidden sm:table-cell">{{ $g->alamat ?? '-' }}</td>
                            <td class="px-3 sm:px-6 py-3 text-xs sm:text-sm text-gray-600 whitespace-nowrap hidden sm:table-cell">{{ $g->email ?? '-' }}</td>
                            <td class="px-3 sm:px-6 py-3 text-xs sm:text-sm text-gray-600 whitespace-nowrap hidden sm:table-cell">{{ str_starts_with($g->no_telepon, '62') ? '0' . substr($g->no_telepon, 2) : ($g->no_telepon ?? '-') }}</td>
                            <td class="px-3 sm:px-6 py-3 text-xs sm:text-sm text-gray-600 whitespace-nowrap hidden sm:table-cell">{{ $g->opening_at?->format('d-m-Y') ?? '-' }}</td>
                            <td class="px-3 sm:px-6 py-3 text-xs sm:text-sm whitespace-nowrap">
                                @if ($g->is_active)
                                    <span class="text-green-600 font-medium">Aktif</span>
                                @else
                                    <span class="text-red-500 font-medium">Tutup</span>
                                @endif
                            </td>
                            <td class="px-3 sm:px-6 py-3 text-right whitespace-nowrap">
                                <button onclick="openEditModal({{ $g->id }})"
                                    class="inline-block px-2 sm:px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 cursor-pointer">Edit</button>
                                @if ($g->is_active)
                                    <form method="POST" action="/gerais/{{ $g->id }}/tutup" onsubmit="showConfirm('Tutup gerai ini?', function(){ this.submit(); }.bind(this)); return false;" class="inline">
                                        @csrf
                                        <button class="inline-block px-2 sm:px-3 py-1 text-xs font-medium text-orange-600 bg-orange-50 rounded-lg hover:bg-orange-100">Tutup</button>
                                    </form>
                                @else
                                    <form method="POST" action="/gerais/{{ $g->id }}/buka" onsubmit="showConfirm('Buka kembali gerai ini?', function(){ this.submit(); }.bind(this)); return false;" class="inline">
                                        @csrf
                                        <button class="inline-block px-2 sm:px-3 py-1 text-xs font-medium text-green-600 bg-green-50 rounded-lg hover:bg-green-100">Buka</button>
                                    </form>
                                    <form method="POST" action="/gerais/{{ $g->id }}" onsubmit="showConfirm('Hapus gerai ini?', function(){ this.submit(); }.bind(this)); return false;" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="inline-block px-2 sm:px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 sm:px-6 py-8 text-center text-sm text-gray-500">Belum ada data gerai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
<div id="fabMenu" class="fixed bottom-6 right-6 z-40 flex flex-col items-center gap-3">
    <div id="fabActions" class="flex flex-col items-center gap-3 transition-all duration-200 ease-in-out opacity-0 scale-0 pointer-events-none">
        <a href="/gerais/export"
            class="w-12 h-12 bg-purple-600 text-white rounded-full shadow-lg hover:bg-purple-700 flex items-center justify-center text-xs font-medium relative">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span class="absolute right-full mr-3 bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap">Download Excel</span>
        </a>
        <button onclick="openImportModal()"
            class="w-12 h-12 bg-green-600 text-white rounded-full shadow-lg hover:bg-green-700 flex items-center justify-center text-xs font-medium relative cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
            <span class="absolute right-full mr-3 bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap">Upload Excel</span>
        </button>
        <button onclick="openCreateModal()"
            class="w-12 h-12 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 flex items-center justify-center text-xs font-medium relative cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
            <span class="absolute right-full mr-3 bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap">Tambah Gerai</span>
        </button>
    </div>
    <button id="fabToggle"
        class="w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 flex items-center justify-center transition-transform duration-200">
        <svg id="fabIcon" class="w-7 h-7 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
        </svg>
    </button>
</div>

{{-- Modal Create --}}
<div id="createModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeCreateModal()"></div>
    <div class="relative bg-white rounded-xl shadow-lg w-full max-w-lg mx-4 p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Tambah Gerai</h2>
        <form method="POST" action="/gerais">
            @csrf
            <div class="mb-4">
                <label for="create_kode_gerai" class="block text-sm font-medium text-gray-700 mb-1">Kode Gerai</label>
                <input id="create_kode_gerai" type="text" name="kode_gerai" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label for="create_nama_gerai" class="block text-sm font-medium text-gray-700 mb-1">Nama Gerai</label>
                <input id="create_nama_gerai" type="text" name="nama_gerai" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label for="create_franchisee" class="block text-sm font-medium text-gray-700 mb-1">Franchisee</label>
                <input id="create_franchisee" type="text" name="franchisee" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label for="create_alamat" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                <textarea id="create_alamat" name="alamat" rows="2"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="mb-4">
                <label for="create_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input id="create_email" type="email" name="email"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label for="create_no_telepon" class="block text-sm font-medium text-gray-700 mb-1">No Telepon</label>
                <input id="create_no_telepon" type="text" name="no_telepon" placeholder="628xxxxxxxx"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-6">
                <label for="create_opening_at" class="block text-sm font-medium text-gray-700 mb-1">Opening</label>
                <input id="create_opening_at" type="date" name="opening_at"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeCreateModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium cursor-pointer">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium cursor-pointer">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeEditModal()"></div>
    <div class="relative bg-white rounded-xl shadow-lg w-full max-w-lg mx-4 p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Edit Gerai</h2>
        <form id="editForm" method="POST" action="">
            @csrf @method('PUT')
            <div class="mb-4">
                <label for="edit_kode_gerai" class="block text-sm font-medium text-gray-700 mb-1">Kode Gerai</label>
                <input id="edit_kode_gerai" type="text" name="kode_gerai" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label for="edit_nama_gerai" class="block text-sm font-medium text-gray-700 mb-1">Nama Gerai</label>
                <input id="edit_nama_gerai" type="text" name="nama_gerai" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label for="edit_franchisee" class="block text-sm font-medium text-gray-700 mb-1">Franchisee</label>
                <input id="edit_franchisee" type="text" name="franchisee" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label for="edit_alamat" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                <textarea id="edit_alamat" name="alamat" rows="2"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="mb-4">
                <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input id="edit_email" type="email" name="email"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label for="edit_no_telepon" class="block text-sm font-medium text-gray-700 mb-1">No Telepon</label>
                <input id="edit_no_telepon" type="text" name="no_telepon" placeholder="628xxxxxxxx"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-6">
                <label for="edit_opening_at" class="block text-sm font-medium text-gray-700 mb-1">Opening</label>
                <input id="edit_opening_at" type="date" name="opening_at"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeEditModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium cursor-pointer">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium cursor-pointer">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Import --}}
<div id="importModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeImportModal()"></div>
    <div class="relative bg-white rounded-xl shadow-lg w-full max-w-lg mx-4 p-6 sm:p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-2">Import Gerai</h2>
        <p class="text-sm text-gray-500 mb-4">Upload file Excel untuk import data gerai.</p>
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
            <p class="font-medium mb-1">Format file:</p>
            <p>Kolom A: Kode Gerai<br>Kolom B: Nama Gerai<br>Kolom C: Franchisee<br>Kolom D: Alamat<br>Kolom E: Email<br>Kolom F: No Telepon<br>Kolom G: Opening (format dd-mm-yyyy, opsional)</p>
            <a href="/gerais/template" class="mt-2 inline-block text-blue-600 hover:underline font-medium">Download template &rarr;</a>
        </div>
        <form method="POST" action="/gerais/import" enctype="multipart/form-data">
            @csrf
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih file Excel</label>
                <input type="file" name="file" accept=".xlsx,.xls" required
                    class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeImportModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium cursor-pointer">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium cursor-pointer">Import</button>
            </div>
        </form>
    </div>
</div>

<script>
var geraiData = {!! json_encode($gerais->map(fn($g) => [
    'id' => $g->id,
    'kode_gerai' => $g->kode_gerai,
    'nama_gerai' => $g->nama_gerai,
    'franchisee' => $g->franchisee,
    'alamat' => $g->alamat ?? '',
    'email' => $g->email ?? '',
    'no_telepon' => $g->no_telepon ?? '',
    'opening_at' => $g->opening_at?->format('Y-m-d') ?? '',
])) !!};

var fabToggle = document.getElementById('fabToggle');
var fabActions = document.getElementById('fabActions');
var fabIcon = document.getElementById('fabIcon');

function closeFab() {
    fabActions.classList.remove('opacity-100', 'scale-100', 'pointer-events-auto');
    fabActions.classList.add('opacity-0', 'scale-0', 'pointer-events-none');
    fabIcon.classList.remove('rotate-45');
}

function openFab() {
    fabActions.classList.remove('opacity-0', 'scale-0', 'pointer-events-none');
    fabActions.classList.add('opacity-100', 'scale-100', 'pointer-events-auto');
    fabIcon.classList.add('rotate-45');
}

fabToggle.addEventListener('click', function(e) {
    e.stopPropagation();
    var isOpen = fabActions.classList.contains('opacity-100');
    if (isOpen) { closeFab(); } else { openFab(); }
});

document.addEventListener('click', function(e) {
    if (fabActions.classList.contains('opacity-100') && !e.target.closest('#fabMenu')) {
        closeFab();
    }
});

function openCreateModal() {
    closeFab();
    document.getElementById('create_kode_gerai').value = '';
    document.getElementById('create_nama_gerai').value = '';
    document.getElementById('create_franchisee').value = '';
    document.getElementById('create_alamat').value = '';
    document.getElementById('create_email').value = '';
    document.getElementById('create_no_telepon').value = '';
    document.getElementById('create_opening_at').value = '';
    document.getElementById('createModal').classList.remove('hidden');
}
function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
}

function openEditModal(id) {
    closeFab();
    var g = geraiData.find(function(x) { return x.id === id; });
    if (!g) return;
    document.getElementById('editForm').action = '/gerais/' + id;
    document.getElementById('edit_kode_gerai').value = g.kode_gerai;
    document.getElementById('edit_nama_gerai').value = g.nama_gerai;
    document.getElementById('edit_franchisee').value = g.franchisee;
    document.getElementById('edit_alamat').value = g.alamat;
    document.getElementById('edit_email').value = g.email;
    document.getElementById('edit_no_telepon').value = g.no_telepon;
    document.getElementById('edit_opening_at').value = g.opening_at;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function openImportModal() {
    closeFab();
    document.getElementById('importModal').classList.remove('hidden');
}
function closeImportModal() {
    document.getElementById('importModal').classList.add('hidden');
}

function filterGerai(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#geraiTableBody tr').forEach(function(row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
    });
}
</script>
@endsection
