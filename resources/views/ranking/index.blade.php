@extends('layouts.admin')

@section('title', 'Daftar Nilai - MARS')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-3">
            <h1 class="text-lg font-bold text-gray-800">Daftar Nilai</h1>
            <div class="relative">
                <div class="flex items-center">
                <input type="text" id="searchRanking" placeholder="Cari gerai..."
                    class="w-0 px-0 border-0 py-2 text-sm focus:outline-none transition-all duration-200 ease-in-out"
                    autocomplete="off" value="{{ request('search') }}"
                    onkeydown="if(event.key==='Enter'){event.preventDefault();window.location='/ranking?search='+encodeURIComponent(this.value)}">
                <button type="button" id="searchBtn" onclick="var i=document.getElementById('searchRanking');if(i.classList.contains('w-0')){i.classList.remove('w-0','px-0','border-0');i.classList.add('w-48','sm:w-64','px-3','border','border-gray-300','rounded-lg');i.focus()}else if(i.value){window.location='/ranking?search='+encodeURIComponent(i.value)}else{i.classList.add('w-0','px-0','border-0');i.classList.remove('w-48','sm:w-64','px-3','border','border-gray-300','rounded-lg')}" class="shrink-0 p-2 text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </div>
                <ul id="searchSuggest" class="hidden absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-50 max-h-60 overflow-y-auto list-none p-0"></ul>
            </div>
            </div>
        </div>

        @if ($reports->isEmpty())
            <div class="p-6 text-sm text-gray-400">Belum ada data monitoring yang selesai.</div>
        @else
            <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-500">
                        <th class="px-2 sm:px-5 py-3 font-medium whitespace-nowrap">Gerai</th>
                        <th class="px-2 sm:px-5 py-3 font-medium whitespace-nowrap">Kode</th>
                        <th class="px-2 sm:px-5 py-3 font-medium whitespace-nowrap hidden sm:table-cell">Franchisee</th>
                        <th class="px-2 sm:px-5 py-3 font-medium whitespace-nowrap">Petugas</th>
                        <th class="px-2 sm:px-5 py-3 font-medium whitespace-nowrap hidden sm:table-cell">Tanggal</th>
                        <th class="px-2 sm:px-5 py-3 font-medium whitespace-nowrap hidden md:table-cell">Periode</th>
                        <th class="px-2 sm:px-5 py-3 font-medium whitespace-nowrap text-right">Skor</th>
                        <th class="px-2 sm:px-5 py-3 font-medium whitespace-nowrap text-right">Grade</th>
                        <th class="px-2 sm:px-5 py-3 font-medium whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="rankingTableBody">
                    @foreach ($reports as $i => $r)
                        <tr class="border-t">
                            <td class="px-2 sm:px-5 py-3 font-medium text-gray-800 whitespace-nowrap">{{ $r['gerai']->nama_gerai }}</td>
                            <td class="px-2 sm:px-5 py-3 text-gray-500 whitespace-nowrap">{{ $r['gerai']->kode_gerai }}</td>
                            <td class="px-2 sm:px-5 py-3 text-gray-500 whitespace-nowrap hidden sm:table-cell">{{ $r['gerai']->franchisee }}</td>
                            <td class="px-2 sm:px-5 py-3 text-gray-700 whitespace-nowrap">{{ $r['petugas'] }}</td>
                            <td class="px-2 sm:px-5 py-3 text-gray-500 whitespace-nowrap hidden sm:table-cell">{{ $r['tanggal']->format('d-m-Y') }}</td>
                            <td class="px-2 sm:px-5 py-3 text-gray-500 whitespace-nowrap hidden md:table-cell">{{ $r['periode_label'] ?? '-' }}</td>
                            <td class="px-2 sm:px-5 py-3 text-right font-semibold text-blue-600 whitespace-nowrap">{{ $r['skor'] }}</td>
                            @php $grade = \App\Models\MonitoringReport::gradeFromScore((float) $r['skor']); @endphp
                            <td class="px-2 sm:px-5 py-3 text-right font-semibold whitespace-nowrap {{ $grade === 'A' ? 'text-green-600' : ($grade === 'B' ? 'text-blue-600' : ($grade === 'C' ? 'text-yellow-600' : ($grade === 'D' ? 'text-orange-500' : 'text-red-600'))) }}">{{ $grade }}</td>
                            <td class="px-2 sm:px-5 py-3 text-center whitespace-nowrap">
                                <button onclick="openEditModal('{{ $r['id'] }}', '{{ str_replace("'", "\\'", $r['gerai']->nama_gerai) }}', '{{ $r['skor'] }}', '{{ $r['tanggal']->format('Y-m-d') }}', '{{ str_replace("'", "\\'", $r['petugas']) }}')"
                                    class="text-xs font-medium text-blue-600 hover:text-blue-800 mr-1 sm:mr-2">Edit</button>
                                <form method="POST" action="/ranking/{{ $r['id'] }}" onsubmit="showConfirm('Hapus nilai {{ $r['gerai']->nama_gerai }}?', function(){ this.submit(); }.bind(this)); return false;" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-medium text-red-600 hover:text-red-800">Hapus</button>
                                </form>
                            </td>
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

    {{-- FAB --}}
    <div id="fabMenu" class="fixed bottom-6 right-6 z-40">
        <div id="fabActions" class="flex flex-col items-end gap-3 mb-3 opacity-0 scale-75 transition-all duration-200 origin-bottom-right pointer-events-none">
            <button onclick="openHapusPeriodeModal(); closeFab()" class="flex items-center gap-2 w-12 h-12 bg-red-600 text-white rounded-full shadow-lg hover:bg-red-700 transition-all duration-200 justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span class="absolute right-full mr-3 bg-gray-800 text-white text-xs font-medium px-3 py-1.5 rounded-lg whitespace-nowrap pointer-events-none shadow-lg">Hapus Nilai</span>
            </button>
            <button onclick="openDownloadModal(); closeFab()" class="flex items-center gap-2 w-12 h-12 bg-green-600 text-white rounded-full shadow-lg hover:bg-green-700 transition-all duration-200 justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="absolute right-full mr-3 bg-gray-800 text-white text-xs font-medium px-3 py-1.5 rounded-lg whitespace-nowrap pointer-events-none shadow-lg">Download Excel</span>
            </button>
        </div>
        <button onclick="toggleFab(event)" class="w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 focus:outline-none transition-all duration-200 flex items-center justify-center">
            <svg id="fabIcon" class="w-7 h-7 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
        </button>
    </div>

    <script>
    function toggleFab(e) {
        e.stopPropagation();
        var actions = document.getElementById('fabActions');
        var icon = document.getElementById('fabIcon');
        var isOpen = !actions.classList.contains('opacity-0');
        if (isOpen) {
            actions.classList.add('opacity-0', 'scale-75', 'pointer-events-none');
            icon.style.transform = 'rotate(0deg)';
        } else {
            actions.classList.remove('opacity-0', 'scale-75', 'pointer-events-none');
            icon.style.transform = 'rotate(45deg)';
        }
    }
    function closeFab() {
        var actions = document.getElementById('fabActions');
        var icon = document.getElementById('fabIcon');
        actions.classList.add('opacity-0', 'scale-75', 'pointer-events-none');
        icon.style.transform = 'rotate(0deg)';
    }
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#fabMenu')) closeFab();
    });
    </script>

    {{-- Modal Download --}}
    <div id="downloadModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="fixed inset-0 bg-black/50" onclick="closeDownloadModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-sm w-full mx-4 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Download Excel</h3>
            <form method="GET" action="/ranking/excel">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Periode</label>
                    <select name="periode_label" class="border rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Periode</option>
                        @foreach ($periodeLabels as $label)
                            <option value="{{ $label }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="closeDownloadModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">Download</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Hapus Periode --}}
    <div id="hapusPeriodeModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="fixed inset-0 bg-black/50" onclick="closeHapusPeriodeModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-sm w-full mx-4 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Hapus Nilai per Periode</h3>
            <form method="POST" action="/ranking/hapus-periode" onsubmit="return confirm('Yakin hapus semua data nilai periode ini?')">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Periode</label>
                    <select name="periode_label" class="border rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Pilih Periode</option>
                        @foreach ($periodeLabels as $label)
                            <option value="{{ $label }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="closeHapusPeriodeModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">Hapus</button>
                </div>
            </form>
    </div>
