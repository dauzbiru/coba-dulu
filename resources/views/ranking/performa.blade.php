@extends('layouts.admin')

@section('title', 'Performa Gerai - MARS')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
@endpush

@section('content')
    <div class="bg-white rounded-xl shadow-sm border">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center gap-3 relative">
            <h1 class="text-lg font-bold text-gray-800">Performa Gerai</h1>
            <div class="ml-auto relative flex items-center shrink-0" style="min-width: 40px;">
                <input type="text" id="geraiSearch" placeholder="Cari kode/nama gerai..."
                    class="w-0 h-[38px] px-0 py-2 border border-transparent text-sm focus:outline-none transition-all duration-200 ease-in-out"
                    autocomplete="off" oninput="onSearchInput(this.value)">
                <button type="button" onclick="toggleSearch('geraiSearch', this)" class="shrink-0 p-2 text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
                <ul id="geraiSuggest" class="hidden absolute z-10 left-0 right-0 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto list-none p-0 m-0 mt-1" style="top:100%; max-height:200px"></ul>
            </div>
        </div>

        <div class="p-5 overflow-hidden">
            @if ($geraiId)
                @if (!empty($reportData))
                    <div class="overflow-x-auto mb-6">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-left text-gray-500">
                                    <th class="px-5 py-3 font-medium">Tanggal</th>
                                    @foreach ($reportData as $rd)
                                        <th class="px-5 py-3 font-medium text-center">{{ $rd['tanggal'] }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-t">
                                    <td class="px-5 py-3 font-medium text-gray-600">Nilai</td>
                                    @foreach ($reportData as $rd)
                                        <td class="px-5 py-3 text-center font-semibold text-blue-600">{{ $rd['skor'] }}</td>
                                    @endforeach
                                </tr>
                                <tr class="border-t">
                                    <td class="px-5 py-3 font-medium text-gray-600">Grade</td>
                                    @foreach ($reportData as $rd)
                                        @php $grd = \App\Models\MonitoringReport::gradeFromScore((float) $rd['skor']); @endphp
                                        <td class="px-5 py-3 text-center font-semibold {{ $grd === 'A' ? 'text-green-600' : ($grd === 'B' ? 'text-blue-600' : ($grd === 'C' ? 'text-yellow-600' : ($grd === 'D' ? 'text-orange-500' : 'text-red-600'))) }}">{{ $grd }}</td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif

                <div>
                    <h2 class="text-base font-semibold text-gray-800 mb-4">Trend Skor {{ $geraiNama }}</h2>
                    @if (empty($chartLabels))
                        <p class="text-sm text-gray-400">Belum ada data monitoring untuk gerai ini.</p>
                    @else
                        <div class="relative" style="height: 350px;">
                            <canvas id="performaChart"></canvas>
                        </div>
                    @endif
                </div>
            @else
                <p class="text-sm text-gray-400">Pilih gerai untuk melihat grafik performa.</p>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
var geraiList = [
    @foreach ($gerais as $g)
        { id: {{ $g->id }}, kode: @json($g->kode_gerai), nama: @json($g->nama_gerai) },
    @endforeach
];

var input = document.getElementById('geraiSearch');
var suggest = document.getElementById('geraiSuggest');

function renderSuggest(filter) {
    var q = filter.toLowerCase();
    var items = geraiList.filter(function(g) {
        return g.kode.toLowerCase().indexOf(q) !== -1 || g.nama.toLowerCase().indexOf(q) !== -1;
    });
    suggest.innerHTML = '';
    if (items.length === 0) {
        suggest.classList.add('hidden');
        return;
    }
    items.forEach(function(g) {
        var li = document.createElement('li');
        li.className = 'px-3 py-2.5 cursor-pointer hover:bg-blue-50 border-b border-gray-100 text-sm';
        li.textContent = g.kode + ' - ' + g.nama;
        li.addEventListener('click', function() {
            input.value = g.kode + ' - ' + g.nama;
            suggest.classList.add('hidden');
            window.location.href = '/ranking/performa?gerai_id=' + g.id;
        });
        suggest.appendChild(li);
    });
    suggest.classList.remove('hidden');
}

var expandTimer;
function onSearchInput(val) {
    clearTimeout(expandTimer);
    expandTimer = setTimeout(function() { renderSuggest(val); }, 150);
}

input.addEventListener('blur', function() {
    setTimeout(function() {
        suggest.classList.add('hidden');
        if (!input.value) {
            input.classList.add('w-0', 'px-0', 'border-0');
            input.classList.remove('w-48', 'sm:w-64', 'px-3', 'border', 'border-gray-300', 'rounded-lg');
            var btn = input.parentElement.querySelector('button');
            if (btn) btn.classList.remove('hidden');
        }
    }, 200);
});
document.addEventListener('click', function(e) {
    if (!e.target.closest('.relative')) suggest.classList.add('hidden');
});
</script>
    @if ($geraiId && !empty($chartLabels))
        <script>
            const ctx = document.getElementById('performaChart').getContext('2d');
            Chart.register(ChartDataLabels);
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Skor',
                        data: @json($chartData),
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#2563eb',
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        fill: true,
                        tension: 0.3,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'bottom',
                            color: '#2563eb',
                            font: { weight: 'bold', size: 11 },
                            formatter: (value) => value,
                        }
                    },
                    scales: {
                        y: {
                            min: 700,
                            max: 1000,
                            grid: {
                                color: 'rgba(0,0,0,0.05)',
                            },
                            ticks: {
                                font: { size: 12 },
                            },
                            afterBuildTicks: function(axis) { axis.ticks = [700, 800, 900, 1000].map(v => ({ value: v })); }
                        },
                        x: {
                            grid: {
                                display: false,
                            },
                            ticks: {
                                font: { size: 12 },
                            }
                        }
                    }
                }
            });
        </script>
    @endif
@endpush
