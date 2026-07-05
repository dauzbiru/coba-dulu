@extends('layouts.admin')

@section('title', 'Laporan - ' . $report->gerai->nama_gerai)

@section('content')
<div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
    <div>
        <a href="{{ $prefix === 'pra-monitoring' ? '/report/pre-monitoring' : '/report' }}" class="text-sm text-blue-600 hover:underline">&larr; Kembali ke Laporan</a>
        <h2 class="text-lg sm:text-xl font-bold text-gray-800 mt-1">{{ $report->gerai->kode_gerai }} - {{ $report->gerai->nama_gerai }}</h2>
    </div>
    <div class="flex gap-2">
        <button onclick="showPdfModal()" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">PDF</button>
        <a href="/{{ $prefix }}/{{ $report->id }}/excel" class="px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700">Excel</a>
        <button onclick="showWaModal()" class="px-4 py-2 bg-green-500 text-white text-sm font-medium rounded-lg hover:bg-green-600">WhatsApp</button>
        <a href="/{{ $prefix }}/{{ $report->id }}/assessment" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">Edit</a>
    </div>
</div>

<div class="bg-white rounded-xl shadow-md overflow-hidden mb-4">
    <div class="px-4 sm:px-6 py-3 border-b border-gray-200 bg-gray-50">
        <h3 class="text-sm font-semibold text-gray-700">Informasi Laporan</h3>
    </div>
    <div class="overflow-x-auto">
    <table class="w-full">
        <tbody class="divide-y divide-gray-200">
            <tr>
                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-500 w-1/3">Kode Gerai</td>
                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-800">{{ $report->gerai->kode_gerai }}</td>
            </tr>
            <tr>
                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-500">Nama Gerai</td>
                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-800">{{ $report->gerai->nama_gerai }}</td>
            </tr>
            <tr>
                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-500">Franchisee</td>
                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-800">{{ $report->gerai->franchisee }}</td>
            </tr>
            <tr>
                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-500">Periode</td>
                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-800">{{ $report->periode_label ?? '-' }}</td>
            </tr>
            <tr>
                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-500">Lokasi Checkin</td>
                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-800">{{ $report->location }}</td>
            </tr>
            <tr>
                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-500">Checkin</td>
                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-800">{{ $report->checkin_at->format('d-m-Y H:i:s') }}</td>
            </tr>
            <tr>
                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-500">Submit</td>
                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-800">{{ $report->submit_at ? $report->submit_at->format('d-m-Y H:i:s') : '-' }}</td>
            </tr>
            <tr>
                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-500">Petugas</td>
                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-800">{{ $report->user?->name ?? '-' }}</td>
            </tr>
        </tbody>
    </table>
    </div>
</div>

<div class="bg-white rounded-xl shadow-md overflow-hidden mb-4">
    <div class="px-4 sm:px-6 py-3 border-b border-gray-200 bg-gray-50">
        <h3 class="text-sm font-semibold text-gray-700">Total Nilai</h3>
    </div>
    <div class="p-4 sm:p-5">
        <p class="text-2xl font-bold text-blue-600">{{ $totalScore }}</p>
        @php $grade = \App\Models\MonitoringReport::gradeFromScore((float) $totalScore); @endphp
        <p class="text-lg font-semibold {{ $grade === 'A' ? 'text-green-600' : ($grade === 'B' ? 'text-blue-600' : ($grade === 'C' ? 'text-yellow-600' : ($grade === 'D' ? 'text-orange-500' : 'text-red-600'))) }}">Grade: {{ $grade }}</p>
    </div>
</div>

