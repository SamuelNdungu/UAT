<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        // Prefer company name from CompanyData for page title and alt text
        $companyName = null;
        try {
            if (class_exists('\App\\Models\\CompanyData')) {
                $__c = \App\Models\CompanyData::first();
                if ($__c && !empty($__c->company_name)) {
                    $companyName = $__c->company_name;
                }
            }
        } catch (\Throwable $__e) {
            $companyName = null;
        }
    @endphp
    <title>{{ $companyName ?? config('app.name', 'Emely Insurance') }}</title>

    <!-- Tailwind & Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            blue: '#253E8B',   // primary
                            blueDark: '#1E3A8A',
                            red: '#E53935',    // accent
                            grayLight: '#F3F4F6',
                            grayDark: '#374151',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-white text-brand-grayDark font-sans">

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-white p-6 flex flex-col justify-between fixed top-0 left-0 h-full border-r border-gray-200 overflow-y-auto">
        <div>
            <!-- Logo -->
             <!-- Navbar Brand: prefer company logo from CompanyData (public storage) then fall back to common public paths -->
            @php
                // Try company logo from CompanyData first (uploaded via settings, stored on 'public' disk)
                $logoUrl = null;
                try {
                    $company = null;
                    if (class_exists('\App\\Models\\CompanyData')) {
                        $company = \App\Models\CompanyData::first();
                    }

                    if ($company && !empty($company->logo_path) && \Storage::disk('public')->exists($company->logo_path)) {
                        // asset('storage/...') expects the public/storage symlink
                        $logoUrl = asset('storage/' . ltrim($company->logo_path, '/'));
                    }
                } catch (\Throwable $e) {
                    // ignore and fall back to file checks below
                    $logoUrl = null;
                }

                if (! $logoUrl) {
                    // check a few common public paths for a company logo
                    $possibleLogos = [
                        'storage/company/logo.png',
                        'storage/company/logo.jpg',
                        'assets/img/company-logo.png',
                        'assets/img/company-logo.jpg',
                        'assets/img/logo.png',
                        'assets/img/logo.jpg',
                        'img/logo.png',
                        'img/company-logo.png',
                    ];
                    foreach ($possibleLogos as $p) {
                        if (file_exists(public_path($p))) {
                            $logoUrl = asset($p);
                            break;
                        }
                    }
                }
            @endphp
            <div class="flex items-center mb-10">
                <a href="{{ route('home') }}">@if(!empty($logoUrl))
                    <img src="{{ $logoUrl }}" alt="{{ $companyName ?? config('app.name', 'Emely Insurance') }}" style="height:36px; max-height:42px; object-fit:contain;" />
                @else
                    @php
                        // Prefer company name from CompanyData when available
                        $companyNameFallback = null;
                        try {
                            if (class_exists('\App\\Models\\CompanyData')) {
                                $c = \App\Models\CompanyData::first();
                                if ($c && !empty($c->company_name)) {
                                    $companyNameFallback = $c->company_name;
                                }
                            }
                        } catch (\Throwable $ex) {
                            $companyNameFallback = null;
                        }
                    @endphp
                    {{ $companyNameFallback ?? config('app.name', 'Emely Insurance') }}
                @endif</a>
            </div>

            <!-- Nav -->
            <nav>
                <ul>
                    <li class="mb-1">
                        <a class="flex items-center p-2 rounded-lg font-medium 
                            {{ request()->routeIs('home') 
                                ? 'bg-brand-blue text-white' 
                                : 'hover:bg-brand-blue hover:text-white' }}" 
                            href="{{ route('home') }}">
                            <i class="fas fa-tachometer-alt w-5 text-center mr-3"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="mb-1">
                        <a class="flex items-center p-2 rounded-lg font-medium {{ request()->routeIs('ai.*') ? 'bg-brand-blue text-white' 
                                : 'hover:bg-brand-blue hover:text-white' }}" 
                            href="{{ route('ai.index') }}">
                            <i class="fas fa-robot w-5 text-center mr-3"></i>
                            AI Assistant
                        </a>
                    </li>
                    <li class="mb-1">
                        <a class="flex items-center p-2 rounded-lg font-medium 
                            {{ request()->routeIs('customers.*') 
                                ? 'bg-brand-blue text-white' 
                                : 'hover:bg-brand-blue hover:text-white' }}" 
                            href="{{ route('customers.index') }}">
                            <i class="fas fa-users w-5 text-center mr-3"></i>
                            Customers
                        </a>
                    </li>
                    <li class="mb-1">
                        <a class="flex items-center p-2 rounded-lg  font-medium {{ request()->routeIs('leads.*') ? 'bg-brand-blue text-white' 
                                : 'hover:bg-brand-blue hover:text-white' }}" 
                            href="{{ route('leads.index') }}">
                            <i class="fas fa-bullseye w-5 text-center mr-3"></i>
                            Leads
                        </a>
                    </li>
                    <li class="mb-1">
                        <a class="flex items-center p-2 rounded-lg  font-medium {{ request()->routeIs('policies.*') ? 'bg-brand-blue text-white' 
                                : 'hover:bg-brand-blue hover:text-white' }}" 
                            href="{{ route('policies.index') }}">
                            <i class="fas fa-file-contract w-5 text-center mr-3"></i>
                            Policies
                        </a>
                    </li>
                    <li class="mb-1">
                        <a class="flex items-center p-2 rounded-lg  font-medium {{ request()->routeIs('payments.*') ? 'bg-brand-blue text-white' 
                                : 'hover:bg-brand-blue hover:text-white' }}" 
                            href="{{ route('payments.index') }}">
                            <i class="fas fa-credit-card w-5 text-center mr-3"></i>
                            Payments
                        </a>
                    </li>
                    
                    <li class="mb-1">
                        <a class="flex items-center p-2 rounded-lg  font-medium {{ request()->routeIs('collection.*') ? 'bg-brand-blue text-white' 
                                : 'hover:bg-brand-blue hover:text-white' }}" 
                            href="{{ route('collection.index') }}">
                            <i class="fas fa-hand-holding-usd w-5 text-center mr-3"></i>
                            Collection
                        </a>
                    </li>
                    <li class="mb-1">
                        <a class="flex items-center p-2 rounded-lg  font-medium {{ request()->routeIs('renewals.*') ? 'bg-brand-blue text-white' 
                                : 'hover:bg-brand-blue hover:text-white' }}" 
                            href="{{ route('renewals.index') }}">
                            <i class="fas fa-sync-alt w-5 text-center mr-3"></i>
                            Renewals
                        </a>
                    </li>
                    <li class="mb-1">
                        <a class="flex items-center p-2 rounded-lg  font-medium {{ request()->routeIs('claims.*') ? 'bg-brand-blue text-white' 
                                : 'hover:bg-brand-blue hover:text-white' }}" 
                            href="{{ route('claims.index') }}">
                            <i class="fas fa-file-invoice-dollar w-5 text-center mr-3"></i>
                            Claims
                        </a>
                    </li>
                    <li class="mb-1">
                        <a class="flex items-center p-2 rounded-lg font-medium {{ request()->routeIs('dmvic.*') ? 'bg-brand-blue text-white' 
                                : 'hover:bg-brand-blue hover:text-white' }}" 
                            href="{{ route('dmvic.dashboard') }}">
                            <i class="fas fa-car-crash w-5 text-center mr-3"></i>
                            DMVIC
                        </a>
                    </li>
                    <li class="mb-1">
                        <a class="flex items-center p-2 rounded-lg  font-medium {{ request()->routeIs('fees.*') ? 'bg-brand-blue text-white' 
                                : 'hover:bg-brand-blue hover:text-white' }}" 
                            href="{{ route('fees.index') }}">
                            <i class="fas fa-money-bill-wave w-5 text-center mr-3"></i>
                            Fees
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Footer Links -->
        <div>
            <ul>
                <li class="mb-1">
                    <a class="flex items-center p-2 rounded-lg font-medium 
                        {{ request()->routeIs('settings.*') 
                            ? 'bg-brand-blue text-white' 
                            : 'hover:bg-brand-blue hover:text-white' }}" 
                        href="{{ route('settings.index') }}">
                        <i class="fas fa-cog w-5 text-center mr-3"></i>
                        Settings
                    </a>
                </li>
                <li>
                    @auth
                    <a class="flex items-center p-2 rounded-lg font-medium hover:bg-red-500 hover:text-white"
                       href="{{ route('logout') }}" 
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt w-5 text-center mr-3"></i>
                        Log Out
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                    @else
                    <a class="flex items-center p-2 rounded-lg font-medium hover:bg-brand-blue hover:text-white" 
                       href="{{ route('login') }}">
                        <i class="fas fa-sign-in-alt w-5 text-center mr-3"></i>
                        Login
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                    @endauth
                </li>
            </ul>
        </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 ml-64">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center sticky top-0 z-40 shadow-sm">
            <h2 class="text-2xl font-bold text-brand-blue">@yield('page_title', 'Dashboard')</h2>
            <div class="flex items-center space-x-6">
                <div class="relative">
                    <button id="profileMenuButton" type="button" aria-haspopup="true" aria-expanded="false"
                            class="flex items-center cursor-pointer focus:outline-none" >
                        <img class="w-9 h-9 rounded-full mr-3" src="{{ asset('img/profile.png') }}" alt="Profile Picture" />
                        <div class="flex items-center">
                            <span class="text-gray-700 font-medium mr-1">{{ auth()->user()->name ?? 'User' }}</span>
                            <i class="fas fa-chevron-down chev text-gray-500 text-xs transform transition-transform duration-150"></i>
                        </div>
                    </button>

                    <div id="profileMenu"
                         class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg py-1 z-50 hidden origin-top-right ring-1 ring-black ring-opacity-5 transition ease-out duration-150 transform opacity-0 scale-95"
                         role="menu" aria-orientation="vertical" aria-labelledby="profileMenuButton">
                        <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="0">
                            <i class="fas fa-user-cog mr-3 text-gray-500 w-5"></i> Settings
                        </a>
                        <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="0">
                            <i class="fas fa-history mr-3 text-gray-500 w-5"></i> Activity Log
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <a href="{{ route('logout') }}" class="flex items-center px-4 py-2 text-sm text-brand-red hover:bg-gray-100" role="menuitem" tabindex="0"
                           onclick="event.preventDefault(); document.getElementById('logout-form-top').submit();">
                            <i class="fas fa-sign-out-alt mr-3 w-5"></i> Logout
                        </a>
                        <form id="logout-form-top" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                    </div>

                    <script>
                        (function () {
                            const btn = document.getElementById('profileMenuButton');
                            const menu = document.getElementById('profileMenu');
                            const chev = btn.querySelector('.chev');

                            if (!btn || !menu) return;

                            function openMenu() {
                                btn.setAttribute('aria-expanded', 'true');
                                menu.classList.remove('hidden', 'opacity-0', 'scale-95');
                                menu.classList.add('opacity-100', 'scale-100');
                                chev.classList.add('rotate-180');
                                // move focus to first menu item for accessibility
                                const first = menu.querySelector('[role="menuitem"]');
                                if (first) first.focus();
                            }

                            function closeMenu() {
                                btn.setAttribute('aria-expanded', 'false');
                                // keep hidden until transition ends for smoothness
                                menu.classList.remove('opacity-100', 'scale-100');
                                menu.classList.add('opacity-0', 'scale-95');
                                chev.classList.remove('rotate-180');
                                // delay adding hidden so transition can run
                                window.setTimeout(() => menu.classList.add('hidden'), 150);
                            }

                            btn.addEventListener('click', function (e) {
                                e.stopPropagation();
                                const opened = btn.getAttribute('aria-expanded') === 'true';
                                if (opened) closeMenu(); else openMenu();
                            });

                            // Close when clicking outside
                            document.addEventListener('click', function (e) {
                                if (!menu.contains(e.target) && !btn.contains(e.target)) {
                                    if (btn.getAttribute('aria-expanded') === 'true') closeMenu();
                                }
                            });

                            // Close on Escape, allow keyboard navigation
                            document.addEventListener('keydown', function (e) {
                                if (e.key === 'Escape') {
                                    if (btn.getAttribute('aria-expanded') === 'true') {
                                        closeMenu();
                                        btn.focus();
                                    }
                                }
                                // Handle ArrowDown/ArrowUp for simple keyboard nav
                                if ((e.key === 'ArrowDown' || e.key === 'ArrowUp') && btn.getAttribute('aria-expanded') !== 'true') {
                                    e.preventDefault();
                                    openMenu();
                                }
                            });

                            // Close on blur if focus moves outside
                            document.addEventListener('focusin', function (e) {
                                if (!menu.contains(e.target) && !btn.contains(e.target)) {
                                    if (btn.getAttribute('aria-expanded') === 'true') closeMenu();
                                }
                            });
                        })();
                    </script>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="p-6">
            @yield('content')
        </main>
    </div>
