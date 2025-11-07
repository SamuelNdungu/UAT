@extends('layouts.ui')

@push('styles')
<style>
    .chart-container {
        height: 80vh;
        min-height: 500px;
        padding: 20px;
    }
    .refresh-btn {
        background: #2c5282;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
    }
</style>
@endpush

@section('content')
<div class="p-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-brand-blue">DMVIC Stock Comparison</h1>
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-600">
                Last updated: {{ now()->format('M d, Y H:i:s') }}
            </span>
            <button onclick="window.location.reload()" class="refresh-btn">
                <i class="fas fa-sync-alt"></i>
                <span>Refresh</span>
            </button>
        </div>
    </div>

    @if(isset($error))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p class="font-bold">Error</p>
            <p>{{ $error }}</p>
        </div>
    @endif

    @if(isset($success) && $success)
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Stock Levels by Vehicle Type</h2>
            </div>
            <div class="chart-container">
                <canvas id="stockChart"></canvas>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('stockChart').getContext('2d');
    const chartData = {!! $chartData !!};
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: chartData.datasets.map(dataset => ({
                label: dataset.label,
                data: dataset.data,
                backgroundColor: dataset.backgroundColor,
                borderColor: dataset.backgroundColor,
                borderWidth: 1,
                barPercentage: 0.8,
                categoryPercentage: 0.8
            }))
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Stock Count' },
                    ticks: { precision: 0 }
                },
                x: {
                    title: { display: true, text: 'Vehicle Type' }
                }
            },
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: context => `${context.dataset.label}: ${context.raw.toLocaleString()}`
                    }
                }
            }
        }
    });

    // Auto-refresh every 5 minutes
    setTimeout(() => window.location.reload(), 300000);
});
</script>
@endpush
@endsection
