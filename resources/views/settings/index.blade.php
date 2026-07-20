@extends('layouts.admin')

@section('title', 'Pengaturan - MARS')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <svg class="w-7 h-7 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <h1 class="text-2xl font-bold text-gray-800">Pengaturan</h1>
    </div>

    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-1">AI Typo Checker</h2>
            <p class="text-sm text-gray-500 mb-4">Prompt yang dikirim ke Gemini API untuk koreksi typo.</p>

            <label for="ai_typo_prompt" class="block text-sm font-medium text-gray-700 mb-2">Prompt Template</label>
            <textarea
                name="ai_typo_prompt"
                id="ai_typo_prompt"
                rows="10"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
                required
            >{{ old('ai_typo_prompt', $settings['ai_typo_prompt'] ?? '') }}</textarea>

            <p class="text-xs text-gray-400 mt-2">Teks pengguna akan ditambahkan di akhir prompt dengan format: <code class="bg-gray-100 px-1 rounded">Teks:\n{teks}</code></p>

            <div class="mt-4 flex justify-end">
                <button type="submit" class="px-5 py-2 rounded-lg text-sm font-medium hover:opacity-80 transition-colors" style="background:#DCFCE7;color:#16A34A">
                    Simpan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
