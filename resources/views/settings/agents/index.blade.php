@extends('layouts.appPages')

@section('content')

<div class="container">
    <h2>Agents</h2>
    <a href="{{ route('settings.agents.create') }}" class="btn btn-primary mb-2">Add Agent</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Agent Code</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>KRA PIN</th>
                <th>Commission Rate</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($agents as $agent)
            <tr>
                <td>{{ $agent->agent_code }}</td>
                <td>{{ $agent->name }}</td>
                <td>{{ $agent->phone }}</td>
                <td>{{ $agent->email }}</td>
                <td>{{ $agent->kra_pin }}</td>
                <td>{{ $agent->commission_rate }}</td>
                <td>{{ $agent->status }}</td>
                <td>
                    <a href="{{ route('settings.agents.edit', $agent) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('settings.agents.destroy', $agent) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete agent?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $agents->links() }}
</div>
@endsection
