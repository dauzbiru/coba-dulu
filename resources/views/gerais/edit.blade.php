@extends('layouts.admin')

@section('title', 'Edit Gerai - Monapps')

@section('content')
    <div class="max-w-lg mx-auto bg-white rounded-xl shadow-md p-6 sm:p-8">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-6">Edit Gerai</h1>

        <form method="POST" action="/gerais/{{ $gerai->id }}">
            @csrf @method('PUT')

            <div class="mb-4">
                <label for="kode_gerai" class="block text-sm font-medium text-gray-700 mb-1">Kode Gerai</label>
                <input id="kode_gerai" type="text" name="kode_gerai" value="{{ old('kode_gerai', $gerai->kode_gerai) }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('kode_gerai')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="nama_gerai" class="block text-sm font-medium text-gray-700 mb-1">Nama Gerai</label>
                <input id="nama_gerai" type="text" name="nama_gerai" value="{{ old('nama_gerai', $gerai->nama_gerai) }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('nama_gerai')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="franchisee" class="block text-sm font-medium text-gray-700 mb-1">Franchisee</label>
                <input id="franchisee" type="text" name="franchisee" value="{{ old('franchisee', $gerai->franchisee) }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('franchisee')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="opening_at" class="block text-sm font-medium text-gray-700 mb-1">Opening</label>
                <input id="opening_at" type="date" name="opening_at" value="{{ old('opening_at', $gerai->opening_at?->format('Y-m-d')) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('opening_at')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <a href="/gerais" class="flex-1 text-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">Batal</a>
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Simpan</button>
            </div>
        </form>
    </div>
@endsection
