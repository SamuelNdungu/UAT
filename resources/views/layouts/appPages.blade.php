<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>{{ config('app.name', 'Emely Insurance') }}</title>
    <!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" xintegrity="sha512-Fo3rlrQkTy1zXS1ORQfrI6EaD94ICFPJ5p4L28/9HdOCxUs1D14jFRUqKFl2SoTiEUbMfpNU3gN0V8A8j5Rdrg==" crossorigin="anonymous" referrerpolicy="no-referrer" />


<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">

<!-- DataTables Buttons CSS (for export buttons) -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap5.min.css">


    <!-- Fonts and Bootstrap -->
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
    <link rel="canonical" href="https://getbootstrap.com/docs/5.0/examples/sidebars/">

    <!-- Bootstrap core CSS -->
    <link href="../assets/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <!-- Reference the custom CSS file -->
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
            <a class="navbar-brand" href="{{ url('/home') }}">{{ $__companyName ?? config('app.name', 'Emely Insurance') }}</a>
            <!-- Sidebar Toggle-->
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <!-- Navbar Search-->
            <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                <div class="input-group">
                     
                </div>
            </form>
            <!-- Navbar-->
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li class="nav-item dropdown">
                    
                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                    <img src="{{ asset('img/profile.png') }}" alt="Profile Picture" width="35" height="35" class="rounded-circle me-2 border-white shadow" style="border-width: 2px; border-color: white; border-style: solid;">
   
                    {{ Auth::user()->name }}</a>
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
                <a class="nav-link" href="{{ route('home') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div>
                    Dashboard
                </a>

                <!-- AI Assistant (moved here for prominence) -->
                <a class="nav-link" href="{{ route('ai.index') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-robot"></i></div>
                    AI Assistant
                </a>
                <a class="nav-link" href="{{ route('customers.index') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                    Customers
                </a>
                <a class="nav-link" href="{{ route('leads.index') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-funnel-dollar"></i></div>
                    Leads
                </a> 
                <a class="nav-link" href="{{ route('policies.index') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-file-signature"></i></div>
                    Policies
                </a>
                <a class="nav-link" href="{{ route('payments.index') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-money-bill-wave"></i></div>
                    Payments
                </a>
                <a class="nav-link" href="{{ route('collection.index') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-hand-holding-usd"></i></div>
                    Collection
                </a>
                <a class="nav-link" href="{{ route('renewals.index') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-calendar-alt"></i></div>
                    Renewals
                </a>
                <a class="nav-link" href="{{ route('claims.index') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-clipboard-list"></i></div>
                    Claims
                </a>
                <a class="nav-link" href="{{ route('fees.index') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-file-invoice"></i></div>
                    Fees
                </a>
                
                <!-- START REPORTING MODULE MENU ITEM -->
                <li class="nav-item">
                    <!-- Main Reports Link/Dropdown Trigger -->
                    <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" 
                       data-bs-toggle="collapse" 
                       href="#reportsMenu" 
                       role="button" 
                       aria-expanded="{{ request()->routeIs('reports.*') ? 'true' : 'false' }}" 
                       aria-controls="reportsMenu">
                        <i class="fas fa-chart-bar fa-fw"></i> 
                        <span class="ms-2">Reports</span>
                        <i class="fas fa-angle-down ms-auto"></i>
                    </a>

                    <!-- Collapsible Sub-Menu -->
                    <div class="collapse {{ request()->routeIs('reports.*') ? 'show' : '' }}" id="reportsMenu">
                        <ul class="nav flex-column ps-3">
                            
                            <!-- Financial Reports -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('reports.sales') ? 'active bg-light text-primary' : '' }}" 
                                   href="{{ route('reports.sales') }}">
                                    <i class="fas fa-cash-register fa-fw"></i> Sales & Production
                                </a>
                            </li>

                            <li class="nav-item">
                                <!-- Placeholder Route -->
                                <a class="nav-link {{ request()->routeIs('reports.debt_aging') ? 'active bg-light text-primary' : '' }}" 
                                   href="{{ route('reports.debt_aging') }}">
                                    <i class="fas fa-clock fa-fw"></i> Debt Aging
                                </a>
                            </li>

                            <!-- Operational Reports -->
                            <li class="nav-item">
                                <!-- Placeholder Route -->
                                <a class="nav-link {{ request()->routeIs('reports.claims') ? 'active bg-light text-primary' : '' }}" 
                                   href="{{ route('reports.claims') }}">
                                    <i class="fas fa-hand-holding-medical fa-fw"></i> Claims Analysis
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <!-- Placeholder Route -->
                                <a class="nav-link {{ request()->routeIs('reports.renewals') ? 'active bg-light text-primary' : '' }}" 
                                   href="{{ route('reports.renewals') }}">
                                    <i class="fas fa-redo fa-fw"></i> Renewals Tracking
                                </a>
                            </li>

                            <li class="nav-item">
                                <!-- Placeholder Route -->
                                <a class="nav-link {{ request()->routeIs('reports.commissions') ? 'active bg-light text-primary' : '' }}" 
                                   href="{{ route('reports.commissions') }}">
                                    <i class="fas fa-money-check-alt fa-fw"></i> Commission Payable
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>
                <!-- END REPORTING MODULE MENU ITEM -->
                
                <!-- AI Assistant link moved to the top of the sidebar -->
                    <a class="nav-link" href="{{ route('settings.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-cog"></i></div>
                        Settings
                    </a>
            </div>
        </div>

                    <!---->

                    <div class="sb-sidenav-footer">
                        <div class="small">Power by:</div>
                        Bima Connect
                    </div> 
                </nav>
            </div>
          
            <div id="layoutSidenav_content">
            <main class="full-width px-4 ">
                @yield('content')
            </main>
            </div>
        </div>


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
    var sidenav = document.getElementById('layoutSidenav_nav');
    var mainContent = document.querySelector('main');
    
    sidenav.classList.toggle('sidebar-collapsed');
    sidenav.classList.toggle('sidebar-expanded');
    
    // Adjust main content width when sidebar is collapsed or expanded
    if (sidenav.classList.contains('sidebar-collapsed')) {
        mainContent.style.marginLeft = '80px'; /* Reduced margin when collapsed */
    } else {
        mainContent.style.marginLeft = '250px'; /* Full margin when expanded */
    }
});


</script>

</body>
</html>
