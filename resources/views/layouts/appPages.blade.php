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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrQkTy1zXS1ORQfrI6EaD94ICFPJ5p4L28/9HdOCxUs1D14jFRUqKFl2SoTiEUbMfpNU3gN0V8A8j5Rdrg==" crossorigin="anonymous" referrerpolicy="no-referrer" />


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
            <a class="navbar-brand" href="{{ url('/home') }}">
                        {{ config('app.name', 'Bima-Connect') }}
                    </a>
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
                    <img src="https://github.com/mdo.png" alt="" width="32" height="32" class="rounded-circle me-2">                    
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

                                <a class="nav-link"  href="{{ url('/') }}">
                                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                    Dashboard</a> 
                                <a class="nav-link" href="{{ route('customers.index') }}" >
                                    <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                                    Customers</a>

                                <a class="nav-link" href="{{ route('leads.index') }}">
                                    <div class="sb-nav-link-icon"><i class="fas fa-user-tag"></i></div>
                                    Leads
                                </a>
                                <a class="nav-link" href="{{ route('policies.index')  }}">
                                    <div class="sb-nav-link-icon"><i class="fas fa-file-contract"></i></div>
                                    Policies
                                </a>
                                <a class="nav-link" href="{{ route('payments.index')  }}">     
                                    <div class="sb-nav-link-icon"><i class="fas fa-credit-card"></i></div>
                                    Payments
                                </a>
                                <a class="nav-link" href="{{ route('collection.index')  }}">                                
                                    <div class="sb-nav-link-icon"><i class="fas fa-credit-card"></i></div>
                                    Collection
                                </a>
                                <a class="nav-link" href="{{ route('renewals.index')  }}">
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

                    <!---->

                    <div class="sb-sidenav-footer">
                        <div class="small">Power by:</div>
                        Bima Connect
                    </div>



                    <!---->
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        {{ Auth::user()->name }}
                    </div>
                </nav>
            </div>
          
            <main class="full-width px-4 ">
                @yield('content')
            </main>
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
            document.getElementById('wrapper').classList.toggle('toggled');
        });
    </script>
</body>
</html>
