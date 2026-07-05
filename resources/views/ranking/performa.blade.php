@extends('layouts.admin')

@section('title', 'Performa Gerai - Monapps')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
@endpush

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Performa Gerai</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <form method="GET" action="/ranking/performa" id="formPerforma">
            <div class="relative">
                <label class="block text-sm font-medium text-gray-600 mb-1">Pilih Gerai</label>
                <input type="text" id="geraiSearch" placeholder="Ketik kode atau nama gerai..." value="{{ $geraiId ? $gerais->firstWhere('id', $geraiId)?->kode_gerai . ' - ' . $geraiNama : '' }}" autocomplete="off"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="hidden" name="gerai_id" id="geraiId" value="{{ $geraiId }}">
                <ul id="geraiSuggest" class="hidden absolute z-10 left-0 right-0 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto list-none p-0 m-0 mt-1" style="max-height:200px"></ul>
            </div>
        </form>
    </div>

    @if ($geraiId)
        @if (!empty($reportData))
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden mb-6">
                <div class="overflow-x-auto">
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
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Trend Skor {{ $geraiNama }}</h2>

            @if (empty($chartLabels))
                <p class="text-sm text-gray-400">Belum ada data monitoring untuk gerai ini.</p>
            @else
                <div class="relative" style="height: 350px;">
                    <canvas id="performaChart"></canvas>
                </div>
            @endif
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border p-6 text-sm text-gray-400">
            Pilih gerai untuk melihat grafik performa.
        </div>
    @endif
@endsection

@push('scripts')
<script>
var geraiList = [
    @foreach ($gerais as $g)
        { id: {{ $g->id }}, kode: "{{ addslashes($g->kode_gerai) }}", nama: "{{ addslashes($g->nama_gerai) }}" },
    @endforeach
];

var input = document.getElementById('geraiSearch');
var hidden = document.getElementById('geraiId');
var suggest = document.getElementById('geraiSuggest');

function renderSuggest(filter) {
    var q = filter.toLowerCase();
    var items = geraiList.filter(function(g) {
        return g.kode.toLowerCase().indexOf(q) !== -1 || g.nama.toLowerCase().indexOf(q) !== -1;
    });
    suggest.innerHTML = '';
    if (items.length === 0 || q === '') {
        suggest.classList.add('hidden');
        return;
    }
    items.forEach(function(g) {
        var li = document.createElement('li');
        li.className = 'px-3 py-2.5 cursor-pointer hover:bg-blue-50 border-b border-gray-100 text-sm';
        li.textContent = g.kode + ' - ' + g.nama;
        li.addEventListener('click', function() {
            input.value = g.kode + ' - ' + g.nama;
            hidden.value = g.id;
            suggest.classList.add('hidden');
            document.getElementById('formPerforma').submit();
        });
        suggest.appendChild(li);
    });
    suggest.classList.remove('hidden');
}

input.addEventListener('focus', function() {
    if (hidden.value) {
        this.value = '';
        hidden.value = '';
    }
    renderSuggest(this.value);
});
input.addEventListener('input', function() {
    hidden.value = '';
    renderSuggest(this.value);
});
input.addEventListener('blur', function() {
    setTimeout(function() { suggest.classList.add('hidden'); }, 200);
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
