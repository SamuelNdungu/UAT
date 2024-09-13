@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Reports</h3>
        </div>
        <div class="card-body">
            <!-- Add the filter form here -->
            <form action="{{ route('reports.export.claims.excel') }}" method="GET" class="mb-3">
                <div class="form-row">
                    <div class="col-md-4">
                        <label for="date_from">From:</label>
                        <input type="date" name="date_from" id="date_from" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="date_to">To:</label>
                        <input type="date" name="date_to" id="date_to" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="status">Status:</label>
                        <select name="status" id="status" class="form-control">
                            <option value="all">All</option>
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>
                <div class="form-row mt-3">
                    <div class="col">
                        <button type="submit" class="btn btn-outline-primary">Export to Excel</button>
                        <a href="{{ route('reports.export.claims.pdf') }}" class="btn btn-outline-danger">Export to PDF</a>
                    </div>
                </div>
            </form>

            <!-- Display a table if there are any reports -->
            <table class="table table-hover">
                <thead class="thead-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Report Name</th>
                        <th scope="col">Date Created</th>
                        <th scope="col" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th scope="row">1</th>
                        <td>Claims Report</td>
                        <td>{{ \Carbon\Carbon::now()->format('Y-m-d') }}</td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <a href="{{ route('reports.export.claims.excel') }}" class="btn btn-outline-primary">Excel</a>
                                <a href="{{ route('reports.export.claims.pdf') }}" class="btn btn-outline-danger">PDF</a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
