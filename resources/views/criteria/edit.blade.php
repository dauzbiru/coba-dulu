@extends('layouts.admin')

@section('title', 'Edit Opsi - ' . $item->name)

@section('content')
<div class="max-w-md mx-auto">
    <a href="/categories/{{ $item->category_id }}" class="text-sm text-blue-600 hover:underline">&larr; Kembali</a>
    <h2 class="text-lg font-bold text-gray-800 mt-2 mb-4">Edit Opsi: {{ $item->name }}</h2>

    <form method="POST" action="/items/{{ $item->id }}/criteria/{{ $criterion->id }}" class="bg-white rounded-xl shadow-md p-4 sm:p-6 space-y-4">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Opsi</label>
            <input type="text" name="description" value="{{ old('description', $criterion->description) }}" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-400">
            @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="w-full py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">Simpan</button>
    </form>
</div>
@endsection
