@extends('admin.layout')

@section('title', 'Academic Sessions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Academic Sessions</h2>
    <a href="{{ route('admin.sessions.create') }}" class="btn btn-primary">Add Session</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Active</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sessions as $session)
        <tr>
            <td>{{ $session->session_name }}</td>
            <td>{{ $session->start_date->format('Y-m-d') }}</td>
            <td>{{ $session->end_date->format('Y-m-d') }}</td>
            <td>{{ $session->is_active ? 'Yes' : 'No' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
