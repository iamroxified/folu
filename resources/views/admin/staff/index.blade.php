@extends('admin.layout')

@section('title', 'Staff')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Staff</h2>
    <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">Add Staff</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        @foreach($staff as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->role?->role_name ?? 'N/A' }}</td>
            <td>{{ $user->created_at->format('Y-m-d') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $staff->links() }}
@endsection
