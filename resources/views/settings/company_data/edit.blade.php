@extends('layouts.appPages')



@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4>Company Data</h4>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('settings.company-data.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $company->company_name ?? '') }}" required>
                    @error('company_name')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $company->email ?? '') }}">
                    @error('email')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $company->phone ?? '') }}">
                    @error('phone')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Website</label>
                    <input type="url" name="website" class="form-control" value="{{ old('website', $company->website ?? '') }}">
                    @error('website')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="4">{{ old('address', $company->address ?? '') }}</textarea>
                    @error('address')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Logo (PNG/JPG, max 2MB)</label>
                    @if(!empty($company->logo_path) && \Storage::disk('public')->exists($company->logo_path))
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $company->logo_path) }}" alt="Current logo" style="max-height:80px;">
                        </div>
                    @endif
                    <input type="file" name="logo" class="form-control">
                    @error('logo')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <button class="btn btn-success" type="submit">Save</button>
            </form>
        </div>
    </div>
</div>
@endsection
