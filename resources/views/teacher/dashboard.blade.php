@extends('teacher.layout')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h2>Welcome, {{ Auth::user()->name }}</h2>
        <p>Active Session: {{ $activeSession ? $activeSession->session_name : 'None' }}</p>
        <p>Active Term: {{ $activeTerm ? $activeTerm->term_name : 'None' }}</p>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Attendance Management</h5>
            </div>
            <div class="card-body">
                <p>Mark daily attendance for your assigned classes.</p>
                <a href="{{ route('teacher.attendance') }}" class="btn btn-primary">Manage Attendance</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Results Management</h5>
            </div>
            <div class="card-body">
                <p>Enter CA1, CA2, and Exam scores for students.</p>
                <a href="{{ route('teacher.results') }}" class="btn btn-success">Manage Results</a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Your Assigned Classes</h5>
            </div>
            <div class="card-body">
                @if($assignedClasses->count() > 0)
                    <ul class="list-group">
                        @foreach($assignedClasses as $class)
                            <li class="list-group-item">{{ $class->class_name }}</li>
                        @endforeach
                    </ul>
                @else
                    <p>No classes assigned yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
