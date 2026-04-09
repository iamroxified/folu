@extends('admin.layout')

@section('title', 'View Student')

@section('content')
<h2>Student Details</h2>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h4>{{ $student->first_name }} {{ $student->last_name }}</h4>
                <p><strong>Student Number:</strong> {{ $student->student_number }}</p>
                <p><strong>Email:</strong> {{ $student->email }}</p>
                <p><strong>Phone:</strong> {{ $student->phone }}</p>
                <p><strong>Address:</strong> {{ $student->address }}</p>
                <p><strong>Date of Birth:</strong> {{ $student->date_of_birth->format('d/m/Y') }}</p>
                <p><strong>Gender:</strong> {{ ucfirst($student->gender) }}</p>
                <p><strong>Admission Status:</strong>
                    <span class="badge bg-{{ $student->admission_status == 'admitted' ? 'success' : ($student->admission_status == 'pending' ? 'warning' : 'danger') }}">
                        {{ ucfirst($student->admission_status) }}
                    </span>
                </p>
                <p><strong>Category:</strong> {{ $student->category }}</p>
                <p><strong>Current Class:</strong> {{ $student->currentClass ? $student->currentClass->class_name : 'N/A' }}</p>
                <p><strong>Current Session:</strong> {{ $student->currentSession ? $student->currentSession->session_name : 'N/A' }}</p>
                <p><strong>Current Term:</strong> {{ $student->currentTerm ? $student->currentTerm->term_name : 'N/A' }}</p>
                <p><strong>Enrollment Date:</strong> {{ $student->enrollment_date->format('d/m/Y') }}</p>
            </div>
            <div class="col-md-4">
                @if($student->passport)
                    <img src="{{ asset('storage/' . $student->passport) }}" alt="Passport" class="img-fluid rounded">
                @else
                    <div class="text-center">
                        <i class="fas fa-user-circle fa-5x text-muted"></i>
                        <p>No passport photo</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="card-footer">
        <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-warning">Edit</a>
        <a href="{{ route('admin.students.admission_letter', $student) }}" class="btn btn-info" target="_blank">Print Admission Letter</a>
        <a href="{{ route('admin.students') }}" class="btn btn-secondary">Back</a>
    </div>
</div>
@endsection
