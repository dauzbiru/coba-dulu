@extends('layouts.admin')

@section('title', 'Tambah Tugas - Monapps')

@section('content')
    <div class="max-w-lg mx-auto bg-white rounded-xl shadow-md p-6 sm:p-8">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-6">Tambah Tugas</h1>

        @if ($parent)
            <p class="text-sm text-gray-500 mb-4">Tugas induk: <span class="font-semibold text-gray-700">{{ $parent->name }}</span></p>
        @endif

        <form method="POST" action="/categories">
            @csrf
            @if ($parent)
                <input type="hidden" name="parent_id" value="{{ $parent->id }}">
            @endif

            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Tugas</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <a href="{{ $parent ? '/categories/' . $parent->id : '/categories' }}" class="flex-1 text-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">Batal</a>
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Simpan</button>
            </div>
        </form>
    </div>
@endsection
