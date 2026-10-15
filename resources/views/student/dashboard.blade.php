@extends('student.layout')

@section('title', 'Student Dashboard')

@section('content')
@php
    $passportUrl = !empty($student->passport) 
        ? (str_starts_with($student->passport, 'http') || str_starts_with($student->passport, '/') ? $student->passport : '/storage/' . $student->passport)
        : null;

    $isReturning = !in_array($student->category, ['NI', 'NEW_INTAKE']);
@endphp

<!-- Hero Banner -->
<div class="student-hero-banner">
    <div class="row align-items-center position-relative" style="z-index: 2;">
        <div class="col-lg-8 col-md-7 mb-3 mb-md-0">
            <div class="d-flex align-items-center gap-3 flex-wrap flex-sm-nowrap">
                <div class="position-relative flex-shrink-0">
                    @if($passportUrl)
                        <img src="{{ $passportUrl }}" alt="{{ $student->first_name }}" class="rounded-circle border border-3 border-white shadow" style="width: 86px; height: 86px; object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-white text-primary d-inline-flex align-items-center justify-content-center shadow" style="width: 86px; height: 86px; font-size: 32px; font-weight: bold; color: var(--brand-primary) !important;">
                            {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                        </div>
                    @endif
                    <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-2 border-white rounded-circle" title="Active Student"></span>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <span class="badge bg-warning text-dark fw-bold px-2 py-1">Student Portal</span>
                        <span class="badge bg-white-50 text-white">{{ $isReturning ? 'Returning Student (OS)' : 'New Intake (NI)' }}</span>
                    </div>
                    <h2 class="fw-bold mb-1 text-white">Welcome back, {{ $student->first_name }} {{ $student->last_name }}!</h2>
                    <p class="mb-2 text-white-50 small">
                        <i class="fas fa-id-card me-1"></i> Admission No: <strong class="text-white">{{ $student->admission_no }}</strong>
                        @if($student->student_number && $student->student_number !== $student->admission_no)
                            | Student ID: <strong class="text-white">{{ $student->student_number }}</strong>
                        @endif
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-white text-dark shadow-sm">
                            <i class="fas fa-chalkboard me-1 text-primary"></i> {{ $student->currentClass ? $student->currentClass->class_name : 'Not Assigned' }}
                        </span>
                        <span class="badge bg-white text-dark shadow-sm">
                            <i class="fas fa-calendar-alt me-1 text-primary"></i> {{ $currentSession ? $currentSession->session_name : 'Current Session' }}
                        </span>
                        <span class="badge bg-white text-dark shadow-sm">
                            <i class="fas fa-clock me-1 text-primary"></i> {{ $currentTerm ? $currentTerm->term_name : 'Current Term' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-5 text-md-end mt-3 mt-md-0">
            <div class="d-flex flex-column flex-sm-row flex-md-column gap-2 justify-content-md-end">
                <a href="{{ route('student.results') }}" class="btn btn-warning text-dark fw-bold shadow-sm">
                    <i class="fas fa-award me-1"></i> View Academic Results
                </a>
                <a href="{{ route('student.payments') }}" class="btn btn-outline-light fw-bold shadow-sm">
                    <i class="fas fa-receipt me-1"></i> Fee Statement & Receipts
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Top KPI Cards -->
<div class="row g-3 mb-4">
    <!-- 1. Attendance Rate -->
    <div class="col-xl-3 col-sm-6">
        <div class="card kpi-card shadow-sm h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold text-uppercase">Attendance Rate</span>
                    <div class="kpi-icon-box" style="background: rgba(34, 197, 94, 0.12); color: #16a34a;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-1">
                    <h3 class="fw-bold mb-0 text-dark">{{ $attendancePercentage }}%</h3>
                    <span class="badge bg-{{ $attendancePercentage >= 75 ? 'success' : ($attendancePercentage >= 50 ? 'warning' : 'danger') }}-subtle text-{{ $attendancePercentage >= 75 ? 'success' : ($attendancePercentage >= 50 ? 'warning' : 'danger') }} border" style="font-size: 11px;">
                        {{ $attendancePercentage >= 75 ? 'Excellent' : ($attendancePercentage >= 50 ? 'Average' : 'Low') }}
                    </span>
                </div>
                <div class="progress mb-2" style="height: 6px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $attendancePercentage }}%;" aria-valuenow="{{ $attendancePercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="small text-muted d-flex justify-content-between">
                    <span>Present: <strong>{{ $daysPresent }}</strong> days</span>
                    <span>Total: <strong>{{ $totalAttendanceDays }}</strong> days</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Academic Performance -->
    <div class="col-xl-3 col-sm-6">
        <div class="card kpi-card shadow-sm h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold text-uppercase">Academic Average</span>
                    <div class="kpi-icon-box" style="background: rgba(14, 165, 233, 0.12); color: #0284c7;">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                </div>
                @php
                    $avgGradeBadge = match(true) {
                        $gradeAverage >= 75 => ['A', 'success'],
                        $gradeAverage >= 65 => ['B', 'primary'],
                        $gradeAverage >= 50 => ['C', 'info'],
                        $gradeAverage >= 40 => ['D', 'warning'],
                        default => ['E/F', 'danger']
                    };
                @endphp
                <div class="d-flex align-items-baseline gap-2 mb-1">
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($gradeAverage, 1) }}%</h3>
                    <span class="badge bg-{{ $avgGradeBadge[1] }} text-white px-2" style="font-size: 11px;">
                        Grade {{ $avgGradeBadge[0] }}
                    </span>
                </div>
                <div class="progress mb-2" style="height: 6px;">
                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ min(100, $gradeAverage) }}%;" aria-valuenow="{{ $gradeAverage }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="small text-muted d-flex justify-content-between">
                    <span>Assessments: <strong>{{ count($recentResults) }}</strong></span>
                    <a href="{{ route('student.results') }}" class="text-decoration-none small text-primary fw-bold">Result Slips &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. School Fee Status -->
    <div class="col-xl-3 col-sm-6">
        <div class="card kpi-card shadow-sm h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold text-uppercase">Fees & Balance</span>
                    <div class="kpi-icon-box" style="background: rgba(114, 9, 34, 0.12); color: var(--brand-primary);">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-1">
                    <h3 class="fw-bold mb-0 text-dark">₦{{ number_format($totalPaid, 0) }}</h3>
                    <span class="badge bg-{{ $feeStatus === 'cleared' ? 'success' : ($feeStatus === 'partial' ? 'warning text-dark' : 'danger') }} px-2" style="font-size: 11px;">
                        {{ strtoupper($feeStatus) }}
                    </span>
                </div>
                <div class="progress mb-2" style="height: 6px;">
                    <div class="progress-bar bg-{{ $feeStatus === 'cleared' ? 'success' : ($feeStatus === 'partial' ? 'warning' : 'danger') }}" role="progressbar" style="width: {{ $feePaymentProgress }}%;" aria-valuenow="{{ $feePaymentProgress }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="small text-muted d-flex justify-content-between">
                    <span>Owed: <strong class="text-{{ $pendingFees > 0 ? 'danger' : 'success' }}">₦{{ number_format($pendingFees, 2) }}</strong></span>
                    <span>Total: ₦{{ number_format($totalFees, 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Registered Subjects -->
    <div class="col-xl-3 col-sm-6">
        <div class="card kpi-card shadow-sm h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold text-uppercase">Curriculum</span>
                    <div class="kpi-icon-box" style="background: rgba(245, 158, 11, 0.15); color: #b45309;">
                        <i class="fas fa-book-reader"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-1">
                    <h3 class="fw-bold mb-0 text-dark">{{ $totalSubjectsCount }}</h3>
                    <span class="badge bg-warning-subtle text-dark border border-warning" style="font-size: 11px;">
                        Active Subjects
                    </span>
                </div>
                <div class="progress mb-2" style="height: 6px;">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: 100%;"></div>
                </div>
                <div class="small text-muted d-flex justify-content-between">
                    <span>Class: <strong>{{ $student->currentClass ? $student->currentClass->class_name : 'Assigned' }}</strong></span>
                    <span class="text-muted">Core & Electives</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="row g-4">
    <!-- Left Column: Academic Results & Fee Allocations (8 Cols) -->
    <div class="col-lg-8">
        <!-- 1. Recent Academic Results Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0 fw-bold text-dark">
                    <i class="fas fa-award text-warning me-2"></i>Recent Examination & Assessment Results
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border">{{ count($recentResults) }} Record(s)</span>
                    <a href="{{ route('student.results') }}" class="btn btn-outline-primary btn-sm fw-bold">
                        Full Results Sheet <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                @if(empty($recentResults))
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-clipboard-list fa-3x mb-3 text-secondary opacity-50"></i>
                        <h6 class="fw-bold text-dark mb-1">No Academic Results Published Yet</h6>
                        <p class="small text-muted mb-0">Your exam, test, and continuous assessment scores will appear here once teachers record them.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Subject</th>
                                    <th>Term</th>
                                    <th>Assessment</th>
                                    <th>Score / Max</th>
                                    <th>Grade</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentResults as $res)
                                    @php
                                        $sScore = (float)($res['score'] ?? 0);
                                        $sMax = (float)($res['max_score'] ?? 100);
                                        $sPct = isset($res['percentage']) ? (float)$res['percentage'] : ($sMax > 0 ? round(($sScore / $sMax) * 100, 1) : 0);
                                        $sGrade = !empty($res['letter_grade']) ? strtoupper((string)$res['letter_grade']) : (!empty($res['grade']) ? strtoupper((string)$res['grade']) : nigerian_grade_from_score($sScore));
                                        $gradeClass = match($sGrade) {
                                            'A', 'A1' => 'bg-success text-white',
                                            'B', 'B2', 'B3' => 'bg-primary text-white',
                                            'C', 'C4', 'C5', 'C6' => 'bg-info text-dark',
                                            'D', 'D7', 'E', 'E8' => 'bg-warning text-dark',
                                            default => 'bg-danger text-white'
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong class="text-dark">{{ $res['subject_name'] ?? 'Subject' }}</strong>
                                            @if(!empty($res['subject_code']))
                                                <small class="text-muted d-block" style="font-size: 11px;">{{ $res['subject_code'] }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ ucfirst($res['term_name'] ?? 'Current') }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary text-white">{{ ucfirst($res['assessment_name'] ?? $res['assessment_type'] ?? 'Exam') }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ number_format($sScore, 1) }} <span class="text-muted fw-normal" style="font-size: 11px;">/ {{ number_format($sMax, 0) }}</span></div>
                                            <div class="progress mt-1" style="height: 4px; width: 70px;">
                                                <div class="progress-bar bg-{{ $sPct >= 50 ? 'success' : 'danger' }}" style="width: {{ $sPct }}%;"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $gradeClass }} px-2 py-1">{{ $sGrade }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $res['comments'] ?? $res['remarks'] ?? '—' }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- 2. Allocated School Fees Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fas fa-file-invoice-dollar text-success me-2"></i>Allocated School Fees & Tuition
                    </h5>
                    <small class="text-muted">Official fee schedule allocated to your class and category.</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-{{ $feeStatus === 'cleared' ? 'success' : 'danger' }} px-3 py-2 fw-bold">
                        {{ $feeStatus === 'cleared' ? 'All Cleared' : 'Balance: ₦' . number_format($pendingFees, 2) }}
                    </span>
                    <a href="{{ route('student.payments') }}" class="btn btn-outline-success btn-sm fw-bold">
                        Pay / History <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                @if($studentFees->isEmpty() && $additionalCharges->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-50"></i>
                        <h6 class="fw-bold text-dark mb-1">No Pending Fee Allocations</h6>
                        <p class="small text-muted mb-0">There are no fee structures currently assigned to your profile for this term.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fee Structure</th>
                                    <th>Session / Term</th>
                                    <th>Amount Due</th>
                                    <th>Paid</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($studentFees as $sf)
                                    @php
                                        $fDue = (float)($sf->base_amount > 0.001 ? $sf->base_amount : $sf->amount_due);
                                        $fPaid = (float)$sf->amount_paid;
                                        $fBal = max(0.00, $fDue - $fPaid);
                                        $fStatus = ($fBal <= 0.001) ? 'paid' : ($fPaid > 0 ? 'partial' : 'unpaid');
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong class="text-dark">{{ $sf->feeStructure?->name ?? $sf->feeStructure?->description ?? 'School Tuition Fee' }}</strong>
                                            @if($sf->feeStructure?->description && $sf->feeStructure?->description !== $sf->feeStructure?->name)
                                                <small class="text-muted d-block" style="font-size: 11px;">{{ $sf->feeStructure->description }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $sf->session?->session_name ?? 'Current' }}
                                                @if($sf->term) ({{ $sf->term->term_name }}) @endif
                                            </small>
                                        </td>
                                        <td class="fw-bold text-dark">₦{{ number_format($fDue, 2) }}</td>
                                        <td class="fw-bold text-success">₦{{ number_format($fPaid, 2) }}</td>
                                        <td class="fw-bold text-{{ $fBal > 0 ? 'danger' : 'muted' }}">₦{{ number_format($fBal, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $fStatus === 'paid' ? 'success' : ($fStatus === 'partial' ? 'warning text-dark' : 'danger') }} px-2 py-1">
                                                {{ ucfirst($fStatus) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach

                                @foreach($additionalCharges as $ac)
                                    @php
                                        $cDue = (float)$ac->amount;
                                        $cPaid = (float)$ac->amount_paid;
                                        $cBal = max(0.00, $cDue - $cPaid);
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong class="text-dark">{{ $ac->fee_name }}</strong>
                                            <span class="badge bg-warning text-dark ms-1" style="font-size: 10px;">Extra Fee</span>
                                            @if($ac->reason)
                                                <small class="text-muted d-block" style="font-size: 11px;">{{ $ac->reason }}</small>
                                            @endif
                                        </td>
                                        <td><small class="text-muted">Special Charge</small></td>
                                        <td class="fw-bold text-dark">₦{{ number_format($cDue, 2) }}</td>
                                        <td class="fw-bold text-success">₦{{ number_format($cPaid, 2) }}</td>
                                        <td class="fw-bold text-{{ $cBal > 0 ? 'danger' : 'muted' }}">₦{{ number_format($cBal, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $cBal <= 0.001 ? 'success' : ($cPaid > 0 ? 'warning text-dark' : 'danger') }} px-2 py-1">
                                                {{ $cBal <= 0.001 ? 'Paid' : ($cPaid > 0 ? 'Partial' : 'Unpaid') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            @if(!empty($recentPayments) && $recentPayments->count() > 0)
                <div class="card-footer bg-light py-2 px-3 border-top d-flex justify-content-between align-items-center flex-wrap">
                    <small class="text-muted">
                        <i class="fas fa-check-circle text-success me-1"></i> Latest payment of <strong>₦{{ number_format((float)$recentPayments->first()->amount, 2) }}</strong> received on {{ !empty($recentPayments->first()->payment_date) ? date('M d, Y', strtotime($recentPayments->first()->payment_date)) : $recentPayments->first()->created_at->format('M d, Y') }}.
                    </small>
                    <a href="{{ route('student.receipts') }}" class="btn btn-sm btn-link text-primary fw-bold p-0 text-decoration-none">
                        Print Receipts <i class="fas fa-print ms-1"></i>
                    </a>
                </div>
            @endif
        </div>

        <!-- 3. Recent Attendance Activity -->
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0 fw-bold text-dark">
                    <i class="fas fa-calendar-check text-primary me-2"></i>Recent Attendance Activity
                </h5>
                <a href="{{ url('/student/attendance.php') }}" class="btn btn-outline-primary btn-sm fw-bold">
                    View Complete Log <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                @if(empty($recentAttendance))
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-calendar-times fa-2x mb-2 text-muted opacity-50"></i>
                        <p class="small text-muted mb-0">No daily attendance records logged yet for this academic period.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Session / Term</th>
                                    <th>Status</th>
                                    <th>Arrival Time</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentAttendance as $att)
                                    @php
                                        $attSt = strtolower((string)($att['status'] ?? 'present'));
                                        $attBadge = match($attSt) {
                                            'present' => 'bg-success text-white',
                                            'absent' => 'bg-danger text-white',
                                            'late' => 'bg-warning text-dark',
                                            'excused', 'sick', 'holiday' => 'bg-info text-dark',
                                            default => 'bg-secondary text-white'
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ !empty($att['date']) ? date('M d, Y', strtotime((string)$att['date'])) : '—' }}</strong>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ !empty($att['date']) ? date('l', strtotime((string)$att['date'])) : '' }}</small>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $att['session_name'] ?? 'Current' }} @if(!empty($att['term_name'])) ({{ $att['term_name'] }}) @endif</small>
                                        </td>
                                        <td>
                                            <span class="badge {{ $attBadge }} px-2 py-1">{{ ucfirst($attSt) }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $att['arrival_time'] ?? '—' }}</small>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $att['remarks'] ?? '—' }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Shortcuts, Profile & Announcements (4 Cols) -->
    <div class="col-lg-4">
        <!-- 1. Quick Actions Menu -->
        <div class="card shadow-sm mb-4 border-0" style="background: #ffffff;">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0 fw-bold text-dark">
                    <i class="fas fa-bolt text-warning me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body p-3">
                <a href="{{ route('student.results') }}" class="quick-action-link">
                    <i class="fas fa-award text-warning"></i>
                    <span class="flex-grow-1">My Academic Results</span>
                    <i class="fas fa-chevron-right text-muted small"></i>
                </a>

                <a href="{{ route('student.payments') }}" class="quick-action-link">
                    <i class="fas fa-wallet text-success"></i>
                    <span class="flex-grow-1">School Fees & Payment</span>
                    <i class="fas fa-chevron-right text-muted small"></i>
                </a>

                <a href="{{ route('student.receipts') }}" class="quick-action-link">
                    <i class="fas fa-receipt text-info"></i>
                    <span class="flex-grow-1">Payment Receipts</span>
                    <i class="fas fa-chevron-right text-muted small"></i>
                </a>

                <a href="{{ url('/student/attendance.php') }}" class="quick-action-link">
                    <i class="fas fa-calendar-check text-success"></i>
                    <span class="flex-grow-1">Attendance Log</span>
                    <i class="fas fa-chevron-right text-muted small"></i>
                </a>

                <a href="{{ route('student.profile') }}" class="quick-action-link">
                    <i class="fas fa-user-graduate text-primary"></i>
                    <span class="flex-grow-1">My Profile & Bio</span>
                    <i class="fas fa-chevron-right text-muted small"></i>
                </a>

                <a href="{{ route('student.complaints') }}" class="quick-action-link">
                    <i class="fas fa-comment-dots text-danger"></i>
                    <span class="flex-grow-1">Submit Helpdesk Inquiry</span>
                    <i class="fas fa-chevron-right text-muted small"></i>
                </a>

                <a href="{{ route('student.progression') }}" class="quick-action-link">
                    <i class="fas fa-graduation-cap text-info"></i>
                    <span class="flex-grow-1">Class Progression Check</span>
                    <i class="fas fa-chevron-right text-muted small"></i>
                </a>
            </div>
        </div>

        <!-- 2. Academic Profile Details Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0 fw-bold text-dark">
                    <i class="fas fa-user-graduate text-primary me-2"></i>Student Details
                </h5>
            </div>
            <div class="card-body p-3">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 45%;">Admission No:</td>
                        <td><code>{{ $student->admission_no }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Current Class:</td>
                        <td><strong>{{ $student->currentClass ? $student->currentClass->class_name : 'Not Assigned' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Student Type:</td>
                        <td>
                            <span class="badge bg-{{ strtolower($student->student_type ?? 'day') === 'boarding' ? 'info' : 'secondary' }} text-white">
                                {{ ucfirst($student->student_type ?? 'Day') }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Intake Status:</td>
                        <td>
                            <span class="badge bg-{{ $isReturning ? 'primary' : 'warning text-dark' }}">
                                {{ $isReturning ? 'Returning (OS)' : 'New Intake (NI)' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Gender:</td>
                        <td>{{ ucfirst($student->gender ?? 'N/A') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">State of Origin:</td>
                        <td>{{ $student->state_of_origin ?: 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date of Birth:</td>
                        <td>{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('M d, Y') : 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- 3. School Notices & Announcements -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold text-dark">
                    <i class="fas fa-bullhorn text-danger me-2"></i>Notice Board
                </h5>
                <a href="{{ url('/student/announcements.php') }}" class="small text-decoration-none">All Notices &rarr;</a>
            </div>
            <div class="card-body p-3">
                @if(empty($announcements))
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-bell-slash fa-2x mb-2 text-muted opacity-50"></i>
                        <p class="small text-muted mb-0">No active school announcements posted at this time.</p>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach(array_slice($announcements, 0, 4) as $ann)
                            <div class="list-group-item px-0 py-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-dark small">{{ $ann['title'] ?? 'Notice' }}</strong>
                                    <small class="text-muted" style="font-size: 10px;">{{ !empty($ann['publish_date'] ?? $ann['created_at']) ? date('M d', strtotime($ann['publish_date'] ?? $ann['created_at'])) : '' }}</small>
                                </div>
                                <p class="text-secondary small mb-0 text-truncate" style="font-size: 11px;">
                                    {{ Str::limit(strip_tags($ann['content'] ?? $ann['message'] ?? ''), 80) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection