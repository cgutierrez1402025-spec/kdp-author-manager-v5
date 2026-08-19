<div class="space-y-4">
    <div class="grid grid-cols-3 gap-4">
        <div class="p-4 bg-white rounded-lg shadow">
            <h3 class="text-sm font-medium text-gray-500">Costos Totales</h3>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($totalCosts, 2) }} €</p>
        </div>

        <div class="p-4 bg-white rounded-lg shadow">
            <h3 class="text-sm font-medium text-gray-500">Ingresos Generados</h3>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($totalRevenue, 2) }} €</p>
        </div>

        <div class="p-4 bg-white rounded-lg shadow">
            <h3 class="text-sm font-medium text-gray-500">ROI</h3>
            <p class="text-2xl font-bold {{ $roi >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ number_format($roi, 2) }} € ({{ $roiPercentage }}%)
            </p>
        </div>
    </div>

    @if(!empty($chartData))
    <div class="p-4 bg-white rounded-lg shadow">
        <h3 class="text-sm font-medium text-gray-500 mb-3">Evolución Diaria (Royalties)</h3>
        <div class="h-64">
            <canvas id="revenue-chart" data-chart="{{ json_encode($chartData) }}"></canvas>
        </div>
    </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = @json($chartData);
    const ctx = document.getElementById('revenue-chart');

    if (ctx && chartData.length > 0) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(d => d.date),
                datasets: [{
                    label: 'Royalties Netas',
                    data: chartData.map(d => d.net_royalties),
                    borderColor: '#3b82f6',
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' €';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>