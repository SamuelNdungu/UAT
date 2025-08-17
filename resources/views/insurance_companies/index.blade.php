@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="my-4">Insurance Companies</h1>
    <a href="{{ route('insurance_companies.create') }}" class="btn btn-primary mb-3">Add Insurance Company</a>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        <script>
            window.onload = function() {
                alert('You cannot delete Insurer as it has active policies.');
            }
        </script>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>City</th>
                <th>Country</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($companies as $company)
            <tr>
                <td>{{ $company->name }}</td>
                <td>{{ $company->email }}</td>
                <td>{{ $company->phone }}</td>
                <td>{{ $company->city }}</td>
                <td>{{ $company->country }}</td>
                <td>
                    <a href="{{ route('insurance_companies.show', $company->id) }}" class="btn btn-info btn-sm" title="View">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('insurance_companies.edit', $company->id) }}" class="btn btn-warning btn-sm" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('insurance_companies.destroy', $company->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure?')">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
