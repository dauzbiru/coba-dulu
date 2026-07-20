@extends('layouts.admin')

@section('title', 'Data Komplain - MARS')

@section('content')
    <div class="bg-white rounded-xl shadow-md">
        <div class="sticky top-0 bg-white z-10 px-4 sm:px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-3">
            <div class="flex items-center gap-4">
                <h2 class="text-base sm:text-lg font-semibold text-gray-800 truncate">Data Komplain</h2>
                <div id="filterButtons" class="flex items-center gap-2 text-xs font-medium overflow-x-auto">
                    <button onclick="filterByStatus('')" id="filterAll" style="background:#374151;color:#FFFFFF" class="px-2 py-0.5 rounded-full font-medium cursor-pointer transition-colors">All: {{ $komplains->count() }}</button>
                    <button onclick="filterByStatus('Open')" id="filterOpen" style="background:#DBEAFE;color:#1D4ED8" class="px-2 py-0.5 rounded-full font-medium cursor-pointer hover:opacity-80 transition-colors">Open: {{ $openCount }}</button>
                    <button onclick="filterByStatus('On Progress')" id="filterProses" style="background:#FEF3C7;color:#D97706" class="px-2 py-0.5 rounded-full font-medium cursor-pointer hover:opacity-80 transition-colors">On Progress: {{ $onProgressCount }}</button>
                    <button onclick="filterByStatus('Closed')" id="filterClosed" style="background:#DCFCE7;color:#16A34A" class="px-2 py-0.5 rounded-full font-medium cursor-pointer hover:opacity-80 transition-colors">Closed: {{ $closedCount }}</button>
                </div>
            </div>
            <div class="relative flex items-center gap-1 sm:gap-2 shrink-0">
                <input type="text" id="searchKomplain" placeholder="Cari komplain..."
                    class="absolute right-full mr-2 w-0 px-0 py-2 border-0 text-sm focus:outline-none transition-all duration-200 ease-in-out rounded-lg opacity-0 pointer-events-none"
                    autocomplete="off" oninput="filterKomplain(this.value)">
                <button type="button" onclick="toggleSearch('searchKomplain', this); toggleFilterOnMobile()" class="shrink-0 p-2 text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
                <ul id="komplainSuggest" class="hidden mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-[9999] max-h-60 overflow-y-auto list-none p-0 w-64"></ul>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full table-fixed">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky top-0 z-10">
                        <th class="px-3 py-3 w-[90px]">Tanggal<br>Komplain</th>
                        <th class="px-3 py-3 w-[90px]">Kode Gerai</th>
                        <th class="px-3 py-3 w-[90px]">Nama Gerai</th>
                        <th class="px-3 py-3 w-[230px]">Uraian</th>
                        <th class="px-3 py-3 w-[90px]">Media</th>
                        <th class="px-3 py-3 w-[90px]">Kategori</th>
                        <th class="px-3 py-3 w-[90px]">Prioritas</th>
                        <th class="px-3 py-3 w-[90px]">Status</th>
                        <th class="px-3 py-3 w-[120px]"></th>
                    </tr>
                </thead>
                <tbody id="komplainTableBody" class="divide-y divide-gray-200">
                    @forelse ($komplains as $k)
                        <tr class="hover:bg-gray-50 status-row" data-status="{{ $k->status }}">
                            <td class="px-3 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $k->tanggal_komplain?->format('d-m-Y') ?? '-' }}</td>
                            <td class="px-3 py-3 text-xs text-gray-600 break-words">{{ $k->kode_gerai }}</td>
                            <td class="px-3 py-3 text-xs text-gray-600 break-words">{{ $k->nama_gerai }}</td>
                            <td class="px-3 py-3 text-xs text-gray-600 w-[230px]">
                                <div class="uraian-truncate max-h-[20px] overflow-hidden leading-5 whitespace-pre-wrap break-words">{{ $k->uraian }}</div>
                                <button type="button" onclick="var el=this.previousElementSibling;el.classList.toggle('max-h-[20px]');el.classList.toggle('max-h-[100px]');var sib=this.nextElementSibling;sib.classList.toggle('hidden');this.classList.toggle('hidden')"
                                    class="text-blue-500 hover:text-blue-700 text-[10px] mt-0.5 hidden uraian-btn">See more</button>
                                <button type="button" onclick="var el=this.parentElement.querySelector('.uraian-truncate');el.classList.toggle('max-h-[20px]');el.classList.toggle('max-h-[100px]');this.classList.toggle('hidden');this.previousElementSibling.classList.toggle('hidden')"
                                    class="text-blue-500 hover:text-blue-700 text-[10px] mt-0.5 block hidden uraian-btn">See less</button>
                            </td>
                            <td class="px-3 py-3 text-xs text-gray-600 break-words">{{ $k->media_laporan ?? '-' }}</td>
                            <td class="px-3 py-3 text-xs text-gray-600 break-words">{{ $k->kategori_laporan ?? '-' }}</td>
                            <td class="px-3 py-3 text-xs whitespace-nowrap">
                                @if ($k->prioritas === 'Mendesak')
                                    <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-medium">Mendesak</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 font-medium">{{ $k->prioritas ?? 'Normal' }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-xs whitespace-nowrap">
                                @if ($k->status === 'Open')
                                    <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-medium">Open</span>
                                @elseif ($k->status === 'On Progress')
                                    <span class="px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 font-medium">On Progress</span>
                                @elseif ($k->status === 'Closed')
                                    <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-medium">Closed</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 font-medium">{{ $k->status ?? '-' }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-right whitespace-nowrap">
                                <a href="/komplain/{{ $k->id }}"
                                    class="inline-block px-2 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Detail</a>
                                <button onclick="openEditModal({{ $k->id }})"
                                    class="inline-block px-2 py-1 text-xs font-medium rounded-lg hover:opacity-80 cursor-pointer" style="background:#FEF3C7;color:#D97706">Edit</button>
                                <form method="POST" action="/komplain/{{ $k->id }}" onsubmit="showConfirm('Hapus komplain ini?', function(){ this.submit(); }.bind(this)); return false;" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="inline-block px-2 py-1 text-xs font-medium rounded-lg hover:opacity-80" style="background:#FEE2E2;color:#DC2626">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-3 py-8 text-center text-sm text-gray-500">Belum ada data komplain.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- FAB --}}
    <div id="fabMenu" class="fixed bottom-6 right-6 z-40 flex flex-col items-center gap-3">
        <div id="fabActions" class="flex flex-col items-center gap-3 transition-all duration-200 ease-in-out opacity-0 scale-0 pointer-events-none">
            <a href="/komplain/excel/all" id="btnDownloadExcel" target="_blank" onclick="return openYearModal('excel')"
                class="w-12 h-12 rounded-full shadow-lg hover:opacity-80 flex items-center justify-center text-xs font-medium relative"
                style="background:#ECFDF5;color:#059669">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="absolute right-full mr-3 bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap">Download Excel</span>
            </a>
            <a href="/komplain/pdf/all" id="btnDownloadPdf" target="_blank" onclick="return openYearModal('pdf')"
                class="w-12 h-12 bg-red-500 text-white rounded-full shadow-lg hover:bg-red-600 flex items-center justify-center text-xs font-medium relative">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="absolute right-full mr-3 bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap">Download PDF</span>
            </a>
            <button onclick="openCreateModal()"
                class="w-12 h-12 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 flex items-center justify-center text-xs font-medium relative cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
                <span class="absolute right-full mr-3 bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap">Tambah Komplain</span>
            </button>
        </div>
        <button id="fabToggle"
            style="background:#3B82F6;color:#FFFFFF"
            class="w-14 h-14 rounded-full shadow-lg hover:opacity-80 flex items-center justify-center transition-transform duration-200">
            <svg id="fabIcon" class="w-7 h-7 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
            </svg>
        </button>
    </div>

    {{-- Modal Create --}}
    <div id="createModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-black/50" onclick="closeCreateModal()"></div>
        <div class="relative bg-white rounded-xl shadow-lg w-full max-w-lg mx-4 p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Tambah Komplain</h2>
            <form method="POST" action="/komplain">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Komplain *</label>
                        <input type="text" name="tanggal_komplain" id="create_tanggal_komplain" required placeholder="Pilih tanggal"
                            class="fp-date w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Gerai *</label>
                        <div class="relative">
                            <input type="text" name="kode_gerai" id="create_kode_gerai" required autocomplete="off" placeholder="Ketik kode atau nama gerai..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <ul id="createGeraiSuggest" class="absolute z-20 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-48 overflow-y-auto hidden"></ul>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Gerai *</label>
                        <input type="text" name="nama_gerai" id="create_nama_gerai" required readonly placeholder="Auto-fill dari kode gerai"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Media Laporan</label>
                        <input type="text" name="media_laporan" placeholder="Email, Telepon, dll"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Laporan</label>
                        <select name="kategori_laporan"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Kategori</option>
                            <option value="Kualitas Air Minum">Kualitas Air Minum</option>
                            <option value="Kelayakan Uji Lab">Kelayakan Uji Lab</option>
                            <option value="Standar Pelayanan kpd Pelanggan">Standar Pelayanan kpd Pelanggan</option>
                            <option value="Standar Operasional dari Karyawan">Standar Operasional dari Karyawan</option>
                            <option value="Pelanggaran PSSO">Pelanggaran PSSO</option>
                            <option value="Antrian dan Titipan Galon Pelanggan">Antrian dan Titipan Galon Pelanggan</option>
                            <option value="Jumlah Karyawan">Jumlah Karyawan</option>
                            <option value="Jam Operasional Gerai (tutup lebih awal dll)">Jam Operasional Gerai (tutup lebih awal dll)</option>
                            <option value="Transaksi/Metode Pembayaran">Transaksi/Metode Pembayaran</option>
                            <option value="Standar Uang Kembalian">Standar Uang Kembalian</option>
                            <option value="Kondisi Halaman Parkir di Gerai">Kondisi Halaman Parkir di Gerai</option>
                            <option value="Kondisi Akses Jalan ke Gerai">Kondisi Akses Jalan ke Gerai</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prioritas</label>
                        <select name="prioritas"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Normal">Normal</option>
                            <option value="Mendesak">Mendesak</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Uraian Komplain *</label>
                        <textarea name="uraian" rows="1" required oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px'"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none overflow-hidden"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeCreateModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium cursor-pointer">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-lg hover:opacity-80 text-sm font-medium cursor-pointer" style="background:#DCFCE7;color:#16A34A">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-black/50" onclick="closeEditModal()"></div>
        <div class="relative bg-white rounded-xl shadow-lg w-full max-w-lg mx-4 p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Edit Komplain</h2>
            <form id="editForm" method="POST" action="">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Komplain *</label>
                        <input type="text" name="tanggal_komplain" id="edit_tanggal_komplain" required placeholder="Pilih tanggal"
                            class="fp-date w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Gerai *</label>
                        <div class="relative">
                            <input type="text" name="kode_gerai" id="edit_kode_gerai" required autocomplete="off" placeholder="Ketik kode atau nama gerai..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <ul id="editGeraiSuggest" class="absolute z-20 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-48 overflow-y-auto hidden"></ul>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Gerai *</label>
                        <input type="text" name="nama_gerai" id="edit_nama_gerai" required readonly placeholder="Auto-fill dari kode gerai"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Media Laporan</label>
                        <input type="text" name="media_laporan" id="edit_media_laporan"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Laporan</label>
                        <select name="kategori_laporan" id="edit_kategori_laporan"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Kategori</option>
                            <option value="Kualitas Air Minum">Kualitas Air Minum</option>
                            <option value="Kelayakan Uji Lab">Kelayakan Uji Lab</option>
                            <option value="Standar Pelayanan kpd Pelanggan">Standar Pelayanan kpd Pelanggan</option>
                            <option value="Standar Operasional dari Karyawan">Standar Operasional dari Karyawan</option>
                            <option value="Pelanggaran PSSO">Pelanggaran PSSO</option>
                            <option value="Antrian dan Titipan Galon Pelanggan">Antrian dan Titipan Galon Pelanggan</option>
                            <option value="Jumlah Karyawan">Jumlah Karyawan</option>
                            <option value="Jam Operasional Gerai (tutup lebih awal dll)">Jam Operasional Gerai (tutup lebih awal dll)</option>
                            <option value="Transaksi/Metode Pembayaran">Transaksi/Metode Pembayaran</option>
                            <option value="Standar Uang Kembalian">Standar Uang Kembalian</option>
                            <option value="Kondisi Halaman Parkir di Gerai">Kondisi Halaman Parkir di Gerai</option>
                            <option value="Kondisi Akses Jalan ke Gerai">Kondisi Akses Jalan ke Gerai</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prioritas</label>
                        <select name="prioritas" id="edit_prioritas"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Normal">Normal</option>
                            <option value="Mendesak">Mendesak</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Uraian Komplain *</label>
                        <textarea name="uraian" id="edit_uraian" rows="1" required oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px'"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none overflow-hidden"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeEditModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium cursor-pointer">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-lg hover:opacity-80 text-sm font-medium cursor-pointer" style="background:#DCFCE7;color:#16A34A">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Pilih Tahun --}}
    <div id="yearModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-black/50" onclick="closeYearModal()"></div>
        <div class="relative bg-white rounded-xl shadow-lg w-full max-w-sm mx-4 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-1">Pilih Tahun</h2>
            <p class="text-sm text-gray-500 mb-4" id="yearModalDesc"></p>
            <select id="yearSelect"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm mb-4">
                <option value="all">Semua Tahun</option>
                @foreach ($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
            <div class="flex gap-3">
                <button onclick="closeYearModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium cursor-pointer">Batal</button>
                <button onclick="downloadWithYear()" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium cursor-pointer">Download</button>
            </div>
        </div>
    </div>

<script>
try {
    flatpickr('.fp-date', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd M Y',
        locale: 'id',
        disableMobile: true
    });
} catch(e) {
    console.error('Flatpickr init failed:', e);
}

var komplainData = {!! json_encode($komplains->map(fn($k) => [
    'id' => $k->id,
    'periode' => $k->periode ?? '',
    'tanggal_komplain' => $k->tanggal_komplain?->format('Y-m-d') ?? '',
    'kode_gerai' => $k->kode_gerai,
    'nama_gerai' => $k->nama_gerai,
    'uraian' => $k->uraian,
    'media_laporan' => $k->media_laporan ?? '',
    'kategori_laporan' => $k->kategori_laporan ?? '',
    'prioritas' => $k->prioritas ?? 'Normal',
    'pic_penanganan' => $k->pic_penanganan ?? '',
    'tindak_lanjut' => $k->tindak_lanjut ?? '',
    'status' => $k->status ?? 'Open',
    'tanggal_follow_up' => $k->tanggal_follow_up?->format('Y-m-d') ?? '',
    'tanggal_close' => $k->tanggal_close?->format('Y-m-d') ?? '',
]), JSON_HEX_TAG) !!};

var geraiData = {!! json_encode($gerais, JSON_HEX_TAG) !!};

function renderGeraiSuggest(listId, query, onSelect) {
    var list = document.getElementById(listId);
    list.innerHTML = '';
    var val = query.toLowerCase();
    var items = geraiData.filter(function(g) {
        return g.kode_gerai.toLowerCase().indexOf(val) !== -1 || g.nama_gerai.toLowerCase().indexOf(val) !== -1;
    });
    if (items.length === 0) { list.classList.add('hidden'); return; }
    items.forEach(function(item) {
        var li = document.createElement('li');
        li.className = 'px-3 py-2 cursor-pointer hover:bg-blue-50 text-sm';
        li.innerHTML = '<span class="font-medium text-gray-800">' + item.kode_gerai + '</span><span class="text-gray-500"> - ' + item.nama_gerai + '</span>';
        li.addEventListener('mousedown', function(e) {
            e.preventDefault();
            onSelect(item);
            list.classList.add('hidden');
        });
        list.appendChild(li);
    });
    list.classList.remove('hidden');
}

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
    document.querySelector('#createModal form').reset();
    document.getElementById('createModal').classList.remove('hidden');
}
function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
}

