@extends('student.layout')

@section('title', 'My Profile')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">My Profile</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                @if($student->passport)
                    <img src="{{ asset('storage/' . $student->passport) }}" alt="Passport" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                @else
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px;">
                        <i class="fas fa-user fa-3x text-muted"></i>
                    </div>
                @endif
                <h5>{{ $student->first_name }} {{ $student->last_name }}</h5>
                <p class="text-muted">{{ $student->student_number }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Personal Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>First Name:</strong> {{ $student->first_name }}</p>
                        <p><strong>Last Name:</strong> {{ $student->last_name }}</p>
                        <p><strong>Email:</strong> {{ $student->email }}</p>
                        <p><strong>Phone:</strong> {{ $student->phone ?: 'Not provided' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Date of Birth:</strong> {{ $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : 'Not provided' }}</p>
                        <p><strong>Gender:</strong> {{ ucfirst($student->gender) }}</p>
                        <p><strong>Address:</strong> {{ $student->address ?: 'Not provided' }}</p>
                        <p><strong>Enrollment Date:</strong> {{ $student->enrollment_date ? $student->enrollment_date->format('d/m/Y') : 'Not provided' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5>Academic Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Current Class:</strong> {{ $student->currentClass ? $student->currentClass->class_name : 'Not assigned' }}</p>
                        <p><strong>Current Session:</strong> {{ $student->currentSession ? $student->currentSession->session_name : 'Not assigned' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Current Term:</strong> {{ $student->currentTerm ? $student->currentTerm->term_name : 'Not assigned' }}</p>
                        <p><strong>Admission Status:</strong> <span class="badge bg-{{ $student->admission_status == 'approved' ? 'success' : 'warning' }}">{{ ucfirst($student->admission_status) }}</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection