<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Bima-Connect') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrQkTy1zXS1ORQfrI6EaD94ICFPJ5p4L28/9HdOCxUs1D14jFRUqKFl2SoTiEUbMfpNU3gN0V8A8j5Rdrg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">

    <!-- DataTables Buttons CSS (for export buttons) -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap5.min.css">

    <!-- Custom CSS -->
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/dashboard.css') }}" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

    <!-- Top Navigation Bar -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        @php
            $__companyName = null;
            try {
                if (class_exists('\App\\Models\\CompanyData')) {
                    $__c = \App\Models\CompanyData::first();
                    if ($__c && !empty($__c->company_name)) $__companyName = $__c->company_name;
                }
            } catch (\Throwable $__e) { $__companyName = null; }
        @endphp
        <a class="navbar-brand" href="{{ url('/home') }}">{{ $__companyName ?? config('app.name', 'Bima-Connect') }}</a>
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        
        <!-- Navbar-->
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                    <img src="https://github.com/mdo.png" alt="" width="32" height="32" class="rounded-circle me-2">                    
                    {{ Auth::user()->name }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="#">Settings</a></li>
                    <li><a class="dropdown-item" href="#">Activity Log</a></li>
                    <li><hr class="dropdown-divider" /></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">  
                            <a class="nav-link" href="{{ url('/') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Dashboard
                            </a>
                            <a class="nav-link" href="{{ route('customers.index') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                                Customers
                            </a>
                            <a class="nav-link" href="{{ route('leads.index') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-user-tag"></i></div>
                                Leads
                            </a>
                            <a class="nav-link" href="{{ route('policies.index') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-file-contract"></i></div>
                                Policies
                            </a>
                            <a class="nav-link" href="{{ route('payments.index') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-credit-card"></i></div>
                                Payments
                            </a>
                            <a class="nav-link" href="{{ route('collection.index') }}">                                
                                <div class="sb-nav-link-icon"><i class="fas fa-credit-card"></i></div>
                                Collection
                            </a>
                            <a class="nav-link" href="{{ route('renewals.index') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-calendar-check"></i></div>
                                Renewals
                            </a>
                            <a class="nav-link" href="{{ route('claims.index') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                                Claims
                            </a>
                            <a class="nav-link" href="{{ url('/reports') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                                Reports
                            </a>
                        </div>
                    </div>

                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        {{ Auth::user()->name }}
                    </div>
                </nav>
            </div>
          
            <main class="container-fluid px-4">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS and Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <!-- Custom JS -->
    <script src="{{ asset('js/scripts.js') }}"></script>

    <!-- Sidebar Toggle Script -->
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('wrapper').classList.toggle('toggled');
        });
    </script>
</body>
</html>
