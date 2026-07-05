@extends('layouts.admin')

@section('title', 'Periode Semester - Monapps')

@section('content')
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="text-base sm:text-lg font-semibold text-gray-800">Periode Semester</h2>
            <a href="/semester-periods/create"
                class="inline-block text-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">+ Periode</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px]">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-4 sm:px-6 py-3">Label</th>
                        <th class="px-4 sm:px-6 py-3">Tahun</th>
                        <th class="px-4 sm:px-6 py-3">Bulan Mulai</th>
                        <th class="px-4 sm:px-6 py-3">Bulan Selesai</th>
                        <th class="px-4 sm:px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($periods as $p)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-800">{{ $p->label }}</td>
                            <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-600">{{ $p->year }}</td>
                            <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-600">{{ $p->start_month }}</td>
                            <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-600">{{ $p->end_month }}</td>
                            <td class="px-4 sm:px-6 py-3 text-right whitespace-nowrap">
                                <a href="/semester-periods/{{ $p->id }}/edit"
                                    class="inline-block px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100">Edit</a>
                                <form method="POST" action="/semester-periods/{{ $p->id }}" onsubmit="showConfirm('Hapus periode ini?', function(){ this.submit(); }.bind(this)); return false;" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="inline-block px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 sm:px-6 py-8 text-center text-sm text-gray-500">Belum ada periode semester.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
