@extends('layouts.admin')

@section('title', 'Dashboard - Monapps')

@section('content')
    @if (isset($role) && $role === 'guest')
        <div class="max-w-lg mx-auto mt-16 text-center">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Selamat Datang, {{ auth()->user()->name }}</h1>
            <p class="text-gray-500 mb-8">Silakan pilih jenis laporan yang akan dibuat</p>
            <div class="flex flex-col gap-4">
                <a href="/monitoring"
                    class="block w-full px-6 py-5 bg-blue-600 text-white text-lg font-semibold rounded-xl hover:bg-blue-700 transition">
                    + Buat Laporan Monitoring
                </a>
                <a href="/pra-monitoring"
                    class="block w-full px-6 py-5 bg-gray-600 text-white text-lg font-semibold rounded-xl hover:bg-gray-700 transition">
                    + Buat Laporan Pra-Monitoring
                </a>
            </div>
        </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <p class="text-sm text-gray-500">Total Gerai</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalGerai }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <p class="text-sm text-gray-500">Monitoring {{ $periodeLabel }}</p>
            <p class="text-3xl font-bold text-blue-600 mt-1">{{ $monitoringPeriode }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <p class="text-sm text-gray-500">Pra-Monitoring Bulan Ini</p>
            <p class="text-3xl font-bold text-gray-600 mt-1">{{ $praMonitoringBulanIni }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Monitoring Terbaru --}}
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="px-5 py-4 border-b flex items-center justify-between">
                <h2 class="font-semibold text-gray-800">Monitoring Terbaru</h2>
                <a href="/report" class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
            </div>
            <div class="p-5">
                @if ($monitoringTerbaru->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada data monitoring.</p>
                @else
                    <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500">
                                <th class="pb-2 font-medium">Gerai</th>
                                <th class="pb-2 font-medium">Petugas</th>
                                <th class="pb-2 font-medium">Tanggal</th>
                                <th class="pb-2 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($monitoringTerbaru as $report)
                                <tr class="border-t">
                                    <td class="py-2.5 text-gray-800">{{ $report->gerai->nama_gerai }}</td>
                                    <td class="py-2.5 text-gray-500">{{ $report->user?->name ?? '-' }}</td>
                                    <td class="py-2.5 text-gray-500">{{ $report->checkin_at ? $report->checkin_at->format('d M Y') : '-' }}</td>
                                    <td class="py-2.5">
                                        @if ($report->submit_at)
                                            <span class="inline-block px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Selesai</span>
                                        @else
                                            <span class="inline-block px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Proses</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Pra-Monitoring Terbaru --}}
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="px-5 py-4 border-b flex items-center justify-between">
                <h2 class="font-semibold text-gray-800">Pra-Monitoring Terbaru</h2>
                <a href="/report/pre-monitoring" class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
            </div>
            <div class="p-5">
                @if ($praMonitoringTerbaru->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada data pra-monitoring.</p>
                @else
                    <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500">
                                <th class="pb-2 font-medium">Gerai</th>
                                <th class="pb-2 font-medium">Petugas</th>
                                <th class="pb-2 font-medium">Tanggal</th>
                                <th class="pb-2 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($praMonitoringTerbaru as $report)
                                <tr class="border-t">
                                    <td class="py-2.5 text-gray-800">{{ $report->gerai->nama_gerai }}</td>
                                    <td class="py-2.5 text-gray-500">{{ $report->user?->name ?? '-' }}</td>
                                    <td class="py-2.5 text-gray-500">{{ $report->checkin_at ? $report->checkin_at->format('d M Y') : '-' }}</td>
                                    <td class="py-2.5">
                                        @if ($report->submit_at)
                                            <span class="inline-block px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Selesai</span>
                                        @else
                                            <span class="inline-block px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Proses</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
@endsection
