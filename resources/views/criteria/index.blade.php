@extends('layouts.admin')

@section('title', 'Opsi - ' . $item->name)

@push('head')
<style>
.sortable-ghost { opacity: 0.5; background: #eff6ff; }
.sortable-drag { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); transform: scale(1.02); }
</style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="/categories/{{ $item->category_id }}" class="text-sm text-blue-600 hover:underline">&larr; Kembali</a>
    <div class="flex items-center justify-between mt-2 mb-4">
        <h2 class="text-lg font-bold text-gray-800">Opsi: {{ $item->name }}</h2>
        <button type="button" onclick="toggleBatchForm()" class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700">+ Opsi</button>
    </div>

    @if ($criteria->isNotEmpty())
        <div class="space-y-2" id="sortable-criteria">
            @foreach ($criteria as $c)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3" data-criterion-id="{{ $c->id }}">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <span class="drag-handle cursor-grab text-gray-300 hover:text-gray-500 shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 6h2v2H8V6zm6 0h2v2h-2V6zM8 11h2v2H8v-2zm6 0h2v2h-2v-2zm-6 5h2v2H8v-2zm6 0h2v2h-2v-2z"/></svg>
                            </span>
                            <span class="criterion-number text-xs text-gray-400 w-5 shrink-0">{{ $loop->iteration }}.</span>
                            <span class="text-sm text-gray-700 truncate">{{ $c->description }}</span>
                        </div>
                        <div class="flex gap-1 shrink-0">
                            <a href="/items/{{ $item->id }}/criteria/{{ $c->id }}/edit" class="p-1 text-blue-600 hover:text-blue-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button type="button" onclick="deleteCriterion({{ $c->id }})" class="p-1 text-red-500 hover:text-red-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div id="empty-state" class="bg-white rounded-xl shadow-md p-8 text-center text-sm text-gray-500">
            Belum ada opsi.
        </div>
    @endif

    {{-- Batch add form --}}
    <form id="batchForm" method="POST" action="/items/{{ $item->id }}/criteria/batch" class="mt-4 hidden">
        @csrf
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 space-y-3">
            <p class="text-sm font-semibold text-gray-700">Tambah 5 Opsi Baru</p>
            @for ($i = 1; $i <= 5; $i++)
                <input type="text" name="descriptions[]" placeholder="Opsi {{ $i }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" autocomplete="off">
            @endfor
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">Simpan Semua</button>
                <button type="button" onclick="toggleBatchForm()" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300">Batal</button>
            </div>
        </div>
    </form>
</div>

<form id="deleteCriterionForm" method="POST" style="display:none">
    @csrf @method('DELETE')
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
var sortableEl = document.getElementById('sortable-criteria');
if (sortableEl) {
    new Sortable(sortableEl, {
        handle: '.drag-handle',
        animation: 200,
        easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
        ghostClass: 'sortable-ghost',
        dragClass: 'sortable-drag',
        onEnd: function() {
            var items = [];
            document.querySelectorAll('#sortable-criteria [data-criterion-id]').forEach(function(el) {
                items.push(el.dataset.criterionId);
            });

            var formData = new FormData();
            formData.append('_method', 'PUT');
            items.forEach(function(id) {
                formData.append('items[]', id);
            });

            fetch('/items/{{ $item->id }}/criteria/reorder', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            }).then(function() {
                document.querySelectorAll('#sortable-criteria [data-criterion-id]').forEach(function(el, i) {
                    el.querySelector('.criterion-number').textContent = (i + 1) + '.';
                });
            });
        }
    });
}

function deleteCriterion(id) {
    showConfirm('Hapus opsi ini?', function() {
        var form = document.getElementById('deleteCriterionForm');
        form.action = '/items/{{ $item->id }}/criteria/' + id;
        form.submit();
    });
}

function toggleBatchForm() {
    var form = document.getElementById('batchForm');
    form.classList.toggle('hidden');
    if (!form.classList.contains('hidden')) {
        form.querySelector('input')?.focus();
    }
}
</script>
@endpush
