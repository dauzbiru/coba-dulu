@extends('layouts.admin')

@section('title', 'Tambah Opsi - ' . $item->name)

@section('content')
<div class="max-w-md mx-auto">
    <h2 class="text-lg font-bold text-gray-800 mt-2 mb-4">Tambah Opsi: {{ $item->name }}</h2>

    <form method="POST" action="/items/{{ $item->id }}/criteria" class="bg-white rounded-xl shadow-md p-4 sm:p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Opsi</label>
            <input type="text" name="description" value="{{ old('description') }}" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-400">
            @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="w-full py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">Simpan</button>
    </form>
</div>
@endsection
