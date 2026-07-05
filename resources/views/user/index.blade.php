@extends('layouts.admin')

@section('title', 'Admin - Monapps')

@section('content')
    @if ($tab === 'user')
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h2 class="text-base sm:text-lg font-semibold text-gray-800">Daftar User</h2>
                <div class="flex items-center gap-3">
                    <span class="text-xs sm:text-sm text-gray-500">{{ $users->count() }} user</span>
                    <a href="/user/create" class="px-3 py-1.5 bg-blue-600 text-white text-xs sm:text-sm font-medium rounded-lg hover:bg-blue-700">+ Tambah</a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[500px]">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider">
                            <th class="px-4 sm:px-6 py-3">#</th>
                            <th class="px-4 sm:px-6 py-3">Name</th>
                            <th class="px-4 sm:px-6 py-3">Username</th>
                            <th class="px-4 sm:px-6 py-3">Role</th>
                            <th class="px-4 sm:px-6 py-3 hidden sm:table-cell">Dibuat</th>
                            <th class="px-4 sm:px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($users as $u)
                            <tr class="hover:bg-gray-50 {{ $u->id === $user->id ? 'bg-blue-50' : '' }}">
                                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-500">{{ $loop->iteration }}</td>
                                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm font-medium text-gray-800 whitespace-nowrap">
                                    {{ $u->name }}
                                    @if ($u->id === $user->id)
                                        <span class="ml-1 text-[10px] sm:text-xs text-blue-600 font-normal">(kamu)</span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-600">{{ $u->username }}</td>
                                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm">
                                    @if ($u->role === 'guest')
                                        <span class="inline-block px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">Guest</span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">Admin</span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-500 hidden sm:table-cell">{{ $u->created_at->format('d M Y') }}</td>
                                <td class="px-4 sm:px-6 py-3 text-right whitespace-nowrap">
                                    <a href="/user/{{ $u->id }}/edit"
                                        class="inline-block px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100">Edit</a>
                                    @if ($u->id !== $user->id)
                                        <form method="POST" action="/user/{{ $u->id }}" onsubmit="showConfirm('Hapus user {{ $u->name }}?', function(){ this.submit(); }.bind(this)); return false;" class="inline">
                                            @csrf @method('DELETE')
                                            <button class="inline-block px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100">Hapus</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($users->isEmpty())
                <div class="px-4 sm:px-6 py-8 text-center text-sm text-gray-500">Belum ada user.</div>
            @endif
        </div>
    @endif
@endsection
