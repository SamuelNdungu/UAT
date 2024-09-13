@extends('layouts.app')

@section('content')
    <!-- Reference the custom CSS file -->
    <link href="{{ asset('assets/css/dashboard.css') }}" rel="stylesheet">
        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-sm-6">
                <div class="card-box bg-cyan" style="border-radius: 5px;">
                    <div class="inner">
                        <h3> KES 1,200,000 </h3>
                        <p> Sales </p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-chart-line" aria-hidden="true"></i>
                    </div>
                    <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6">
                <div class="card-box bg-green" style="border-radius: 5px;">
                    <div class="inner">
                        <h3> KES 300,000 </h3>
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
                        <h3> 150 </h3>
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
                        <h3> 50 </h3>
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
                        <h3> KES 500,000 </h3>
                        <p> Amount Due </p>
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
                        <h3> KES 200,000 </h3>
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
                        <h3> KES 700,000 </h3>
                        <p> Balance </p>
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
                        <h3> KES 2,000,000 </h3>
                        <p> Total </p>
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
    </div>

    <!-- JavaScript for Charts -->
    <script>
        // Monthly Sales Bar Graph
        var ctx1 = document.getElementById('monthlySalesChart').getContext('2d');
        var monthlySalesChart = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
                datasets: [{
                    label: 'Sales (KES)',
                    data: [1200000, 1500000, 1100000, 1700000, 1900000, 1300000, 1400000],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Policy Distribution Pie Chart
        var ctx2 = document.getElementById('policyDistributionChart').getContext('2d');
        var policyDistributionChart = new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: ['Life', 'Health', 'Motor', 'Home', 'Travel'],
                datasets: [{
                    label: 'Policy Distribution',
                    data: [25, 30, 20, 15, 10],
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
                responsive: true
            }
        });
    </script>
@endsection
