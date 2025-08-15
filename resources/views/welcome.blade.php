@extends('layouts.appLR')

@section('content')
<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Arial', sans-serif;
    }
    .container {
        margin-top: 1px;
    }
    .card {
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }
    .card-header {
        background: linear-gradient(45deg, #007bff, #e74c3c);
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
        padding: 20px;
    }
    .card-body {
        padding: 30px;
    }
    .form-control {
        border-radius: 10px;
        border-color: #ced4da;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 8px rgba(0, 123, 255, 0.25);
    }
    .btn-primary {
        border-radius: 10px;
        background-color: #007bff;
        border-color: #007bff;
    }
    .btn-primary:hover {
        background-color: #0069d9;
        border-color: #0062cc;
    }
    .form-label {
        color: #343a40;
        font-weight: bold;
    }
    .text-center {
        margin-top: 10px;
    }
    .form-check-label {
        color: #343a40;
    }
    .text-primary {
        color: #007bff;
        font-weight: bold;
    }
    .text-primary:hover {
        text-decoration: underline;
    }
    .gradient-text {
        background: linear-gradient(45deg,rgb(94, 33, 5),rgb(190, 156, 62));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .social-icons a {
        color: #007bff;
        font-size: 24px;
        margin-right: 15px;
        transition: color 0.3s ease;
    }
    .social-icons a:hover {
        color: #e74c3c;
    }
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <!-- Logo -->
            <div class="text-center mb-4">
                <img src="{{ asset('img/logo.png') }}" alt="Logo" class="img-fluid" style="max-height: 80px;">
            </div>
            <div class="card">
                <div class="card-header text-center">
                    <h2 class="mb-0 text-white gradient-text">{{ __('Login') }}</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Email') }}</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('Password') }}</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                name="password" required autocomplete="current-password">
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" 
                                {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                {{ __('Remember Me') }}
                            </label>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">{{ __('Login') }}</button>
                        </div>
                        @if (Route::has('password.request'))
                            <div class="text-center mb-3">
                                <a class="text-decoration-none text-primary" href="{{ route('password.request') }}">
                                    {{ __('Forgot Your Password?') }}
                                </a>
                            </div>
                        @endif
 
                        <!-- Optional: Social Icons -->
                        <div class="text-center mt-4 social-icons">
                            <a href="#" class="text-decoration-none"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="text-decoration-none"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-decoration-none"><i class="fab fa-google"></i></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
