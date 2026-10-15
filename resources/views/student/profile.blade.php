@extends('student.layout')

@section('title', 'Student Profile - ' . ($student->first_name ?? 'Student'))

@section('content')
@php
    $passportUrl = !empty($student->passport) 
        ? (str_starts_with($student->passport, 'http') || str_starts_with($student->passport, '/') ? $student->passport : '/storage/' . $student->passport)
        : null;

    $catLabel = match (strtoupper((string)($student->category ?? $student->student_type ?? ''))) {
        'OS', 'RETURNING' => 'Returning Student (OS)',
        'NI', 'NEW_INTAKE', 'NEW' => 'New Intake (NI)',
        default => 'Standard Student'
    };
@endphp

<!-- Page Header & Breadcrumbs -->
<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Student Profile</h3>
        <ul class="breadcrumbs ps-0 mb-0 d-flex align-items-center text-muted" style="list-style: none; font-size: 13px;">
            <li class="nav-home"><a href="{{ route('student.dashboard') }}" class="text-muted"><i class="fas fa-home"></i></a></li>
            <li class="separator mx-2"><i class="fas fa-chevron-right text-muted" style="font-size: 10px;"></i></li>
            <li class="nav-item text-dark fw-semibold">My Academic & Personal Profile</li>
        </ul>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary btn-round btn-sm px-3 shadow-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print Profile
        </button>
        <a href="{{ route('student.results') }}" class="btn btn-primary btn-round btn-sm px-3 shadow-sm">
            <i class="fas fa-award me-1"></i> View Results
        </a>
    </div>
</div>