function openEditModal(id) {
    closeFab();
    var k = komplainData.find(function(x) { return x.id === id; });
    if (!k) return;
    document.getElementById('editForm').action = '/komplain/' + id;
    var fpKC = document.getElementById('edit_tanggal_komplain')._flatpickr;
    if (fpKC) fpKC.setDate(k.tanggal_komplain, true);
    document.getElementById('edit_kode_gerai').value = k.kode_gerai;
    document.getElementById('edit_nama_gerai').value = k.nama_gerai;
    document.getElementById('edit_uraian').value = k.uraian;
    document.getElementById('edit_media_laporan').value = k.media_laporan;
    document.getElementById('edit_kategori_laporan').value = k.kategori_laporan;
    document.getElementById('edit_prioritas').value = k.prioritas;
    setTimeout(function() {
        var el = document.getElementById('edit_uraian');
        el.style.height = 'auto';
        el.style.height = el.scrollHeight + 'px';
    }, 0);
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

var suggestData = {!! json_encode($gerais->map(fn($g) => ['search' => $g->kode_gerai . ' ' . $g->nama_gerai, 'primary' => $g->kode_gerai, 'secondary' => $g->nama_gerai]), JSON_HEX_TAG) !!};

function filterKomplain(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#komplainTableBody tr').forEach(function(row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
    });
    var list = document.getElementById('komplainSuggest');
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
            document.getElementById('searchKomplain').value = item.primary;
            list.classList.add('hidden');
            filterKomplain(item.primary);
        });
        list.appendChild(li);
    });
    var btn = document.getElementById('searchKomplain').parentElement.querySelector('button');
    positionSuggest(btn, 'komplainSuggest');
    list.classList.remove('hidden');
}