</div>

<script>
function openEditModal(id, nama, nilai, tanggal, petugas) {
    document.getElementById('editForm').action = '/ranking/' + id;
    document.getElementById('editGeraiName').textContent = nama;
    document.getElementById('editNilai').value = nilai;
    document.getElementById('editTanggal').value = tanggal;
    document.getElementById('editPetugas').value = petugas;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
function openDownloadModal() {
    document.getElementById('downloadModal').classList.remove('hidden');
    closeFab();
}
function closeDownloadModal() {
    document.getElementById('downloadModal').classList.add('hidden');
}
function openHapusPeriodeModal() {
    document.getElementById('hapusPeriodeModal').classList.remove('hidden');
    closeFab();
}
function closeHapusPeriodeModal() {
    document.getElementById('hapusPeriodeModal').classList.add('hidden');
}
</script>

{{-- Modal Edit Nilai --}}
<div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeEditModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl max-w-sm w-full mx-4 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Edit Nilai</h3>
        <form method="POST" action="" id="editForm">
            @csrf @method('PUT')
            <p id="editGeraiName" class="text-sm text-gray-600 mb-4"></p>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal</label>
                <input type="date" name="checkin_at" id="editTanggal" required
                    class="border rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-600 mb-1">Petugas</label>
                <input type="text" name="petugas" id="editPetugas" required
                    class="border rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-600 mb-1">Nilai</label>
                <input type="number" name="nilai" id="editNilai" step="0.01" min="0" max="1000" required
                    class="border rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
var searchData = @json($gerais);

function renderSearchSuggest(input, items) {
    var list = document.getElementById('searchSuggest');
    list.innerHTML = '';
    if (items.length === 0) { list.classList.add('hidden'); return; }
    items.forEach(function(item) {
        var li = document.createElement('li');
        li.className = 'px-3 py-2.5 cursor-pointer hover:bg-blue-50 text-sm';
        var span1 = document.createElement('span');
        span1.className = 'font-medium text-gray-800';
        span1.textContent = item.kode_gerai;
        var span2 = document.createElement('span');
        span2.className = 'text-gray-500';
        span2.textContent = '- ' + item.nama_gerai;
        li.appendChild(span1);
        li.appendChild(span2);
        li.addEventListener('mousedown', function(e) {
            e.preventDefault();
            var input = document.getElementById('searchRanking');
            input.value = item.kode_gerai;
            list.classList.add('hidden');
        });
        list.appendChild(li);
    });
    list.classList.remove('hidden');
}

document.getElementById('searchRanking').addEventListener('focus', function() {
    if (this.classList.contains('w-0')) return;
    var val = this.value.toLowerCase();
    renderSearchSuggest(this, searchData.filter(function(item) {
        return item.kode_gerai.toLowerCase().indexOf(val) !== -1 || item.nama_gerai.toLowerCase().indexOf(val) !== -1;
    }));
});

document.getElementById('searchRanking').addEventListener('input', function() {
    var val = this.value.toLowerCase();
    renderSearchSuggest(this, searchData.filter(function(item) {
        return item.kode_gerai.toLowerCase().indexOf(val) !== -1 || item.nama_gerai.toLowerCase().indexOf(val) !== -1;
    }));
});

document.getElementById('searchRanking').addEventListener('blur', function() {
    setTimeout(function() { document.getElementById('searchSuggest').classList.add('hidden'); }, 200);
});
</script>

@endsection
