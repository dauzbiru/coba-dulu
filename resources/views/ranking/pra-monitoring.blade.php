@extends('layouts.admin')

@section('title', 'Daftar Nilai Pra-Monitoring - MARS')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-3">
            <h1 class="text-lg font-bold text-gray-800">Daftar Nilai Pra-Monitoring</h1>
            <div class="relative flex items-center">
                <input type="text" id="searchPraMonitoring" placeholder="Cari gerai..."
                    class="w-0 px-0 border-0 py-2 text-sm focus:outline-none transition-all duration-200 ease-in-out"
                    autocomplete="off" value="{{ request('search') }}"
                    onkeydown="if(event.key==='Enter'){event.preventDefault();window.location='/ranking/pra-monitoring?search='+encodeURIComponent(this.value)}">
                <button type="button" onclick="var i=document.getElementById('searchPraMonitoring');if(i.classList.contains('w-0')){i.classList.remove('w-0','px-0','border-0');i.classList.add('w-48','sm:w-64','px-3','border','border-gray-300','rounded-lg');i.focus()}else if(i.value){window.location='/ranking/pra-monitoring?search='+encodeURIComponent(i.value)}else{i.classList.add('w-0','px-0','border-0');i.classList.remove('w-48','sm:w-64','px-3','border','border-gray-300','rounded-lg')}" class="shrink-0 p-2 text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </div>
        </div>

        @if ($reports->isEmpty())
            <div class="p-6 text-sm text-gray-400">Belum ada data pra-monitoring yang selesai.</div>
        @else
            <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-500">
                        <th class="px-2 sm:px-5 py-3 font-medium whitespace-nowrap">Gerai</th>
                        <th class="px-2 sm:px-5 py-3 font-medium whitespace-nowrap">Kode</th>
                        <th class="px-2 sm:px-5 py-3 font-medium whitespace-nowrap">Petugas</th>
                        <th class="px-2 sm:px-5 py-3 font-medium whitespace-nowrap hidden sm:table-cell">Tanggal</th>
                        <th class="px-2 sm:px-5 py-3 font-medium whitespace-nowrap text-right">Skor</th>
                        <th class="px-2 sm:px-5 py-3 font-medium whitespace-nowrap text-right">Grade</th>
                    </tr>
                </thead>
                <tbody id="praMonitoringTableBody">
                    @foreach ($reports as $r)
                        <tr class="border-t">
                            <td class="px-2 sm:px-5 py-3 font-medium text-gray-800 whitespace-nowrap">{{ $r['gerai']->nama_gerai }}</td>
                            <td class="px-2 sm:px-5 py-3 text-gray-500 whitespace-nowrap">{{ $r['gerai']->kode_gerai }}</td>
                            <td class="px-2 sm:px-5 py-3 text-gray-700 whitespace-nowrap">{{ $r['petugas'] }}</td>
                            <td class="px-2 sm:px-5 py-3 text-gray-500 whitespace-nowrap hidden sm:table-cell">{{ $r['tanggal']->format('d-m-Y') }}</td>
                            <td class="px-2 sm:px-5 py-3 text-right font-semibold text-blue-600 whitespace-nowrap">{{ $r['skor'] }}</td>
                            @php $grade = \App\Models\MonitoringReport::gradeFromScore((float) $r['skor']); @endphp
                            <td class="px-2 sm:px-5 py-3 text-right font-semibold whitespace-nowrap {{ $grade === 'A' ? 'text-green-600' : ($grade === 'B' ? 'text-blue-600' : ($grade === 'C' ? 'text-yellow-600' : ($grade === 'D' ? 'text-orange-500' : 'text-red-600'))) }}">{{ $grade }}</td>
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
@endsection