document.getElementById('searchKomplain').addEventListener('blur', function() {
    setTimeout(function() { document.getElementById('komplainSuggest').classList.add('hidden'); }, 200);
});

var currentStatusFilter = '';

function toggleFilterOnMobile() {
    if (window.innerWidth >= 640) return;
    var filters = document.getElementById('filterButtons');
    var input = document.getElementById('searchKomplain');
    if (input.classList.contains('w-0')) {
        filters.classList.remove('hidden');
    } else {
        filters.classList.add('hidden');
    }
}

document.addEventListener('click', function(e) {
    if (window.innerWidth >= 640) return;
    setTimeout(function() {
        var input = document.getElementById('searchKomplain');
        var filters = document.getElementById('filterButtons');
        if (input && filters) {
            if (input.classList.contains('w-0')) {
                filters.classList.remove('hidden');
            } else {
                filters.classList.add('hidden');
            }
        }
    }, 10);
});

function filterByStatus(status) {
    currentStatusFilter = status;
    var rows = document.querySelectorAll('#komplainTableBody tr');
    rows.forEach(function(row) {
        if (!status) {
            row.style.display = '';
        } else {
            row.style.display = row.getAttribute('data-status') === status ? '' : 'none';
        }
    });
    var allBtn = document.getElementById('filterAll');
    var openBtn = document.getElementById('filterOpen');
    var prosesBtn = document.getElementById('filterProses');
    var closedBtn = document.getElementById('filterClosed');

    allBtn.style.background = !status ? '#374151' : '#F3F4F6';
    allBtn.style.color = !status ? '#FFFFFF' : '#4B5563';
    openBtn.style.background = status === 'Open' ? '#3B82F6' : '#DBEAFE';
    openBtn.style.color = status === 'Open' ? '#FFFFFF' : '#1D4ED8';
    prosesBtn.style.background = status === 'On Progress' ? '#F59E0B' : '#FEF3C7';
    prosesBtn.style.color = status === 'On Progress' ? '#FFFFFF' : '#D97706';
    closedBtn.style.background = status === 'Closed' ? '#16A34A' : '#DCFCE7';
    closedBtn.style.color = status === 'Closed' ? '#FFFFFF' : '#16A34A';
}

