@extends('student.layout')

@section('title', 'Academic Progression - ' . ($student->first_name ?? 'Student'))

@section('content')
@php
    $attendanceMet = $attendancePct >= 60;
    $gradeMet = $gradeAverage >= 50;
    $isPromoted = $canProgress;

    // Normalize grade items
    $gradeItems = [];
    if (isset($grades) && $grades->count() > 0) {
        foreach ($grades as $g) {
            $gradeItems[] = [
                'subject_name' => $g->subject?->subject_name ?? 'Subject',
                'subject_code' => $g->subject?->subject_code ?? 'SUB',
                'score' => (float)($g->total_score ?? $g->score ?? 0),
                'grade' => strtoupper((string)($g->grade ?? 'N/A')),
            ];
        }
    } elseif (!empty($results) && is_array($results)) {
        foreach ($results as $r) {
            $gradeItems[] = [
                'subject_name' => $r['subject_name'] ?? 'Subject',
                'subject_code' => $r['subject_code'] ?? 'SUB',
                'score' => (float)($r['total_score'] ?? $r['score'] ?? 0),
                'grade' => strtoupper((string)($r['grade'] ?? 'N/A')),
            ];
        }
    }
@endphp

<!-- Page Header & Breadcrumbs -->
<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Academic Progression & Promotion</h3>
        <ul class="breadcrumbs ps-0 mb-0 d-flex align-items-center text-muted" style="list-style: none; font-size: 13px;">
            <li class="nav-home"><a href="{{ route('student.dashboard') }}" class="text-muted"><i class="fas fa-home"></i></a></li>
            <li class="separator mx-2"><i class="fas fa-chevron-right text-muted" style="font-size: 10px;"></i></li>
            <li class="nav-item text-dark fw-semibold">Class Promotion Assessment</li>
        </ul>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('student.results') }}" class="btn btn-outline-secondary btn-round btn-sm px-3 shadow-sm">
            <i class="fas fa-award me-1"></i> Full Results Sheet
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Performance Table -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle text-white p-2 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #720922, #8e1532); width: 34px; height: 34px;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <h5 class="card-title fw-bold mb-0 text-dark" style="font-size: 16px;">
                            Academic Assessment Breakdown
                        </h5>
                        <small class="text-muted">{{ $currentSession ? $currentSession->session_name : 'Current Session' }} &bull; {{ $currentTerm ? $currentTerm->term_name : 'Current Term' }}</small>
                    </div>
                </div>
                <span class="badge bg-light text-dark border">{{ count($gradeItems) }} Subjects</span>
            </div>

            <div class="card-body p-0">
                @if(count($gradeItems) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Subject</th>
                                    <th class="text-center">Score (%)</th>
                                    <th class="text-center">Grade</th>
                                    <th class="text-center">Benchmark Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gradeItems as $item)
                                    @php
                                        $sc = $item['score'];
                                        $pass = $sc >= 50;
                                        $gBadge = match($item['grade']) {
                                            'A' => 'bg-success',
                                            'B' => 'bg-primary',
                                            'C' => 'bg-info',
                                            'D' => 'bg-warning text-dark',
                                            default => 'bg-danger'
                                        };
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-bold text-dark d-block">{{ $item['subject_name'] }}</span>
                                            <span class="text-muted small">Code: <code>{{ $item['subject_code'] }}</code></span>
                                        </td>
                                        <td class="text-center fw-bold fs-6 {{ $pass ? 'text-dark' : 'text-danger' }}">
                                            {{ number_format($sc, 1) }}%
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $gBadge }} px-3 py-1 rounded-pill" style="font-size: 11px;">
                                                {{ $item['grade'] }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $pass ? 'bg-success' : 'bg-danger' }} px-3 py-1 rounded-pill" style="font-size: 11px;">
                                                <i class="fas {{ $pass ? 'fa-check' : 'fa-times' }} me-1"></i>
                                                {{ $pass ? 'Passed' : 'Deficient' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-list text-muted fa-3x mb-3"></i>
                        <h6 class="fw-bold text-dark">No Terminal Scores Posted Yet</h6>
                        <p class="text-muted small mb-0">Examination results will be compiled here once entered by teachers.</p>
                    </div>
                @endif
            </div>

            <div class="card-footer bg-light p-3 border-top d-flex justify-content-between align-items-center">
                <span class="fw-semibold text-dark small">Cumulative Grade Point Average:</span>
                <span class="fw-bold fs-5 {{ $gradeMet ? 'text-success' : 'text-danger' }}">
                    {{ number_format((float)$gradeAverage, 1) }}%
                </span>
            </div>
        </div>
    </div>

    <!-- Right Column: Promotion Status & Criteria -->
    <div class="col-lg-4">
        <!-- Standing Card -->
        <div class="card shadow-sm border-0 rounded-4 mb-4 text-center overflow-hidden">
            <div class="p-4 {{ $isPromoted ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-dark border border-warning-subtle' }} rounded-top-4">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-2 {{ $isPromoted ? 'bg-success text-white' : 'bg-warning text-dark' }}" style="width: 60px; height: 60px;">
                    <i class="fas {{ $isPromoted ? 'fa-user-check' : 'fa-exclamation-triangle' }} fa-2x"></i>
                </div>
                <h5 class="fw-bold mb-1">
                    {{ $isPromoted ? 'Eligible for Class Promotion' : 'Promotion Criteria Pending' }}
                </h5>
                <p class="small mb-0 opacity-75">
                    {{ $isPromoted ? 'Academic and attendance records satisfy advancement standards.' : 'Academic or attendance threshold not yet reached.' }}
                </p>
            </div>

            <div class="card-body p-4 text-start">
                <h6 class="fw-bold text-dark mb-3" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">
                    Promotion Benchmarks
                </h6>

                <!-- Criterion 1: Academic Average -->
                <div class="d-flex align-items-center justify-content-between p-2 mb-2 rounded-3 border {{ $gradeMet ? 'bg-light' : 'bg-danger-subtle' }}">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas {{ $gradeMet ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' }} fa-lg"></i>
                        <div>
                            <span class="fw-bold text-dark d-block small">Academic Average &ge; 50%</span>
                            <span class="text-muted" style="font-size: 11px;">Current: {{ number_format((float)$gradeAverage, 1) }}%</span>
                        </div>
                    </div>
                    <span class="badge {{ $gradeMet ? 'bg-success' : 'bg-danger' }} px-2 py-1" style="font-size: 10px;">
                        {{ $gradeMet ? 'Met' : 'Pending' }}
                    </span>
                </div>

                <!-- Criterion 2: Attendance Rate -->
                <div class="d-flex align-items-center justify-content-between p-2 mb-2 rounded-3 border {{ $attendanceMet ? 'bg-light' : 'bg-warning-subtle' }}">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas {{ $attendanceMet ? 'fa-check-circle text-success' : 'fa-exclamation-circle text-warning' }} fa-lg"></i>
                        <div>
                            <span class="fw-bold text-dark d-block small">Attendance Rate &ge; 60%</span>
                            <span class="text-muted" style="font-size: 11px;">Current: {{ number_format((float)$attendancePct, 1) }}%</span>
                        </div>
                    </div>
                    <span class="badge {{ $attendanceMet ? 'bg-success' : 'bg-warning text-dark' }} px-2 py-1" style="font-size: 10px;">
                        {{ $attendanceMet ? 'Met' : 'Pending' }}
                    </span>
                </div>

                <!-- Criterion 3: Disciplinary Standing -->
                <div class="d-flex align-items-center justify-content-between p-2 rounded-3 border bg-light">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle text-success fa-lg"></i>
                        <div>
                            <span class="fw-bold text-dark d-block small">Disciplinary Clearance</span>
                            <span class="text-muted" style="font-size: 11px;">In Good Conduct</span>
                        </div>
                    </div>
                    <span class="badge bg-success px-2 py-1" style="font-size: 10px;">Met</span>
                </div>
            </div>
        </div>

        <!-- Next Step Advice Card -->
        <div class="card shadow-sm border-0 rounded-4 bg-light">
            <div class="card-body p-4">
                <h6 class="fw-bold text-dark mb-2" style="font-size: 13px;">
                    <i class="fas fa-info-circle text-primary me-2"></i> Promotion Policy Notice
                </h6>
                <p class="text-muted small mb-0" style="line-height: 1.6;">
                    Official promotions are confirmed at the conclusion of the 3rd Term based on cumulative session weighted performance. For questions, consult your academic guidance counselor.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection