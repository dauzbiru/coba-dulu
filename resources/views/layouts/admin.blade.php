<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - Monapps')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('head')
</head>
<body class="bg-gray-100 min-h-screen">
    {{-- Overlay --}}
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-20 hidden" onclick="toggleSidebar()"></div>

    {{-- Sidebar --}}
    <aside id="sidebar"
        class="fixed inset-y-0 left-0 z-30 w-64 bg-white shadow-md border-r transform -translate-x-full transition-transform duration-200 flex flex-col">

        <div class="p-5 border-b flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Monapps</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ auth()->user()->name }}</p>
            </div>
            <button onclick="toggleSidebar()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <nav class="p-4 space-y-1 flex-1 overflow-y-auto">
            @if (auth()->user()->role === 'guest')
                <a href="/report"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->is('report') && !request()->is('report/pre-monitoring') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Laporan Monitoring
                </a>
                <a href="/report/pre-monitoring"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->is('report/pre-monitoring') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Laporan Pra-Monitoring
                </a>
            @else
                <a href="/dashboard"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->is('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>
                <a href="/user"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->is('user') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    User
                </a>
                <a href="/gerais"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->is('gerais*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Gerai
                </a>
                {{-- Tugas Dropdown --}}
                @php
                    $isTugasActive = request()->is('categories*') || request()->is('tugas/penjelasan-formulir-2') || request()->is('tugas/penjelasan-formulir-3');
                @endphp
                <button onclick="toggleTugas()"
                    class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium {{ $isTugasActive ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    <span class="flex-1 text-left">Tugas</span>
                    <svg id="tugasArrow" class="w-4 h-4 transition-transform {{ $isTugasActive ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div id="tugasSubmenu" class="ml-4 space-y-1 {{ $isTugasActive ? '' : 'hidden' }}">
                    <a href="/categories"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('categories*') ? 'bg-blue-50 text-blue-700' : 'text-gray-500 hover:bg-gray-50' }}">
                        Daftar Tugas
                    </a>
                    <a href="/tugas/penjelasan-formulir-2"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tugas/penjelasan-formulir-2') ? 'bg-blue-50 text-blue-700' : 'text-gray-500 hover:bg-gray-50' }}">
                        Penjelasan Formulir 2
                    </a>
                    <a href="/tugas/penjelasan-formulir-3"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tugas/penjelasan-formulir-3') ? 'bg-blue-50 text-blue-700' : 'text-gray-500 hover:bg-gray-50' }}">
                        Penjelasan Formulir 3
                    </a>
                </div>
                <hr class="border-gray-200 my-1">
                {{-- Monitoring Dropdown --}}
                @php
                    $isMonitoringActive = request()->is('report') || request()->is('ranking') || request()->is('ranking/peringkat') || request()->is('ranking/performa') || request()->is('ranking/import') || request()->is('semester-periods*');
                @endphp
                <button onclick="toggleMonitoring()"
                    class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium {{ $isMonitoringActive ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="flex-1 text-left">Monitoring</span>
                    <svg id="monitoringArrow" class="w-4 h-4 transition-transform {{ $isMonitoringActive ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div id="monitoringSubmenu" class="ml-4 space-y-1 {{ $isMonitoringActive ? '' : 'hidden' }}">
                    <a href="/report"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('report') && !request()->is('report/pre-monitoring') ? 'bg-blue-50 text-blue-700' : 'text-gray-500 hover:bg-gray-50' }}">
                        Laporan Monitoring
                    </a>
                    <a href="/ranking"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('ranking') && !request()->is('ranking/performa') ? 'bg-blue-50 text-blue-700' : 'text-gray-500 hover:bg-gray-50' }}">
                        Daftar Nilai Monitoring
                    </a>
                    <a href="/ranking/peringkat"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('ranking/peringkat') ? 'bg-blue-50 text-blue-700' : 'text-gray-500 hover:bg-gray-50' }}">
                        Peringkat Monitoring
                    </a>
                    <a href="/ranking/performa"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('ranking/performa') ? 'bg-blue-50 text-blue-700' : 'text-gray-500 hover:bg-gray-50' }}">
                        Performa Gerai
                    </a>
                    <a href="/ranking/import"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('ranking/import') ? 'bg-blue-50 text-blue-700' : 'text-gray-500 hover:bg-gray-50' }}">
                        Import Nilai
                    </a>
                    <a href="/semester-periods"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('semester-periods*') ? 'bg-blue-50 text-blue-700' : 'text-gray-500 hover:bg-gray-50' }}">
                        Periode Semester
                    </a>
                </div>
                @php
                    $isPraMonitoringActive = request()->is('report/pre-monitoring') || request()->is('ranking/pra-monitoring');
                @endphp
                <button onclick="togglePraMonitoring()"
                    class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium {{ $isPraMonitoringActive ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="flex-1 text-left">Pra Monitoring</span>
                    <svg id="praMonitoringArrow" class="w-4 h-4 transition-transform {{ $isPraMonitoringActive ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div id="praMonitoringSubmenu" class="ml-4 space-y-1 {{ $isPraMonitoringActive ? '' : 'hidden' }}">
                    <a href="/report/pre-monitoring"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('report/pre-monitoring') ? 'bg-blue-50 text-blue-700' : 'text-gray-500 hover:bg-gray-50' }}">
                        Laporan Pra-Monitoring
                    </a>
                    <a href="/ranking/pra-monitoring"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('ranking/pra-monitoring') ? 'bg-blue-50 text-blue-700' : 'text-gray-500 hover:bg-gray-50' }}">
                        Daftar Nilai Pra-Monitoring
                    </a>
                </div>
                <a href="/excel-template"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->is('excel-template') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Template Excel
                </a>
            @endif
        </nav>

        {{-- Bottom actions --}}
        @if (auth()->user()->role !== 'guest')
            <hr class="border-gray-200 mx-4">
            <div class="p-4 space-y-1">
                <a href="/monitoring"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium bg-blue-600 text-white hover:bg-blue-700">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Monitoring
                </a>
                <a href="/pra-monitoring"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium bg-gray-600 text-white hover:bg-gray-700">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Pra-Monitoring
                </a>
            </div>
        @endif

        <div class="p-4 border-t">
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-red-50 hover:text-red-600">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 flex flex-col min-h-screen">
        {{-- Navbar --}}
        <header class="bg-white shadow-sm border-b h-14 flex items-center px-4 gap-1 sm:gap-3 shrink-0">
            <button onclick="toggleSidebar()" class="text-gray-600 hover:text-gray-800 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <h1 class="text-lg font-bold text-gray-800">Monapps</h1>
            <span class="ml-auto text-sm text-gray-500">{{ auth()->user()->name }}</span>
        </header>

        {{-- Content --}}
        <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-x-auto">
            @if (session('success'))
                <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                    {!! session('error') !!}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    {{-- Custom Modal --}}
    <div id="customModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="fixed inset-0 bg-black/50" onclick="closeModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-sm w-full mx-4 p-6" onclick="closeModal()">
            <p id="modalMessage" class="text-sm text-gray-700 whitespace-pre-wrap"></p>
            <div id="modalActions" class="mt-5 flex justify-end gap-3"></div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('sidebarOverlay').classList.toggle('hidden');
        }
        function toggleTugas() {
            document.getElementById('tugasSubmenu').classList.toggle('hidden');
            document.getElementById('tugasArrow').classList.toggle('rotate-180');
        }
        function togglePraMonitoring() {
            document.getElementById('praMonitoringSubmenu').classList.toggle('hidden');
            document.getElementById('praMonitoringArrow').classList.toggle('rotate-180');
        }
        function toggleMonitoring() {
            document.getElementById('monitoringSubmenu').classList.toggle('hidden');
            document.getElementById('monitoringArrow').classList.toggle('rotate-180');
        }

        function closeModal() {
            document.getElementById('customModal').classList.add('hidden');
        }

        function showAlert(msg) {
            var modal = document.getElementById('customModal');
            document.getElementById('modalMessage').textContent = msg;
            document.getElementById('modalActions').innerHTML = '<button onclick="closeModal()" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">OK</button>';
            modal.classList.remove('hidden');
        }

        function showConfirm(msg, onConfirm) {
            var modal = document.getElementById('customModal');
            document.getElementById('modalMessage').textContent = msg;
            document.getElementById('modalActions').innerHTML =
                '<button onclick="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300">Tidak</button>' +
                '<button id="confirmBtn" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Ya</button>';
            var confirmBtn = document.getElementById('confirmBtn');
            confirmBtn.addEventListener('click', function() {
                confirmBtn.disabled = true;
                closeModal();
                if (onConfirm) onConfirm();
            });
            modal.classList.remove('hidden');
        }

        document.addEventListener('submit', function(e) {
            var form = e.target;
            form.querySelectorAll('button[type="submit"]').forEach(function(btn) {
                btn.disabled = true;
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