document.querySelectorAll('.uraian-truncate').forEach(function(el) {
    if (el.scrollHeight > el.clientHeight + 2) {
        var seeMore = el.nextElementSibling;
        seeMore.classList.remove('hidden');
    }
});

var yearModalType = '';

function openYearModal(type) {
    closeFab();
    yearModalType = type;
    document.getElementById('yearModalDesc').textContent = type === 'excel' ? 'Download data komplain dalam format Excel' : 'Download data komplain dalam format PDF';
    document.getElementById('yearModal').classList.remove('hidden');
    return false;
}

function closeYearModal() {
    document.getElementById('yearModal').classList.add('hidden');
}

function downloadWithYear() {
    var year = document.getElementById('yearSelect').value;
    var url = yearModalType === 'excel' ? '/komplain/excel/all' : '/komplain/pdf/all';
    if (year !== 'all') url += '?year=' + year;
    window.open(url, '_blank');
    closeYearModal();
}

['create', 'edit'].forEach(function(prefix) {
    var input = document.getElementById(prefix + '_kode_gerai');
    var namaInput = document.getElementById(prefix + '_nama_gerai');
    var suggestId = prefix + 'GeraiSuggest';
    input.addEventListener('focus', function() {
        renderGeraiSuggest(suggestId, this.value, function(item) {
            input.value = item.kode_gerai;
            namaInput.value = item.nama_gerai;
        });
    });
    input.addEventListener('input', function() {
        renderGeraiSuggest(suggestId, this.value, function(item) {
            input.value = item.kode_gerai;
            namaInput.value = item.nama_gerai;
        });
    });
    input.addEventListener('blur', function() {
        setTimeout(function() { document.getElementById(suggestId).classList.add('hidden'); }, 200);
    });
});
</script>
@endsection
