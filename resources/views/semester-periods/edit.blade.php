@extends('layouts.admin')

@section('title', 'Edit Periode Semester - Monapps')

@section('content')
    <div class="max-w-lg mx-auto bg-white rounded-xl shadow-md p-6 sm:p-8">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-6">Edit Periode Semester</h1>

        <form method="POST" action="/semester-periods/{{ $semesterPeriod->id }}">
            @csrf @method('PUT')

            <div class="mb-4">
                <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                <input id="year" type="number" name="year" min="2000" max="2099"
                    value="{{ old('year', $semesterPeriod->year) }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('year')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="start_month" class="block text-sm font-medium text-gray-700 mb-1">Bulan Mulai (1-12)</label>
                    <input id="start_month" type="number" name="start_month" min="1" max="12"
                        value="{{ old('start_month', $semesterPeriod->start_month) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('start_month')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_month" class="block text-sm font-medium text-gray-700 mb-1">Bulan Selesai (1-12)</label>
                    <input id="end_month" type="number" name="end_month" min="1" max="12"
                        value="{{ old('end_month', $semesterPeriod->end_month) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('end_month')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex gap-3">
                <a href="/semester-periods" class="flex-1 text-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">Batal</a>
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Simpan</button>
            </div>
        </form>
    </div>
@endsection
