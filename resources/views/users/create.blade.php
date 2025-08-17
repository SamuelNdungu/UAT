@extends('layouts.appPages')
@section('content')
<div class="container fancy-container">
    <div class="gradient-banner mb-4">
        <div class="d-flex align-items-center">
            <span class="banner-icon me-2"><i class="fas fa-users"></i></span>
            <h1 class="my-2 mb-0" style="font-weight: 700; letter-spacing: 1px;">Add User</h1>
        </div>
        <p class="text-muted mb-0" style="font-size:1.1rem;">Fill in the details below to add a new user to the system.</p>
    </div>
    <hr class="section-divider mb-4">
    <div class="card shadow-sm border-0 mb-3" style="background: #f8fafc; border-radius: 12px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="mb-3 position-relative">
                    <label for="name" class="form-label">Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="name" name="name" placeholder="e.g. John Doe" value="{{ old('name') }}">
                    </div>
                    @error('name')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3 position-relative">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="e.g. johndoe@email.com" value="{{ old('email') }}">
                    </div>
                    @error('email')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3 position-relative">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password">
                    </div>
                    @error('password')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3 position-relative">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm password">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100 mt-3" style="font-size:1.1rem; font-weight:600;">
                    <i class="fas fa-save"></i> Save User
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