</div>
<!-- Toast Notifications -->
<script>
// Toast configuration
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

// Show toast notification
window.showToast = function(type, message) {
    Toast.fire({
        icon: type,
        title: message
    });
};

// Handle flash messages
@if(session('success'))
    showToast('success', '{{ session('success') }}');
@endif

@if(session('error'))
    showToast('error', '{{ session('error') }}');
@endif

@if($errors->any())
    @foreach($errors->all() as $error)
        showToast('error', '{{ $error }}');
    @endforeach
@endif

// Header scripts for date range
document.addEventListener('DOMContentLoaded', function () {
    const dateRangeBtn = document.getElementById('dateRangeBtn');
    const dateRangeDropdown = document.getElementById('dateRangeDropdown');
    const customDateRange = document.getElementById('customDateRange');
    const dateRangeOptions = document.querySelectorAll('.date-range-option');
    const selectedRange = document.getElementById('selectedRange');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const applyDateRange = document.getElementById('applyDateRange');

    if (!dateRangeBtn) return;

    const today = new Date();
    const firstDayOfYear = new Date(today.getFullYear(), 0, 1);
    if (dateFrom) dateFrom.valueAsDate = firstDayOfYear;
    if (dateTo) dateTo.valueAsDate = today;

    dateRangeBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        dateRangeDropdown.classList.toggle('hidden');
    });
    document.addEventListener('click', function () {
        dateRangeDropdown.classList.add('hidden');
    });
    dateRangeOptions.forEach(option => {
        option.addEventListener('click', function (e) {
            e.stopPropagation();
            const range = this.getAttribute('data-range');
            if (range === 'custom') {
                customDateRange.classList.remove('hidden');
            } else {
                customDateRange.classList.add('hidden');
                updateDateRange(range);
                dateRangeDropdown.classList.add('hidden');
            }
        });
    });
    if (applyDateRange) {
        applyDateRange.addEventListener('click', function (e) {
            e.stopPropagation();
            if (dateFrom.value && dateTo.value) {
                const from = new Date(dateFrom.value);
                const to = new Date(dateTo.value);
                selectedRange.textContent = `${formatDate(from)} - ${formatDate(to)}`;
                dateRangeDropdown.classList.add('hidden');
            }
        });
    }
    function updateDateRange(range) {
        const today = new Date();
        let from;
        if (range === 'year') {
            from = new Date(today.getFullYear(), 0, 1);
            selectedRange.textContent = 'This Year';
        } else if (range === 'month') {
            from = new Date(today.getFullYear(), today.getMonth(), 1);
            selectedRange.textContent = 'This Month';
        }
        if (dateFrom) dateFrom.valueAsDate = from;
        if (dateTo) dateTo.valueAsDate = today;
    }
    function formatDate(date) {
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }
});
</script>
</body>
</html>
