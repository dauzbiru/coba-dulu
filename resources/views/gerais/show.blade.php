@extends('layouts.admin')

@section('title', $gerai->nama_gerai . ' - Gerai')

@section('content')
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h2 class="text-lg sm:text-xl font-bold text-gray-800 mt-1">{{ $gerai->nama_gerai }}</h2>
        </div>
        <a href="/gerais/{{ $gerai->id }}/edit"
            class="inline-block text-center px-4 py-2 text-sm font-medium rounded-lg hover:opacity-80" style="background:#FEF3C7;color:#D97706">Edit</a>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full">
            <tbody class="divide-y divide-gray-200">
                <tr>
                    <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-500 w-1/3">Kode Gerai</td>
                    <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-800">{{ $gerai->kode_gerai }}</td>
                </tr>
                <tr>
                    <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-500">Nama Gerai</td>
                    <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-800">{{ $gerai->nama_gerai }}</td>
                </tr>
                <tr>
                    <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-500">Franchisee</td>
                    <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-800">{{ $gerai->franchisee }}</td>
                </tr>
                <tr>
                    <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-500">Opening</td>
                    <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-800">{{ $gerai->opening_at?->format('d-m-Y') ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-500">Nama Kota</td>
                    <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-800">{{ $gerai->nama_kota ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-500">Area</td>
                    <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-800">{{ $gerai->area ?? '-' }}</td>
                </tr>
        </tbody>
    </table>
        </div>
</div>
@endsection
