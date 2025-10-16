@extends('layouts.app')

@section('content')
    <!-- Reference the custom CSS file -->
    <link href="{{ asset('assets/css/dashboard.css') }}" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Include the Chart.js datalabels plugin for label placement -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <div class="container-fluid">
        <!-- Date Range Picker -->
        <div class="row mt-4 mb-2">
            <div class="col-md-6">
                <form action="{{ route('home') }}" method="GET">
                    <table class="table table-borderless">
                        <tr>
                            <td>
                                <label for="date_range">Select:</label>
                            </td>
                            <td>
                                <div class="input-daterange input-group" id="datepicker">
                                    <input type="text" class="form-control" name="start_date" value="{{ $startDate->format('d-m-Y') }}" id="start_date" placeholder="Start Date" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">to</span>
                                    </div>
                                    <input type="text" class="form-control" name="end_date" value="{{ $endDate->format('d-m-Y') }}" id="end_date" placeholder="End Date" />
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 col-sm-6">
                <div class="card-box bg-cyan" style="border-radius: 5px;">
                    <div class="inner">
                        <h4> KES {{ number_format($metrics['totalSales']) }} </h4>
                        <p> Total Sales </p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-chart-line" aria-hidden="true"></i>
                    </div>
                    <a href="{{ route('performance') }}" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6">
                <div class="card-box bg-green" style="border-radius: 5px;">
                    <div class="inner">
                        <h4> KES {{ number_format($metrics['totalCommission']) }} </h4>
                        <p> Commission </p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-hand-holding-usd" aria-hidden="true"></i>
                    </div>
                    <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card-box bg-orange" style="border-radius: 5px;">
                    <div class="inner">
                        <h3>{{ $metrics['totalPolicies'] }}</h3>
                        <p> Policies </p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-file-alt" aria-hidden="true"></i>
                    </div>
                    <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card-box bg-red" style="border-radius: 5px;">
                    <div class="inner">
                        <h3>{{ $metrics['totalClaims'] }}</h3>
                        <p> Claims </p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                    </div>
                    <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3 col-sm-6">
                <div class="card-box bg-gold" style="border-radius: 5px;">
                    <div class="inner">
                        <h4> KES {{ number_format($metrics['totalPayments']) }} </h4>
                        <p> Paid </p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-money-bill-wave" aria-hidden="true"></i>
                    </div>
                    <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card-box bg-blue" style="border-radius: 5px;">
                    <div class="inner">
                        <h4> KES {{ number_format($metrics['balance']) }} </h4>
                        <p> Outstanding </p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-hourglass-half" aria-hidden="true"></i>
                    </div>
                    <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card-box bg-red" style="border-radius: 5px;">
                    <div class="inner">
                        <h4> KES {{ number_format($metrics['totalAllocated']) }} </h4>
                        <p> Total Allocated </p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-balance-scale" aria-hidden="true"></i>
                    </div>
                    <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card-box bg-purple" style="border-radius: 5px;">
                    <div class="inner">
                        <h4>{{ $metrics['expiredPolicies'] }}</h4>
                        <p>Unrenewed</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-wallet" aria-hidden="true"></i>
                    </div>
                    <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- New Row for Bar and Pie Charts -->
    <div class="row">
        <!-- Monthly Sales Bar Graph -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    Monthly Sales
                </div>
                <div class="card-body">
                    <canvas id="monthlySalesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Policy Distribution Pie Chart -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    Policy Distribution
                </div>
                <div class="card-body">
                    <canvas id="policyDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Monthly Sales Bar Graph
        var ctx1 = document.getElementById('monthlySalesChart').getContext('2d');
        var monthlySalesData = @json($salesData); // Pass the PHP array to JavaScript
        var monthlySalesLabels = @json($salesLabels); // Pass the PHP array of month labels to JavaScript

        var monthlySalesChart = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: monthlySalesLabels, // Use dynamic month labels
                datasets: [{
                    label: 'Sales (KES)',
                    data: monthlySalesData, // Use dynamic sales data
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Sales (KES)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                },
                plugins: {
                    datalabels: {
                        display: true,
                        color: '#000',  // Label text color
                        formatter: function(value, context) {
                            return 'KES ' + value.toLocaleString();
                        },
                        anchor: 'end',  // Anchor the labels outside
                        align: 'end',  // Align the labels outside
                        offset: 10,  // Add offset to labels for better readability
                        textAlign: 'center',
                        font: {
                            weight: 'bold',
                            size: 12
                        }
                    }
                }
            }
        });
    </script>

    <script>
        var ctx2 = document.getElementById('policyDistributionChart').getContext('2d');
        
        // Dynamic data for the pie chart
        var policyLabels = @json($policyLabels);  // Dynamic policy types
        var policyData = @json($policyCounts);  // Use raw counts instead of percentages
        
        var policyDistributionChart = new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: policyLabels,  // Dynamic policy types
                datasets: [{
                    label: ' ',
                    data: policyData,  // Use raw counts
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(153, 102, 255, 0.5)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,  // Show the legend
                        position: 'bottom'
                    },
                    datalabels: {
                        display: true,
                        color: '#000',  // Label text color
                        formatter: (value, ctx) => {
                            let label = ctx.chart.data.labels[ctx.dataIndex];  // Get the label
                            return label + ': ' + value;  // Display the raw count
                        },
                        anchor: 'end',  // Anchor the labels outside
                        align: 'end',  // Align the labels outside
                        offset: 10,  // Add offset to labels for better readability
                        textAlign: 'center',
                        font: {
                            weight: 'bold',
                            size: 12
                        }
                    }
                }
            }
        });
    </script>

    <!-- Initialize Date Range Picker -->
    <script>
        $(document).ready(function() {
            $('.input-daterange').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true
            });
        });
    </script>
@endsection
