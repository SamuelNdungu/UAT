@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="my-4">Settings</h1>
    <div class="row">
        <div class="col-md-4 mb-3">

            <!-- Link to Insurance Companies CRUD -->
            <div class="col-md-4 mb-3">
                <a href="{{ route('insurance_companies.index') }}" class="btn btn-outline-success w-100">
                    <i class="fas fa-building"></i> Manage Insurance Companies
            </a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="#" class="btn btn-outline-primary w-100">
                <i class="fas fa-users"></i> Users
            </a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="#" class="btn btn-outline-primary w-100">
                <i class="fas fa-car"></i> Motor Vehicle Types
            </a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="#" class="btn btn-outline-primary w-100">
                <i class="fas fa-file-signature"></i> Policy Types
            </a>
        </div>
    </div>
    <p class="mt-4">More admin menus can be added here as needed.</p>
</div>
@endsection
