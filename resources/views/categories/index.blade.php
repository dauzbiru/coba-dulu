@extends('layouts.admin')

@section('title', 'Tugas - MARS')

@push('head')
<style>
.sortable-ghost { opacity: 0.5; background: #eff6ff; }
.sortable-drag { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); transform: scale(1.02); }
</style>
@endpush

@section('content')
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="text-base sm:text-lg font-semibold text-gray-800">
                Tugas
                @php $totalBobot = $categories->sum('items_sum_bobot'); @endphp
                @if ($totalBobot > 0)
                    <span class="text-xs font-normal text-gray-400 ml-2">total bobot {{ $totalBobot }}</span>
                @endif
            </h2>

        </div>

        <div class="p-4 sm:p-6 space-y-3" id="sortable-categories">
            @forelse ($categories as $cat)
                <div class="relative group p-5 bg-white border border-gray-200 rounded-xl hover:shadow-md hover:border-blue-300 transition flex items-start gap-3" data-category-id="{{ $cat->id }}">
                    <span class="drag-handle cursor-grab text-gray-300 hover:text-gray-500 mt-0.5 shrink-0">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 6h2v2H8V6zm6 0h2v2h-2V6zM8 11h2v2H8v-2zm6 0h2v2h-2v-2zm-6 5h2v2H8v-2zm6 0h2v2h-2v-2z"/></svg>
                    </span>
                    <a href="/categories/{{ $cat->id }}" class="block flex-1 min-w-0">
                        <h3 class="text-base font-semibold text-gray-800 mb-1">{{ $cat->name }}</h3>
                        <p class="text-xs text-gray-400">
                            {{ $cat->items_count }} checklist
                            @if ($cat->items_sum_bobot)
                                · total bobot {{ $cat->items_sum_bobot }}
                            @endif
                            @if ($cat->children_count > 0)
                                · {{ $cat->children_count }} sub-tugas
                            @endif
                        </p>
                    </a>
                    <div class="flex gap-1 shrink-0">
                        <button onclick="openEditModal({{ $cat->id }})" class="p-1.5 rounded-lg cursor-pointer" style="background:#FEF3C7;color:#D97706">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <form method="POST" action="/categories/{{ $cat->id }}" onsubmit="showConfirm('Hapus kategori ini?', function(){ this.submit(); }.bind(this)); return false;">
                            @csrf @method('DELETE')
                            <button class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-sm text-gray-500">Belum ada tugas.</div>
            @endforelse
        </div>
    </div>

<button onclick="openCreateModal()"
    style="background:#3B82F6;color:#FFFFFF"
    class="fixed bottom-6 right-6 z-40 w-14 h-14 rounded-full shadow-lg hover:opacity-80 flex items-center justify-center cursor-pointer">
    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
        <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
    </svg>
</button>

{{-- Modal Create --}}
<div id="createModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeCreateModal()"></div>
    <div class="relative bg-white rounded-xl shadow-lg w-full max-w-lg mx-4 p-6 sm:p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Tambah Tugas</h2>
        <form method="POST" action="/categories">
            @csrf
            <div class="mb-6">
                <label for="create_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Tugas</label>
                <input id="create_name" type="text" name="name" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeCreateModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium cursor-pointer">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2 rounded-lg hover:opacity-80 text-sm font-medium cursor-pointer" style="background:#DCFCE7;color:#16A34A">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeEditModal()"></div>
    <div class="relative bg-white rounded-xl shadow-lg w-full max-w-lg mx-4 p-6 sm:p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Edit Tugas</h2>
        <form id="editForm" method="POST" action="">
            @csrf @method('PUT')
            <div class="mb-6">
                <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Tugas</label>
                <input id="edit_name" type="text" name="name" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeEditModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium cursor-pointer">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2 rounded-lg hover:opacity-80 text-sm font-medium cursor-pointer" style="background:#DCFCE7;color:#16A34A">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
var categoryData = {!! json_encode($categories->map(fn($c) => [
    'id' => $c->id,
    'name' => $c->name,
]), JSON_HEX_TAG) !!};

function openCreateModal() {
    document.getElementById('create_name').value = '';
    document.getElementById('createModal').classList.remove('hidden');
}
function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
}

function openEditModal(id) {
    var c = categoryData.find(function(x) { return x.id === id; });
    if (!c) return;
    document.getElementById('editForm').action = '/categories/' + id;
    document.getElementById('edit_name').value = c.name;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
new Sortable(document.getElementById('sortable-categories'), {
    handle: '.drag-handle',
    animation: 200,
    easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
    ghostClass: 'sortable-ghost',
    dragClass: 'sortable-drag',
    onEnd: function() {
        var items = [];
        document.querySelectorAll('#sortable-categories [data-category-id]').forEach(function(el) {
            items.push(el.dataset.categoryId);
        });

        var formData = new FormData();
        formData.append('_method', 'PUT');
        items.forEach(function(id) {
            formData.append('items[]', id);
        });

        fetch('/categories/reorder', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        });
    }
});
</script>
@endpush
