@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Students</h5>
                <h2>{{ $stats['total_students'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Staff</h5>
                <h2>{{ $stats['total_staff'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Active Sessions</h5>
                <h2>{{ $stats['active_sessions'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Pending Admissions</h5>
                <h2>{{ $stats['pending_admissions'] }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                Quick Actions
            </div>
            <div class="card-body">
                <a href="{{ route('admin.students.create') }}" class="btn btn-primary">Add Student</a>
                <a href="{{ route('admin.fees.create') }}" class="btn btn-success">Create Fee</a>
                <a href="{{ route('admin.sessions.create') }}" class="btn btn-info">Add Session</a>
                <a href="{{ route('admin.staff.create') }}" class="btn btn-warning">Add Staff</a>
            </div>
        </div>
    </div>
</div>
@endsection
