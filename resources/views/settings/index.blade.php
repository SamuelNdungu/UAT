@extends('layouts.app')

@section('content')
<div class="container settings-container">
    <div class="settings-banner mb-4">
        <div class="d-flex align-items-center">
            <span class="settings-banner-icon me-2"><i class="fas fa-cogs"></i></span>
            <h1 class="my-2 mb-0" style="font-weight: 700; letter-spacing: 1px;">Settings</h1>
        </div>
        <p class="text-muted mb-0" style="font-size:1.1rem;">Configure and manage all system entities and admin features from one dashboard.</p>
    </div>
    <hr class="settings-divider mb-4">
    <div class="row g-4">
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('insurance_companies.index') }}" class="settings-card card shadow-sm text-decoration-none">
                <div class="card-body d-flex align-items-center">
                    <span class="settings-card-icon bg-success text-white me-3"><i class="fas fa-building"></i></span>
                    <div>
                        <h5 class="mb-1">Insurance Companies</h5>
                        <p class="mb-0 text-muted">Manage all insurance companies in the system.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('users.index') }}" class="settings-card card shadow-sm text-decoration-none">
                <div class="card-body d-flex align-items-center">
                    <span class="settings-card-icon bg-primary text-white me-3"><i class="fas fa-users"></i></span>
                    <div>
                        <h5 class="mb-1">Users</h5>
                        <p class="mb-0 text-muted">Manage system users and permissions.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('vehicle_types.index') }}" class="settings-card card shadow-sm text-decoration-none">
                <div class="card-body d-flex align-items-center">
                    <span class="settings-card-icon bg-warning text-white me-3"><i class="fas fa-car"></i></span>
                    <div>
                        <h5 class="mb-1">Motor Vehicle Types</h5>
                        <p class="mb-0 text-muted">Manage all motor vehicle types.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('policy_types.index') }}" class="settings-card card shadow-sm text-decoration-none">
                <div class="card-body d-flex align-items-center">
                    <span class="settings-card-icon bg-info text-white me-3"><i class="fas fa-file-signature"></i></span>
                    <div>
                        <h5 class="mb-1">Policy Types</h5>
                        <p class="mb-0 text-muted">Manage all policy types available.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('settings.company-data.show') }}" class="settings-card card shadow-sm text-decoration-none">
                <div class="card-body d-flex align-items-center">
                    <span class="settings-card-icon bg-secondary text-white me-3"><i class="fas fa-building"></i></span>
                    <div>
                        <h5 class="mb-1">Company Data</h5>
                        <p class="mb-0 text-muted">Manage the organization's contact and address details.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
 
</div>
<style>
.settings-container {
    margin-top: 32px;
    margin-bottom: 32px;
}
.settings-banner {
    background: linear-gradient(90deg, #4f8cff 0%, #6ed6ff 100%);
    border-radius: 16px;
    padding: 32px 28px 18px 28px;
    box-shadow: 0 4px 24px 0 rgba(60, 72, 88, 0.12);
    color: #fff;
    margin-bottom: 0;
}
.settings-banner-icon {
    font-size: 2.2rem;
    color: #fff;
    background: rgba(255,255,255,0.12);
    border-radius: 50%;
    padding: 10px;
    box-shadow: 0 2px 8px 0 rgba(60, 72, 88, 0.10);
    margin-right: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.settings-divider {
    border: none;
    border-top: 1.5px solid #e0e6ed;
    margin: 0 0 24px 0;
    opacity: 0.7;
}
.settings-card {
    border-radius: 16px;
    transition: box-shadow 0.3s, transform 0.3s;
    box-shadow: 0 2px 12px 0 rgba(60, 72, 88, 0.10);
    border: none;
    background: #fff;
    margin-bottom: 0;
}
.settings-card:hover {
    box-shadow: 0 8px 32px 0 rgba(60, 72, 88, 0.18);
    transform: translateY(-2px) scale(1.03);
    z-index: 2;
}
.settings-card-icon {
    font-size: 2rem;
    border-radius: 50%;
    padding: 12px;
    box-shadow: 0 2px 8px 0 rgba(60, 72, 88, 0.10);
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endsection
