@extends('student.layout')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">Welcome, {{ $student->first_name }} {{ $student->last_name }}</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Current Class</h5>
                <h3>{{ $student->currentClass ? $student->currentClass->class_name : 'N/A' }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Total Fees</h5>
                <h3>₦{{ number_format($totalFees, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Amount Paid</h5>
                <h3>₦{{ number_format($totalPaid, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Pending Fees</h5>
                <h3>₦{{ number_format($pendingFees, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Academic Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Current Session:</strong> {{ $currentSession ? $currentSession->session_name : 'N/A' }}</p>
                <p><strong>Current Term:</strong> {{ $currentTerm ? $currentTerm->term_name : 'N/A' }}</p>
                <p><strong>Student Number:</strong> {{ $student->student_number }}</p>
                <p><strong>Admission Status:</strong> <span class="badge bg-{{ $student->admission_status == 'approved' ? 'success' : 'warning' }}">{{ ucfirst($student->admission_status) }}</span></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Recent Grades</h5>
            </div>
            <div class="card-body">
                @if($recentGrades->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Grade</th>
                                    <th>Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentGrades as $grade)
                                    <tr>
                                        <td>{{ $grade->subject->subject_name }}</td>
                                        <td>{{ $grade->grade }}</td>
                                        <td>{{ $grade->score }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No grades available yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Quick Actions</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('student.results') }}" class="btn btn-primary me-2">View Results</a>
                <a href="{{ route('student.payments') }}" class="btn btn-success me-2">View Payments</a>
                <a href="{{ route('student.receipts') }}" class="btn btn-info me-2">View Receipts</a>
                <a href="{{ route('student.complaints') }}" class="btn btn-warning">Submit Complaint</a>
            </div>
        </div>
    </div>
</div>
@endsection