@extends('layouts.app')

@section('content')
    <h1>Edit Lead</h1>

    <form action="{{ route('leads.update', $lead->id) }}" method="POST">
        @csrf
        @method('PUT')
        <!-- Add fields for corporate_name, contact_name, first_name, last_name, etc. -->
        <button type="submit">Update</button>
    </form>
@endsection
