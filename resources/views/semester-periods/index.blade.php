@extends('layouts.admin')

@section('title', 'Periode Semester - MARS')

@section('content')
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <h2 class="text-base sm:text-lg font-semibold text-gray-800">Periode Semester</h2>
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
                                <button onclick='openEditModal({{ $p->id }}, {{ $p->year }}, {{ $p->start_month }}, {{ $p->end_month }})'
                                    class="inline-block px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100">Edit</button>
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

    {{-- FAB --}}
    <button onclick="openCreateModal()" class="fixed bottom-6 right-6 z-40 w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 focus:outline-none transition-all duration-200 flex items-center justify-center">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
    </button>

    {{-- Modal Tambah Periode --}}
    <div id="createModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="fixed inset-0 bg-black/50" onclick="closeCreateModal()"></div>
        <div class="relative bg-white rounded-xl shadow-lg w-full max-w-md mx-4 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Tambah Periode Semester</h3>
                <button type="button" onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="/semester-periods">
                @csrf
                <div class="mb-4">
                    <label for="modal_year" class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                    <input id="modal_year" type="number" name="year" min="2000" max="2099" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="modal_start_month" class="block text-sm font-medium text-gray-700 mb-1">Bulan Mulai (1-12)</label>
                        <input id="modal_start_month" type="number" name="start_month" min="1" max="12" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="modal_end_month" class="block text-sm font-medium text-gray-700 mb-1">Bulan Selesai (1-12)</label>
                        <input id="modal_end_month" type="number" name="end_month" min="1" max="12" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeCreateModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Periode --}}
    <div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="fixed inset-0 bg-black/50" onclick="closeEditModal()"></div>
        <div class="relative bg-white rounded-xl shadow-lg w-full max-w-md mx-4 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Edit Periode Semester</h3>
                <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="editForm" method="POST" action="">
                @csrf @method('PUT')
                <div class="mb-4">
                    <label for="edit_year" class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                    <input id="edit_year" type="number" name="year" min="2000" max="2099" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="edit_start_month" class="block text-sm font-medium text-gray-700 mb-1">Bulan Mulai (1-12)</label>
                        <input id="edit_start_month" type="number" name="start_month" min="1" max="12" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="edit_end_month" class="block text-sm font-medium text-gray-700 mb-1">Bulan Selesai (1-12)</label>
                        <input id="edit_end_month" type="number" name="end_month" min="1" max="12" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeEditModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
}
function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
}
function openEditModal(id, year, startMonth, endMonth) {
    document.getElementById('editForm').action = '/semester-periods/' + id;
    document.getElementById('edit_year').value = year;
    document.getElementById('edit_start_month').value = startMonth;
    document.getElementById('edit_end_month').value = endMonth;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeCreateModal(); closeEditModal(); }
});
</script>
@endpush
