@extends('layouts.appPages')
@section('content')
<div class="container fancy-container">
    <div class="gradient-banner mb-4">
        <div class="d-flex align-items-center">
            <span class="banner-icon me-2"><i class="fas fa-users"></i></span>
            <h1 class="my-2 mb-0" style="font-weight: 700; letter-spacing: 1px;">Users</h1>
        </div>
        <p class="text-muted mb-0" style="font-size:1.1rem;">Browse, search, and manage all users in your system. Use the table below for quick actions and insights.</p>
    </div>
    <hr class="section-divider mb-4">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <a href="{{ route('users.create') }}" class="btn btn-primary mb-3" style="font-size:1.1rem; font-weight:600; padding:10px 24px;"><i class="fas fa-plus"></i> Add User</a>
    <div class="card-body">
        <div class="table-responsive" style="overflow-x: auto; overflow-y: auto; max-width: 970px;">
            <table id="usersTable" class="table table-striped rounded-top" style="width: auto; font-size: 12px;">
                <thead style="white-space: nowrap;">
                    <tr>
                        <th><i class="fas fa-user"></i> Name</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-calendar-plus"></i> Created At</th>
                        <th><i class="fas fa-calendar-check"></i> Updated At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody style="white-space: nowrap;">
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->created_at }}</td>
                        <td>{{ $user->updated_at }}</td>
                        <td style="white-space: nowrap;">
                            <a href="{{ route('users.show', $user->id) }}" class="btn btn-info btn-xs" title="View"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-xs" title="Edit"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs" title="Delete" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
