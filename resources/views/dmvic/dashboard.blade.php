@extends('layouts.ui')

@section('page_title', 'DMVIC')


@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-4">
        <!-- Card 1 -->
        <div class="flex items-center bg-white rounded-lg border border-gray-200 p-4 transition-transform duration-200 hover:scale-105 hover:shadow-sm">
            <div class="p-4 rounded-xl bg-gradient-to-br from-[#A1D6FC] to-[#5886E9]">
                <i class="fas fa-shopping-cart text-white text-3xl"></i>
            </div>
            <div class="ml-4 text-gray-800">
                <h3 class="text-sm font-semibold uppercase text-gray-500">Card 1</h3>
                <p class="text-base font-bold">0</p>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="flex items-center bg-white rounded-lg border border-gray-200  p-4 transition-transform duration-200 hover:scale-105 hover:shadow-sm">
            <div class="p-4 rounded-xl bg-gradient-to-br from-[#37CDC1] to-[#0F3B99]">
                <i class="fas fa-percentage text-white text-3xl"></i>
            </div>
            <div class="ml-4 text-gray-800">
                <h3 class="text-sm font-semibold uppercase text-gray-500">Card 2</h3>
                <p class="text-base font-bold">0</p>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="flex items-center bg-white rounded-lg border border-gray-200  p-4 transition-transform duration-200 hover:scale-105 hover:shadow-sm">
            <div class="p-4 rounded-xl bg-gradient-to-br from-[#51CD37] to-[#37CDC1]">
                <i class="fas fa-file-contract text-white text-3xl"></i>
            </div>
            <div class="ml-4 text-gray-800">
                <h3 class="text-sm font-semibold uppercase text-gray-500">Card 3</h3>
                <p class="text-base font-bold">0</p>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="flex items-center bg-white rounded-lg border border-gray-200  p-4 transition-transform duration-200 hover:scale-105 hover:shadow-sm">
            <div class="p-4 rounded-xl bg-gradient-to-br from-[#FF5005] to-[#FF9705]">
                <i class="fas fa-file-invoice-dollar text-white text-3xl"></i>
            </div>
            <div class="ml-4 text-gray-800">
                <h3 class="text-sm font-semibold uppercase text-gray-500">Card 4</h3>
                <p class="text-base font-bold">0</p>
            </div>
        </div>
    </div>

    <!-- Header with Actions -->
    <div class="flex justify-between items-center mb-6">
        <!-- Action Buttons -->
        <div class="flex space-x-2">
            <a href="#" class="bg-brand-blue hover:bg-blue-600 text-white px-4 py-2 rounded-md flex items-center space-x-2">
                <i class="fas fa-file-certificate"></i>
                <span>Issue Cert</span>
            </a>
            <a href="#" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md flex items-center space-x-2">
                <i class="fas fa-search"></i>
                <span>View Cert</span>
            </a>
            <a href="#" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md flex items-center space-x-2">
                <i class="fas fa-search-plus"></i>
                <span>Search Cert</span>
            </a>
            <a href="{{ route('dmvic.double-issuance') }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md flex items-center space-x-2">
                <i class="fas fa-check-double"></i>
                <span>Double Validate</span>
            </a>
        </div>
        
        <!-- Last Updated & Refresh -->
        <div class="flex items-center space-x-4">
            <div class="text-sm text-gray-500">
                Last updated: {{ now('Africa/Nairobi')->format('M d, Y H:i:s') }}
            </div>
            <button id="refreshBtn" class="bg-brand-blue hover:bg-blue-600 text-white px-4 py-2 rounded-md flex items-center space-x-2">
                <i class="fas fa-sync-alt"></i>
                <span>Refresh</span>
            </button>
        </div>
    </div>

    @if(isset($stocks) && count($stocks) > 0)
        <!-- Plain Heading >
        <h2 class="text-xl font-semibold text-gray-800 mb-4 text-center">Stock Comparison by Class</h2-->

        <!-- Chart -->
        <div class="chart-container rounded-lg border border-gray-200" style="width:100%; max-width:900px; ">
            <canvas id="stockChart" width="400" height="400" style="background: white; border-radius:8px; padding:8px;"></canvas>
        </div>

        <!-- Inline Key -->
        <!-- div class="summary-key">
            @foreach($byCompany as $companyName => $companyData)
                <div class="summary-key-item">
                    <div class="summary-key-circle" style="background-color: {{ $companyData['color'] }}"></div>
                    {{ $companyName }}: {{ number_format($companyData['total']) }}
                </div>
            @endforeach
        </div -->
    @else
        <div class="bg-red-200 rounded-lg shadow p-8 text-center">
            <h3 class="mt-2 text-lg font-medium text-gray-900">No stock data available</h3>
        </div>
    @endif

    <!-- Motor Policies Table -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Motor Policies</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Policy No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cover Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cert No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">COVER PERIOD</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DMVIC Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse(($policies ?? []) as $p)
                    @php
                        $dmvicCert = $p->certificate_no;
                        $hasCert = $dmvicCert !== 'N/A';
                        $coverage = $p->coverage;
                        if (strtolower($coverage) == 'comprehensive') {
                            $coverage = 'COMP';
                        }
                        
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $hasCert ? 'bg-blue-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"> {{ $p->fileno }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"> {{ $p->customer_name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $p->reg_no }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $p->policy_type_name }} - {{ $coverage }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $p->certificate_no }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $p->start_date }} - {{ $p->expiry_date }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($hasCert)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Issued
                                </span>
                                <div class="text-xs text-gray-500">{{ $p->created_at }}</div>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($hasCert)
                                <a href="{{ route('dmvic.view', $p->id) }}" class="inline-flex text-white bg-green-600 hover:bg-green-900 mr-4">
                                    <i class="fas fa-eye"></i><!--span class="text-xs">View</span-->
                                </a>
                                <a href="{{ route('dmvic.download', $p->id) }}" class="inline-flex text-white bg-blue-600 hover:bg-blue-900 mr-4"><i class="fas fa-download"></i><!--span class="text-xs">Download</span--></a>
                                <a href="{{ route('dmvic.cancel', $p->id) }}" class="inline-flex text-white bg-red-600 hover:bg-red-900">
                                    <i class="fas fa-ban"></i><!--span class="text-xs">Cancel</span-->
                                </a>
                            @else
                            <a class="inline-flex items-center px-3 py-1.5 text-sm rounded bg-brand-blue text-white hover:bg-brand-blue/90" href="{{ route('dmvic.certificates.issue.form', $p->id) }}">
                                    <i class="fas fa-certificate mr-2"></i> Issue
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                            No motor policies found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination would go here -->
        <div class="px-6 py-3 bg-gray-50 text-right text-sm">
            <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <a href="#" class="relative inline-flex items-center px-4 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Previous
                </a>
                <a href="#" aria-current="page" class="z-10 bg-brand-blue border-brand-blue text-white relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                    1
                </a>
                <a href="#" class="bg-white border-gray-300 text-gray-700 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                    2
                </a>
                <a href="#" class="bg-white border-gray-300 text-gray-700 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                    3
                </a>
                <a href="#" class="relative inline-flex items-center px-4 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Next
                </a>
            </nav>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Refresh button
    document.getElementById('refreshBtn')?.addEventListener('click', () => window.location.reload());

    @if(isset($stocks) && count($stocks) > 0)
        const ctx = document.getElementById('stockChart').getContext('2d');
        const stocksData = @json($stocks);
        const byCompany = @json($byCompany);

        const companies = Object.keys(byCompany);

        // Build list of classes but exclude those where ALL companies have 0
        let allClasses = [...new Set(stocksData.map(s => s.ClassificationTitle))];
        const classes = allClasses.filter(cls => {
            return companies.some(companyName => {
                const totalForCompany = stocksData
                    .filter(s => s.CompanyName === companyName && s.ClassificationTitle === cls)
                    .reduce((sum, s) => sum + s.Stock, 0);
                return totalForCompany > 0;
            });
        });

        // Gradients
        const gradientAPA = ctx.createLinearGradient(0, 0, 0, 400);
        gradientAPA.addColorStop(0, '#60a5fa'); // light sapphire blue
        gradientAPA.addColorStop(0.5, '#2563eb'); // vivid royal blue
        gradientAPA.addColorStop(1, '#1e3a8a'); // deep navy


        const gradientFirst = ctx.createLinearGradient(0, 0, 0, 400);
        gradientFirst.addColorStop(0, '#f87171'); // ruby red (bright)  
        gradientFirst.addColorStop(0.5, '#dc2626'); // crimson (classic red)
        gradientFirst.addColorStop(1, '#7f1d1d'); // deep wine (luxury finish)
        // Datasets (company → stock by class)
        const datasets = companies.map(companyName => {
            const companyData = byCompany[companyName];

            let fillColor = companyData.color; // fallback from PHP
            if (companyName.includes("APA")) {
                fillColor = gradientAPA;
            } else if (companyName.includes("First")) {
                fillColor = gradientFirst;
            }

            return {
                label: companyName,
                data: classes.map(cls => {
                    const companyStocks = stocksData.filter(s =>
                        s.CompanyName === companyName && s.ClassificationTitle === cls
                    );
                    return companyStocks.reduce((sum, s) => sum + s.Stock, 0);
                }),
                backgroundColor: fillColor,
                borderRadius: 6,   // smooth bar corners
                barPercentage: 0.4, // 👈 thinner bars
                categoryPercentage: 0.6
            };
        });

        // Chart init
        new Chart(ctx, {
            type: 'bar',
            data: { labels: classes, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { 
                        title: { display: true, text: 'Vehicle Class' },
                        grid: {display: false}
                    },
                    y: { 
                        title: { display: true, text: 'Certificates' }, 
                        beginAtZero: true,
                        ticks: { stepSize: 20 }
                    }
                },
                plugins: {
                    legend: { display: true },
                    tooltip: {
                        callbacks: {
                            label: context => `${context.dataset.label}: ${context.parsed.y} certs`
                        }
                    }
                }
            }
        });
    @endif

    // Auto-refresh every 5 mins
    setTimeout(() => window.location.reload(), 5 * 60 * 1000);
});


</script>

@endsection
