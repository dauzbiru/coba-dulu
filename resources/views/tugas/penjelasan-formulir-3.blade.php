@extends('layouts.admin')

@section('title', $title . ' - Monapps')

@section('content')
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h2 class="text-base sm:text-lg font-semibold text-gray-800">{{ $title }}</h2>
        <div class="flex gap-2">
            <a href="/tugas/penjelasan-formulir/{{ $formulir }}/import"
                class="px-3 py-2 text-xs font-medium text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100">
                Import Excel
            </a>
            <button type="button" onclick="document.getElementById('modalTambah').classList.remove('hidden')"
                class="px-3 py-2 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                + Tambah
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[600px]">
            <thead>
                <tr class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <th class="px-4 sm:px-6 py-3 w-10">No</th>
                    <th class="px-4 sm:px-6 py-3">Kondisi</th>
                    <th class="px-4 sm:px-6 py-3">Penjelasan</th>
                    <th class="px-4 sm:px-6 py-3 w-24"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 sm:px-6 py-3 text-xs text-gray-400 font-mono">{{ $loop->iteration }}</td>
                        <td class="px-4 sm:px-6 py-3 text-sm text-gray-800">{{ $item->kondisi }}</td>
                        <td class="px-4 sm:px-6 py-3 text-sm text-gray-600">{{ $item->penjelasan ?? '-' }}</td>
                        <td class="px-4 sm:px-6 py-3 text-right whitespace-nowrap">
                            <button type="button" onclick="editItem({{ $item->id }}, '{{ addslashes($item->kondisi) }}', '{{ addslashes($item->penjelasan ?? '') }}')"
                                class="inline-block px-2 py-1 text-xs font-medium text-yellow-600 bg-yellow-50 rounded hover:bg-yellow-100">Edit</button>
                            <form method="POST" action="/tugas/penjelasan-formulir/{{ $item->id }}" onsubmit="showConfirm('Hapus item ini?', function(){ this.submit(); }.bind(this)); return false;" class="inline">
                                @csrf @method('DELETE')
                                <button class="inline-block px-2 py-1 text-xs font-medium text-red-600 bg-red-50 rounded hover:bg-red-100">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 sm:px-6 py-8 text-center text-sm text-gray-500">Belum ada item.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="modalTambah" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-2xl mx-4">
        <h3 class="text-base font-semibold text-gray-800 mb-4">Tambah Item</h3>
        <form method="POST" action="/tugas/penjelasan-formulir/{{ $formulir }}">
            @csrf
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Kondisi</label>
                <textarea name="kondisi" required rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Tulis kondisi..."></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Penjelasan</label>
                <textarea name="penjelasan" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Tulis penjelasan..."></textarea>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEdit" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-2xl mx-4">
        <h3 class="text-base font-semibold text-gray-800 mb-4">Edit Item</h3>
        <form method="POST" id="formEdit">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Kondisi</label>
                <textarea name="kondisi" id="editKondisi" required rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Penjelasan</label>
                <textarea name="penjelasan" id="editPenjelasan" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function editItem(id, kondisi, penjelasan) {
    document.getElementById('formEdit').action = '/tugas/penjelasan-formulir/' + id;
    document.getElementById('editKondisi').value = kondisi;
    document.getElementById('editPenjelasan').value = penjelasan;
    document.getElementById('modalEdit').classList.remove('hidden');
}
</script>
@endsection