@if ($report->finding)
    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-4">
        <div class="px-4 sm:px-6 py-3 border-b border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-700">Temuan Monitoring</h3>
        </div>
        <div class="p-4 sm:p-5 space-y-3">
            @if ($report->finding->major)
                <div>
                    <p class="text-xs font-medium text-gray-500">Major</p>
                    <p class="text-sm text-gray-800 whitespace-pre-wrap" style="overflow-wrap: break-word; word-break: break-word;">{{ e($report->finding->major) }}</p>
                </div>
            @endif
            @if ($report->finding->minor)
                <div>
                    <p class="text-xs font-medium text-gray-500">Minor</p>
                    <p class="text-sm text-gray-800 whitespace-pre-wrap" style="overflow-wrap: break-word; word-break: break-word;">{{ e($report->finding->minor) }}</p>
                </div>
            @endif
            @if ($report->finding->pengawas || $report->finding->rata_rata_aj || ($report->finding->tds && $prefix !== 'pra-monitoring') || $report->finding->mesin_ozon || $report->finding->peringatan_awal || $report->finding->note || $report->finding->kondisi_cat || $report->finding->kondisi_awning || $report->finding->kondisi_vinyl || $report->finding->kondisi_stiker_kaca)
                <div class="text-sm text-gray-800">
                    <p class="text-xs font-medium text-gray-500 mb-1">Peringatan Awal:</p>
                    @if ($report->finding->pengawas)
                        <div class="my-0 whitespace-pre-wrap" style="overflow-wrap: break-word; word-break: break-word;">{{ e($report->finding->pengawas) }}</div>
                    @endif
                    @if ($report->finding->rata_rata_aj)
                        <p class="my-0">Rerata AJ ± {{ $report->finding->rata_rata_aj }} gln/hr</p>
                    @endif
                    @if ($report->finding->tds && $prefix !== 'pra-monitoring')
                        @php $tdsDisplay = str_replace('/', ' ppm/', $report->finding->tds) . (str_contains($report->finding->tds, '/') ? '°C' : ''); @endphp
                        <p class="my-0">TDS: {{ $tdsDisplay }}</p>
                    @endif
                    @if ($report->finding->mesin_ozon)
                        <p class="my-0">MO: {{ $report->finding->mesin_ozon }}</p>
                    @endif
                    @if ($report->finding->peringatan_awal)
                        <div class="mt-4 mb-0 whitespace-pre-wrap" style="overflow-wrap: break-word; word-break: break-word;">{{ e($report->finding->peringatan_awal) }}</div>
                    @endif
                    @if ($report->finding->note)
                        <p class="mt-4 mb-0">Note:</p>
                        <div class="my-0 whitespace-pre-wrap" style="overflow-wrap: break-word; word-break: break-word;">{{ e($report->finding->note) }}</div>
                    @endif
                    @if ($report->finding->kondisi_cat || $report->finding->kondisi_awning || $report->finding->kondisi_vinyl || $report->finding->kondisi_stiker_kaca)
                        <p class="mt-4 mb-0">Checklist tampilan gerai:</p>
                        <p class="my-0">Kondisi cat: {{ $report->finding->kondisi_cat ?: 'Baik' }}</p>
                        <p class="my-0">Kondisi awning: {{ $report->finding->kondisi_awning ?: 'Baik' }}</p>
                        <p class="my-0">Kondisi vinyl reklame dinding/jalan: {{ $report->finding->kondisi_vinyl ?: 'Baik' }}</p>
                        <p class="my-0">Kondisi stiker kaca: {{ $report->finding->kondisi_stiker_kaca ?: 'Baik' }}</p>
                    @endif
                </div>
            @endif
            @php
                $penjelasanIsi = $report->finding->penjelasan_isi ?? [];
            @endphp
            @if (!empty(array_filter($penjelasanIsi)))
                <div class="space-y-2 mb-4">
                    <p class="text-xs font-medium text-gray-500">Penjelasan Formulir 2</p>
                    @foreach ($penjelasanIsi as $i => $teks)
                        @if (trim($teks))
                            <p class="text-sm text-gray-800">{{ $i + 1 }}. {{ $teks }}</p>
                        @endif
                    @endforeach
                </div>
            @endif
            @php
                $penjelasanIsi3 = $report->finding->penjelasan_isi_3 ?? [];
            @endphp
            @if (!empty(array_filter($penjelasanIsi3)))
                <div class="space-y-2 mb-4">
                    <p class="text-xs font-medium text-gray-500">Penjelasan Formulir 3</p>
                    @foreach ($penjelasanIsi3 as $itemId => $teks)
                        @if (trim($teks))
                            <p class="text-sm text-gray-800">{{ $loop->iteration }}. {{ $teks }}</p>
                        @endif
                    @endforeach
                </div>
            @endif
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-xs font-medium mb-1">TTD Petugas</p>
                    @if ($report->finding->ttd_petugas)
                        <img src="{{ asset('storage/' . $report->finding->ttd_petugas) }}" class="h-16 w-auto rounded border">
                    @else
                        <p class="text-sm italic">Belum ada</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-xs font-medium mb-1">TTD Pimpinan</p>
                    @if ($report->finding->ttd_pimpinan)
                        <img src="{{ asset('storage/' . $report->finding->ttd_pimpinan) }}" class="h-16 w-auto rounded border ml-auto">
                    @else
                        <p class="text-sm italic">Belum ada</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

