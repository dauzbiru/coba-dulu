@extends('layouts.admin')

@section('title', 'Tugas - Monapps')

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
            <div class="flex gap-2">
                <a href="/categories/create"
                class="inline-block text-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">+ Tugas</a>
            </div>
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
                        <a href="/categories/{{ $cat->id }}/edit" class="p-1.5 text-blue-500 hover:text-blue-700 hover:bg-blue-50 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
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
