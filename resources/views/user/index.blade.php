@extends('layouts.admin')

@section('title', 'Admin - MARS')

@section('content')
    @if ($tab === 'user')
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h2 class="text-base sm:text-lg font-semibold text-gray-800">Daftar User</h2>
                <span class="text-xs sm:text-sm text-gray-500">{{ $users->count() }} user</span>
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
                                <td class="px-4 sm:px-6 py-3 text-xs sm:text-sm text-gray-500 hidden sm:table-cell">{{ $u->created_at->format('d-m-Y') }}</td>
                                <td class="px-4 sm:px-6 py-3 text-right whitespace-nowrap">
                                    <button onclick="openEditModal({{ $u->id }}, '{{ str_replace("'", "\\'", $u->name) }}', '{{ $u->username }}', '{{ $u->role }}')"
                                        class="inline-block px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100">Edit</button>
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

    {{-- Floating Tambah --}}
    <button onclick="openCreateModal()"
        class="fixed bottom-6 right-6 z-40 w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 flex items-center justify-center">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
        </svg>
    </button>

    {{-- Modal Create --}}
    <div id="createModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="fixed inset-0 bg-black/50" onclick="closeCreateModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
            <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-6">Tambah Petugas</h2>
            <form method="POST" action="/user">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                    <input type="text" name="name" required autofocus
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" name="username" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <div class="flex gap-4">
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="role" value="admin" checked class="text-blue-600">
                            <span class="text-sm text-gray-700">Admin</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="role" value="guest" class="text-blue-600">
                            <span class="text-sm text-gray-700">Guest</span>
                        </label>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeCreateModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="fixed inset-0 bg-black/50" onclick="closeEditModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
            <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-6">Edit User</h2>
            <form method="POST" action="" id="editForm">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                    <input type="text" name="name" id="editName" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" name="username" id="editUsername" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <hr class="my-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru <span class="text-gray-400 font-normal">(kosongkan jika tidak diubah)</span></label>
                    <input type="password" name="password"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <div class="flex gap-4">
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="role" value="admin" id="editRoleAdmin" class="text-blue-600">
                            <span class="text-sm text-gray-700">Admin</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="role" value="guest" id="editRoleGuest" class="text-blue-600">
                            <span class="text-sm text-gray-700">Guest</span>
                        </label>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeEditModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>

<script>
function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
}
function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
}
function openEditModal(id, name, username, role) {
    document.getElementById('editForm').action = '/user/' + id;
    document.getElementById('editName').value = name;
    document.getElementById('editUsername').value = username;
    document.getElementById('editRoleAdmin').checked = role === 'admin';
    document.getElementById('editRoleGuest').checked = role === 'guest';
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
@endsection
