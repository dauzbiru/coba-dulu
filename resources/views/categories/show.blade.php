@extends('layouts.admin')

@section('title', $category->name . ' - Tugas')

@push('head')
<style>
.sortable-ghost { opacity: 0.5; background: #eff6ff; }
.sortable-drag { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); transform: scale(1.02); }
</style>
@endpush

@section('content')
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <a href="/categories" class="text-sm text-blue-600 hover:underline">&larr; Kembali</a>
            <h2 class="text-lg sm:text-xl font-bold text-gray-800 mt-1">{{ $category->name }}</h2>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="openBobotModal()"
                class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700">Atur Bobot</button>
            <button type="button" onclick="openModal()"
                class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">+ Item</button>
        </div>
    </div>

    @if ($items->isNotEmpty())
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-4 sm:px-6 py-3 border-b border-gray-200 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-700">Daftar Item</h3>
            </div>

            <div class="divide-y divide-gray-100" id="sortable-items">
                @foreach ($items as $item)
                    <div class="p-4 sm:p-5" data-item-id="{{ $item->id }}">
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <h4 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                                <span class="drag-handle cursor-grab text-gray-300 hover:text-gray-500">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 6h2v2H8V6zm6 0h2v2h-2V6zM8 11h2v2H8v-2zm6 0h2v2h-2v-2zm-6 5h2v2H8v-2zm6 0h2v2h-2v-2z"/></svg>
                                </span>
                                <span class="item-number">{{ $loop->iteration }}.</span>
                                {{ $item->name }}
                                @if($item->bobot) <span class="text-xs text-gray-400 font-normal">(bobot {{ $item->bobot }})</span> @endif
                            </h4>
                            <div class="flex gap-1 shrink-0">
                                <a href="/items/{{ $item->id }}/edit" class="p-1 text-blue-600 hover:text-blue-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <button type="button" onclick="deleteItem({{ $item->id }}, '{{ $item->name }}')" class="p-1 text-red-500 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>

                        @if ($item->criteria->isNotEmpty())
                            <ul class="list-disc list-inside space-y-1 mb-3">
                                @foreach ($item->criteria as $c)
                                    <li class="text-sm text-gray-600">{{ $c->description }}</li>
                                @endforeach
                            </ul>
                            <a href="/items/{{ $item->id }}/criteria" class="text-xs text-gray-400 hover:text-blue-600">Edit opsi</a>
                        @else
                            <p class="text-xs text-gray-400 italic mb-2">Belum ada opsi. <a href="/items/{{ $item->id }}/criteria" class="text-blue-600 hover:underline">Tambah opsi</a></p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-md p-8 text-center text-sm text-gray-500">
            Belum ada item. <a href="#" onclick="openModal(); return false;" class="text-blue-600 hover:underline">Tambah item</a>
        </div>
    @endif

    <form id="deleteForm" method="POST" style="display:none">
        @csrf @method('DELETE')
    </form>

    {{-- Modal Tambah Item + Opsi --}}
    <div id="itemModal" class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center hidden" onclick="if (event.target===this) closeModal()">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Tambah Item</h3>
            <form method="POST" action="/categories/{{ $category->id }}/items">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Item</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" autocomplete="off">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bobot</label>
                    <input type="number" step="0.01" name="bobot" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" autocomplete="off">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Opsi Penilaian</label>
                    <div class="space-y-2">
                        @for ($i = 1; $i <= 5; $i++)
                            <input type="text" name="criteria[]" placeholder="Opsi {{ $i }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" autocomplete="off">
                        @endfor
                    </div>
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Atur Bobot --}}
    <div id="bobotModal" class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center hidden" onclick="if (event.target===this) closeBobotModal()">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6 max-h-[80vh] overflow-y-auto">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Atur Bobot</h3>
            @if ($items->isNotEmpty())
                <form method="POST" action="/categories/{{ $category->id }}/items/bobot">
                    @csrf @method('PUT')
                    <div class="divide-y divide-gray-100">
                        @foreach ($items as $item)
                            <div class="py-3 flex items-center gap-3">
                                <span class="text-sm text-gray-700 flex-1">{{ $item->name }}</span>
                                <input type="number" step="0.01" name="bobot[{{ $item->id }}]" value="{{ $item->bobot }}" placeholder="Bobot"
                                    class="w-24 px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" autocomplete="off">
                            </div>
                        @endforeach
                    </div>
                    <div class="flex gap-2 justify-end mt-4">
                        <button type="button" onclick="closeBobotModal()" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            @else
                <p class="text-sm text-gray-400 italic">Belum ada item.</p>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
var sortable = new Sortable(document.getElementById('sortable-items'), {
    handle: '.drag-handle',
    animation: 200,
    easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
    ghostClass: 'sortable-ghost',
    dragClass: 'sortable-drag',
    onEnd: function() {
        var items = [];
        document.querySelectorAll('#sortable-items [data-item-id]').forEach(function(el) {
            items.push(el.dataset.itemId);
        });

        var formData = new FormData();
        formData.append('_method', 'PUT');
        items.forEach(function(id) {
            formData.append('items[]', id);
        });

        fetch('/categories/{{ $category->id }}/items/reorder', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        }).then(function() {
            document.querySelectorAll('#sortable-items [data-item-id]').forEach(function(el, i) {
                el.querySelector('.item-number').textContent = (i + 1) + '.';
            });
        });
    }
});

function deleteItem(id, name) {
    showConfirm('Hapus item ' + name + '?', function() {
        var form = document.getElementById('deleteForm');
        form.action = '/items/' + id;
        form.submit();
    });
}

function openModal() {
    document.getElementById('itemModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('itemModal').classList.add('hidden');
}

function openBobotModal() {
    document.getElementById('bobotModal').classList.remove('hidden');
}

function closeBobotModal() {
    document.getElementById('bobotModal').classList.add('hidden');
}
</script>
@endpush
