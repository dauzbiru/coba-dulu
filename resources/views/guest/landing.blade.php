@extends('layouts.admin')

@section('title', 'MARS - Guest')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="text-center mb-6">
        <h2 class="text-lg font-bold text-gray-800">Selamat Datang, {{ auth()->user()->name }}</h2>
        <p class="text-sm text-gray-500 mt-1">Pilih jenis laporan yang akan dibuat</p>
    </div>
    <div class="space-y-3">
        <a href="/monitoring"
            class="block bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-5 text-white shadow-lg hover:shadow-xl active:scale-[0.98] transition-all">
            <h3 class="text-lg font-bold">Monitoring</h3>
            <p class="text-sm text-blue-200 mt-1">Buat laporan monitoring reguler</p>
        </a>
        <a href="/pra-monitoring"
            class="block bg-gradient-to-r from-gray-600 to-gray-700 rounded-xl p-5 text-white shadow-lg hover:shadow-xl active:scale-[0.98] transition-all">
            <h3 class="text-lg font-bold">Pra-Monitoring</h3>
            <p class="text-sm text-gray-300 mt-1">Buat laporan pra-monitoring</p>
        </a>
        <a href="/re-monitoring"
            class="block bg-gradient-to-r from-gray-600 to-gray-700 rounded-xl p-5 text-white shadow-lg hover:shadow-xl active:scale-[0.98] transition-all">
            <h3 class="text-lg font-bold">Re-Monitoring</h3>
            <p class="text-sm text-gray-300 mt-1">Buat laporan re-monitoring</p>
        </a>
    </div>
</div>
@endsection