@forelse ($filteredCategories as $cat)
    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-4">
        <div class="px-4 sm:px-6 py-3 border-b border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-700">{{ $cat->name }}</h3>
        </div>

        @if ($cat->items->isNotEmpty())
            <div class="divide-y divide-gray-100">
                @foreach ($cat->items as $item)
                    @php $r = $results->get($item->id) @endphp
                    @php
                        $count = $item->criteria->count();
                        $interval = $item->bobot && $count > 1 ? $item->bobot / ($count - 1) : null;
                    @endphp
                    <div class="p-4 sm:p-5">
                        <p class="text-sm font-medium text-gray-800">{{ $loop->iteration }}. {{ $item->name }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            Nilai: <strong class="text-blue-600">{{ $r && $r->criterion ? $r->criterion->description : '-' }}</strong>
                            @if ($r && $r->criterion && $interval !== null)
                                @php $selectedIdx = $item->criteria->search(fn($c) => $c->id === $r->criterion_id) @endphp
                                @if ($selectedIdx !== false)
                                    <span class="text-gray-400 ml-1">({{ $item->bobot - ($interval * $selectedIdx) }})</span>
                                @endif
                            @endif
                        </p>
                        @if ($r && $r->notes)
                            <p class="text-xs text-gray-400 mt-0.5">Catatan: {{ $r->notes }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-4 text-sm text-gray-400 italic">Belum ada item.</div>
        @endif
    </div>
@empty
    <div class="bg-white rounded-xl shadow-md p-8 text-center text-sm text-gray-500">
        Belum ada kategori penilaian.
    </div>
@endforelse
@endsection

{{-- PDF Modal --}}
<div id="pdfModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closePdfModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl max-w-sm w-full mx-4 p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-1">Pilih Opsi PDF</h3>
        <p class="text-xs text-gray-500 mb-4">Apakah laporan ini perlu ditandai sebagai revisi?</p>
        <div class="mt-4 flex justify-end gap-3">
            <button onclick="downloadPdf(0)" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Tidak Revisi</button>
            <button onclick="downloadPdf(1)" class="px-4 py-2 bg-yellow-500 text-white text-sm font-medium rounded-lg hover:bg-yellow-600">Revisi</button>
        </div>
    </div>
</div>

{{-- WhatsApp Modal --}}
<div id="waModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeWaModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl max-w-sm w-full mx-4 p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-1">Kirim via WhatsApp</h3>
        <p class="text-xs text-gray-500 mb-4">Masukkan nomor tujuan dengan kode negara (contoh: 62812...)</p>
        <input type="text" id="waNumber" placeholder="08123456789" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
        <p id="waError" class="text-xs text-red-500 mt-1 hidden">Nomor tidak valid. Gunakan format: 628xx...</p>
        <div class="mt-4 flex justify-end gap-3">
            <button onclick="closeWaModal()" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300">Batal</button>
            <button onclick="sendWa()" class="px-4 py-2 bg-green-500 text-white text-sm font-medium rounded-lg hover:bg-green-600">Kirim</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
var geraiName = '{{ $report->gerai->kode_gerai }} - {{ $report->gerai->nama_gerai }}';
var tgl = '{{ $report->checkin_at->format("d-m-Y") }}';
var prefix = '{{ ucfirst(str_replace("-", " ", $prefix)) }}';

var pdfUrl = '/{{ $prefix }}/{{ $report->id }}/pdf';

function showPdfModal() {
    document.getElementById('pdfModal').classList.remove('hidden');
}

function closePdfModal() {
    document.getElementById('pdfModal').classList.add('hidden');
}

function downloadPdf(revisi) {
    closePdfModal();
    window.location.href = pdfUrl + (revisi ? '?revisi=1' : '');
}

function showWaModal() {
    document.getElementById('waModal').classList.remove('hidden');
    document.getElementById('waNumber').value = '';
    document.getElementById('waError').classList.add('hidden');
    setTimeout(function(){ document.getElementById('waNumber').focus(); }, 100);
}

function closeWaModal() {
    document.getElementById('waModal').classList.add('hidden');
}

function sendWa() {
    var number = document.getElementById('waNumber').value.trim().replace(/[^0-9]/g, '');
    if (number.length < 8) {
        document.getElementById('waError').classList.remove('hidden');
        return;
    }
    document.getElementById('waError').classList.add('hidden');
    closeWaModal();
    if (number.startsWith('0')) {
        number = '62' + number.slice(1);
    }
    var text = 'Laporan ' + prefix + ' Gerai ' + geraiName + ' Tanggal ' + tgl;
    window.open('https://wa.me/' + number + '?text=' + encodeURIComponent(text), '_blank');
}
</script>
@endpush