<div class="row">
    <!-- Left Column: Student Identity Card -->
    <div class="col-lg-4 col-md-5 mb-4">
        <div class="card card-profile shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-header position-relative" style="background: linear-gradient(135deg, #720922 0%, #4a0314 100%); height: 110px;">
                <div class="position-absolute top-0 end-0 p-3">
                    <span class="badge bg-success text-white px-2 py-1" style="font-size: 11px;">
                        <i class="fas fa-check-circle me-1"></i> {{ ucfirst($student->status ?? 'Active') }}
                    </span>
                </div>
            </div>
            <div class="card-body text-center pt-0 position-relative">
                <div class="profile-picture" style="margin-top: -55px; margin-bottom: 15px;">
                    <div class="avatar avatar-xxl position-relative d-inline-block">
                        @if($passportUrl)
                            <img src="{{ $passportUrl }}" alt="{{ $student->first_name }}" class="avatar-img rounded-circle border border-4 border-white shadow" style="width: 110px; height: 110px; object-fit: cover;" />
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow border border-4 border-white" style="width: 110px; height: 110px; background: linear-gradient(135deg, #720922, #8e1532); font-size: 38px; margin: 0 auto;">
                                {{ strtoupper(substr($student->first_name ?? 'S', 0, 1) . substr($student->last_name ?? 'T', 0, 1)) }}
                            </div>
                        @endif
                        <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-white border-2 rounded-circle" title="Enrolled & Active"></span>
                    </div>
                </div>

                <h4 class="fw-bold mb-1 text-dark">{{ $student->first_name }} {{ $student->last_name }} {{ $student->other_names ?? '' }}</h4>
                <div class="text-muted small mb-2">
                    <span class="fw-semibold text-primary">Adm No:</span> <code>{{ $student->admission_no ?? $student->student_number ?? 'N/A' }}</code>
                </div>

                <div class="d-flex flex-wrap justify-content-center gap-1 mb-3">
                    @if($student->currentClass)
                        <span class="badge bg-warning text-dark px-3 py-1 fw-bold rounded-pill" style="font-size: 11px;">
                            <i class="fas fa-chalkboard me-1 text-danger"></i> {{ $student->currentClass->class_name }}
                        </span>
                    @endif
                    <span class="badge bg-secondary text-white px-3 py-1 rounded-pill" style="font-size: 11px;">
                        {{ $catLabel }}
                    </span>
                </div>

                <hr class="my-3 opacity-25">

                <!-- 3 Quick KPI Badges -->
                <div class="row text-center g-2 mb-3">
                    <div class="col-4">
                        <div class="p-2 rounded-3 bg-light border">
                            <span class="d-block text-muted" style="font-size: 10px; text-transform: uppercase;">Attendance</span>
                            <span class="fw-bold text-success fs-6">{{ number_format((float)$attendancePercentage, 1) }}%</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded-3 bg-light border">
                            <span class="d-block text-muted" style="font-size: 10px; text-transform: uppercase;">Average</span>
                            <span class="fw-bold text-primary fs-6">{{ number_format((float)$gradeAverage, 1) }}%</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded-3 bg-light border">
                            <span class="d-block text-muted" style="font-size: 10px; text-transform: uppercase;">Subjects</span>
                            <span class="fw-bold text-dark fs-6">{{ count($allocatedSubjects) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Biometric & Health Details -->
                <div class="text-start p-3 bg-light rounded-3 border">
                    <h6 class="fw-bold text-dark mb-2" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fas fa-heartbeat text-danger me-1"></i> Medical & Vitals
                    </h6>
                    <div class="d-flex justify-content-between py-1 border-bottom border-light-subtle small">
                        <span class="text-muted">Blood Group:</span>
                        <span class="fw-bold text-dark">{{ $student->blood_group ?: 'Not Stated' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom border-light-subtle small">
                        <span class="text-muted">Genotype:</span>
                        <span class="fw-bold text-dark">{{ $student->genotype ?: 'Not Stated' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 small">
                        <span class="text-muted">Gender:</span>
                        <span class="fw-bold text-dark">{{ ucfirst($student->gender ?? 'N/A') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Personal & Academic Details -->
    <div class="col-lg-8 col-md-7">
        <!-- Personal Information Card -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h5 class="card-title fw-bold mb-0 text-dark" style="font-size: 16px;">Personal & Demographic Information</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="text-muted small d-block mb-1">Full Legal Name</label>
                        <span class="fw-bold text-dark fs-6">{{ $student->first_name }} {{ $student->last_name }} {{ $student->other_names ?? '' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block mb-1">Admission Number</label>
                        <span class="fw-bold text-primary fs-6 font-monospace">{{ $student->admission_no ?? 'N/A' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block mb-1">Date of Birth</label>
                        <span class="fw-semibold text-dark">
                            <i class="fas fa-birthday-cake text-muted me-1"></i>
                            {{ $student->date_of_birth ? $student->date_of_birth->format('F d, Y') : 'Not Provided' }}
                        </span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block mb-1">Gender</label>
                        <span class="fw-semibold text-dark">{{ ucfirst($student->gender ?? 'Not Specified') }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block mb-1">Email Address</label>
                        <span class="fw-semibold text-dark">
                            <i class="fas fa-envelope text-muted me-1"></i>
                            {{ $student->email ?: 'No email registered' }}
                        </span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block mb-1">Phone Number</label>
                        <span class="fw-semibold text-dark">
                            <i class="fas fa-phone-alt text-muted me-1"></i>
                            {{ $student->phone ?: 'No phone registered' }}
                        </span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block mb-1">State of Origin & LGA</label>
                        <span class="fw-semibold text-dark">
                            <i class="fas fa-map-marker-alt text-danger me-1"></i>
                            {{ $student->state_of_origin ?: 'N/A' }} {{ $student->lga ? '('.$student->lga.')' : '' }}
                        </span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block mb-1">Residential Address</label>
                        <span class="fw-semibold text-dark">
                            <i class="fas fa-home text-muted me-1"></i>
                            {{ $student->address ?: ($student->home_address ?: 'Not provided') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic Enrollment Information Card -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                <div class="rounded-circle bg-warning-subtle text-warning p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h5 class="card-title fw-bold mb-0 text-dark" style="font-size: 16px;">Academic Placement & Status</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6 col-md-4">
                        <label class="text-muted small d-block mb-1">Current Class</label>
                        <span class="fw-bold text-dark fs-6">
                            <i class="fas fa-chalkboard text-primary me-1"></i>
                            {{ $student->currentClass ? $student->currentClass->class_name : 'Unassigned' }}
                        </span>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <label class="text-muted small d-block mb-1">Academic Session</label>
                        <span class="fw-semibold text-dark">
                            <i class="fas fa-calendar-alt text-muted me-1"></i>
                            {{ $currentSession ? $currentSession->session_name : 'Current' }}
                        </span>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <label class="text-muted small d-block mb-1">Term</label>
                        <span class="fw-semibold text-dark">
                            <i class="fas fa-clock text-muted me-1"></i>
                            {{ $currentTerm ? $currentTerm->term_name : 'Current Term' }}
                        </span>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <label class="text-muted small d-block mb-1">Intake / Fee Category</label>
                        <span class="badge bg-secondary px-3 py-1 rounded-pill">{{ $catLabel }}</span>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <label class="text-muted small d-block mb-1">Enrollment Date</label>
                        <span class="fw-semibold text-dark">
                            {{ $student->enrollment_date ? $student->enrollment_date->format('d/m/Y') : ($student->created_at ? $student->created_at->format('d/m/Y') : 'N/A') }}
                        </span>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <label class="text-muted small d-block mb-1">Admission Status</label>
                        <span class="badge bg-{{ in_array(strtolower($student->admission_status ?? ''), ['approved', 'admitted', 'active']) ? 'success' : 'warning' }} px-3 py-1 rounded-pill">
                            {{ ucfirst($student->admission_status ?? 'Approved') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Parent / Guardian Information Card -->
        @if($parent)
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                <div class="rounded-circle bg-info-subtle text-info p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                    <i class="fas fa-user-friends"></i>
                </div>
                <h5 class="card-title fw-bold mb-0 text-dark" style="font-size: 16px;">Parent / Guardian Information</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="text-muted small d-block mb-1">Guardian Name</label>
                        <span class="fw-bold text-dark">{{ $parent->first_name }} {{ $parent->last_name }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block mb-1">Relationship</label>
                        <span class="badge bg-light text-dark border px-2 py-1">{{ ucfirst($parent->relationship ?? 'Parent/Guardian') }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block mb-1">Contact Phone</label>
                        <span class="fw-semibold text-dark">{{ $parent->phone ?: 'Not provided' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block mb-1">Email Address</label>
                        <span class="fw-semibold text-dark">{{ $parent->email ?: 'Not provided' }}</span>
                    </div>
                    @if(!empty($parent->address))
                    <div class="col-12">
                        <label class="text-muted small d-block mb-1">Contact Address</label>
                        <span class="fw-semibold text-dark">{{ $parent->address }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Enrolled Subjects Card -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-0 text-dark" style="font-size: 16px;">Curriculum & Registered Subjects</h5>
                </div>
                <span class="badge bg-primary text-white rounded-pill px-3 py-1">{{ count($allocatedSubjects) }} Active Subjects</span>
            </div>
            <div class="card-body p-4">
                @if(count($allocatedSubjects) > 0)
                    <div class="row g-2">
                        @foreach($allocatedSubjects as $subject)
                            <div class="col-md-6 col-sm-12">
                                <div class="d-flex align-items-center p-2 rounded-3 border bg-light">
                                    <div class="rounded p-2 bg-white text-primary me-2 border shadow-sm">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div class="text-truncate">
                                        <span class="fw-bold text-dark d-block text-truncate small">{{ $subject['subject_name'] ?? 'Subject' }}</span>
                                        <span class="text-muted" style="font-size: 11px;">
                                            Code: <code>{{ $subject['subject_code'] ?? 'SUB' }}</code>
                                            @if(!empty($subject['class_level']))
                                                &bull; {{ $subject['class_level'] }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0 small text-center py-3">No subjects registered yet for this session. Contact your class teacher or administrator.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection