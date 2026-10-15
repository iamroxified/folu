@extends('student.layout')

@section('title', 'Academic Results - ' . ($student->first_name ?? 'Student'))

@section('content')
@php
    // Group grades or results by session and term
    $groupedResults = [];

    if (isset($grades) && $grades->count() > 0) {
        foreach ($grades as $g) {
            $sessName = !empty($g->academic_year) ? $g->academic_year : ($g->session?->session_name ?? 'Current Session');
            $termName = !empty($g->term) ? (is_string($g->term) ? $g->term : $g->term?->term_name) : 'Current Term';
            $key = $sessName . '|||' . $termName;
            if (!isset($groupedResults[$key])) {
                $groupedResults[$key] = [
                    'session' => $sessName,
                    'term' => $termName,
                    'items' => [],
                ];
            }
            $ca = (float)($g->ca1_score ?? 0) + (float)($g->ca2_score ?? 0);
            $groupedResults[$key]['items'][] = [
                'subject_name' => $g->subject?->subject_name ?? 'Subject',
                'subject_code' => $g->subject?->subject_code ?? 'SUB',
                'ca_score' => $ca > 0 ? $ca : null,
                'exam_score' => $g->exam_score !== null ? (float)$g->exam_score : null,
                'total_score' => (float)($g->percentage ?? ($g->total_score ?? ($g->score ?? 0))),
                'grade' => strtoupper((string)($g->letter_grade ?? ($g->grade ?? 'N/A'))),
                'remarks' => $g->comments ?? ($g->remarks ?? null),
            ];
        }
    } elseif (!empty($results) && is_array($results)) {
        foreach ($results as $r) {
            $sessName = $r['session_name'] ?? ($r['academic_year'] ?? 'Current Session');
            $termName = $r['term_name'] ?? ($r['term'] ?? 'Current Term');
            $key = $sessName . '|||' . $termName;
            if (!isset($groupedResults[$key])) {
                $groupedResults[$key] = [
                    'session' => $sessName,
                    'term' => $termName,
                    'items' => [],
                ];
            }
            $ca = (float)($r['ca1_score'] ?? 0) + (float)($r['ca2_score'] ?? 0);
            $groupedResults[$key]['items'][] = [
                'subject_name' => $r['subject_name'] ?? 'Subject',
                'subject_code' => $r['subject_code'] ?? 'SUB',
                'ca_score' => $ca > 0 ? $ca : null,
                'exam_score' => isset($r['exam_score']) && $r['exam_score'] !== null ? (float)$r['exam_score'] : null,
                'total_score' => (float)($r['total_score'] ?? $r['score'] ?? 0),
                'grade' => strtoupper((string)($r['grade'] ?? 'N/A')),
                'remarks' => $r['remarks'] ?? null,
            ];
        }
    }

    $avgBadgeClass = match(true) {
        $gradeAverage >= 75 => 'bg-success',
        $gradeAverage >= 60 => 'bg-primary',
        $gradeAverage >= 50 => 'bg-warning text-dark',
        default => 'bg-danger'
    };

    $avgGradeLetter = match(true) {
        $gradeAverage >= 80 => 'A',
        $gradeAverage >= 70 => 'B',
        $gradeAverage >= 60 => 'C',
        $gradeAverage >= 50 => 'D',
        default => 'F'
    };
@endphp

<!-- Page Header & Breadcrumbs -->
<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Academic Results & Performance</h3>
        <ul class="breadcrumbs ps-0 mb-0 d-flex align-items-center text-muted" style="list-style: none; font-size: 13px;">
            <li class="nav-home"><a href="{{ route('student.dashboard') }}" class="text-muted"><i class="fas fa-home"></i></a></li>
            <li class="separator mx-2"><i class="fas fa-chevron-right text-muted" style="font-size: 10px;"></i></li>
            <li class="nav-item text-dark fw-semibold">Examination Results</li>
        </ul>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary btn-round btn-sm px-3 shadow-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print Result Slip
        </button>
        <a href="{{ route('student.progression') }}" class="btn btn-primary btn-round btn-sm px-3 shadow-sm">
            <i class="fas fa-graduation-cap me-1"></i> Promotion Status
        </a>
    </div>
</div>

<!-- 4 Modern Academic KPI Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-stats card-round shadow-sm border-0 h-100">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-5">
                        <div class="icon-big text-center icon-primary bubble-shadow-small rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(114, 9, 34, 0.1); color: #720922;">
                            <i class="fas fa-calculator fa-lg"></i>
                        </div>
                    </div>
                    <div class="col-7 col-stats">
                        <div class="numbers">
                            <p class="card-category text-muted small mb-0">Grade Average</p>
                            <h4 class="card-title fw-bold text-dark mb-0">{{ number_format((float)$gradeAverage, 1) }}%</h4>
                            <span class="badge {{ $avgBadgeClass }} mt-1" style="font-size: 10px;">Grade: {{ $avgGradeLetter }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-stats card-round shadow-sm border-0 h-100">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-5">
                        <div class="icon-big text-center icon-warning bubble-shadow-small rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(255, 173, 0, 0.12); color: #ffad00;">
                            <i class="fas fa-award fa-lg"></i>
                        </div>
                    </div>
                    <div class="col-7 col-stats">
                        <div class="numbers">
                            <p class="card-category text-muted small mb-0">Academic Standing</p>
                            <h4 class="card-title fw-bold text-dark mb-0" style="font-size: 15px;">
                                {{ $gradeAverage >= 70 ? 'Distinction' : ($gradeAverage >= 50 ? 'Good Standing' : 'Pass Required') }}
                            </h4>
                            <span class="text-success small fw-semibold" style="font-size: 11px;">
                                <i class="fas fa-check-circle me-1"></i> Active Record
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-stats card-round shadow-sm border-0 h-100">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-5">
                        <div class="icon-big text-center icon-info bubble-shadow-small rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(23, 162, 184, 0.12); color: #17a2b8;">
                            <i class="fas fa-calendar-check fa-lg"></i>
                        </div>
                    </div>
                    <div class="col-7 col-stats">
                        <div class="numbers">
                            <p class="card-category text-muted small mb-0">Current Session</p>
                            <h4 class="card-title fw-bold text-dark mb-0" style="font-size: 14px;">{{ $currentSession ? $currentSession->session_name : '2026/2027' }}</h4>
                            <span class="badge bg-light text-muted border mt-1" style="font-size: 10px;">{{ $currentTerm ? $currentTerm->term_name : 'Current' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-stats card-round shadow-sm border-0 h-100">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-5">
                        <div class="icon-big text-center icon-success bubble-shadow-small rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(40, 167, 69, 0.12); color: #28a745;">
                            <i class="fas fa-user-graduate fa-lg"></i>
                        </div>
                    </div>
                    <div class="col-7 col-stats">
                        <div class="numbers">
                            <p class="card-category text-muted small mb-0">Registered Class</p>
                            <h4 class="card-title fw-bold text-dark mb-0" style="font-size: 14px;">{{ $student->currentClass ? $student->currentClass->class_name : 'Primary' }}</h4>
                            <span class="badge bg-warning-subtle text-dark border border-warning mt-1" style="font-size: 10px;">
                                Adm: {{ $student->admission_no ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(count($groupedResults) > 0)
    @foreach($groupedResults as $groupKey => $groupData)
        @php
            $items = $groupData['items'];
            $termAvg = count($items) > 0 ? (collect($items)->avg('total_score')) : 0;
            $termTotal = collect($items)->sum('total_score');
            $termMax = count($items) * 100;
            $termLetter = match(true) {
                $termAvg >= 80 => 'A',
                $termAvg >= 70 => 'B',
                $termAvg >= 60 => 'C',
                $termAvg >= 50 => 'D',
                default => 'F'
            };
        @endphp

        <div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle text-white p-2 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #720922, #8e1532); width: 36px; height: 36px;">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div>
                        <h5 class="card-title fw-bold mb-0 text-dark" style="font-size: 16px;">
                            {{ $groupData['session'] }} &bull; {{ $groupData['term'] }}
                        </h5>
                        <small class="text-muted">{{ count($items) }} Subjects Assessed</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border px-3 py-2 fw-semibold">
                        Term Average: <strong class="text-primary">{{ number_format($termAvg, 1) }}%</strong>
                    </span>
                    <span class="badge bg-primary px-3 py-2 fw-bold">
                        Overall Grade: {{ $termLetter }}
                    </span>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width: 50px;">#</th>
                                <th>Subject Curriculum</th>
                                <th class="text-center">CA Score (40%)</th>
                                <th class="text-center">Exam Score (60%)</th>
                                <th class="text-center">Total Score (100%)</th>
                                <th class="text-center">Grade</th>
                                <th>Teacher's Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $idx => $item)
                                @php
                                    $score = $item['total_score'];
                                    $grade = $item['grade'];
                                    $gradeBadge = match($grade) {
                                        'A' => 'bg-success',
                                        'B' => 'bg-primary',
                                        'C' => 'bg-info',
                                        'D' => 'bg-warning text-dark',
                                        default => 'bg-danger'
                                    };
                                    $progColor = match(true) {
                                        $score >= 75 => '#28a745',
                                        $score >= 60 => '#007bff',
                                        $score >= 50 => '#ffc107',
                                        default => '#dc3545'
                                    };
                                @endphp
                                <tr>
                                    <td class="ps-4 text-muted small">{{ $idx + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded p-1 px-2 bg-light border text-primary small fw-bold">
                                                {{ $item['subject_code'] }}
                                            </div>
                                            <span class="fw-bold text-dark">{{ $item['subject_name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center text-muted">
                                        {{ $item['ca_score'] !== null ? number_format($item['ca_score'], 1) : '-' }}
                                    </td>
                                    <td class="text-center text-muted">
                                        {{ $item['exam_score'] !== null ? number_format($item['exam_score'], 1) : '-' }}
                                    </td>
                                    <td class="text-center" style="min-width: 130px;">
                                        <div class="d-flex flex-column align-items-center">
                                            <span class="fw-bold fs-6 text-dark">{{ number_format($score, 1) }}%</span>
                                            <div class="progress w-75 mt-1" style="height: 4px;">
                                                <div class="progress-bar rounded" role="progressbar" style="width: {{ min(100, $score) }}%; background-color: {{ $progColor }};"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $gradeBadge }} px-3 py-1 fw-bold rounded-pill" style="font-size: 11px;">
                                            {{ $grade }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted small">
                                            {{ $item['remarks'] ?: ($score >= 75 ? 'Excellent performance' : ($score >= 50 ? 'Good effort, keep it up' : 'Needs improvement')) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light border-top">
                            <tr>
                                <td colspan="4" class="ps-4 fw-bold text-dark">
                                    Term Grand Total: <span class="text-primary">{{ number_format($termTotal, 1) }} / {{ $termMax }}</span>
                                </td>
                                <td class="text-center fw-bold text-dark">
                                    Avg: {{ number_format($termAvg, 1) }}%
                                </td>
                                <td class="text-center fw-bold">
                                    <span class="badge {{ $termAvg >= 50 ? 'bg-success' : 'bg-danger' }} px-3 py-1">
                                        {{ $termAvg >= 50 ? 'PASSED' : 'FAILED' }}
                                    </span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
@else
    <!-- Empty State -->
    <div class="card shadow-sm border-0 rounded-4 text-center py-5">
        <div class="card-body">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center p-4 mb-3" style="background: rgba(114, 9, 34, 0.08); width: 80px; height: 80px;">
                <i class="fas fa-file-invoice text-primary fa-2x"></i>
            </div>
            <h4 class="fw-bold text-dark mb-2">No Published Results Found</h4>
            <p class="text-muted mx-auto" style="max-width: 500px;">
                Examination scores and terminal reports will appear here once academic assessments have been finalized and published by your subject teachers and administration.
            </p>
            <a href="{{ route('student.dashboard') }}" class="btn btn-outline-primary btn-round px-4">
                <i class="fas fa-arrow-left me-1"></i> Return to Dashboard
            </a>
        </div>
    </div>
@endif

<!-- Grading Scale Guide -->
<div class="card shadow-sm border-0 rounded-4 mt-4">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="card-title fw-bold text-dark mb-0" style="font-size: 14px;">
            <i class="fas fa-info-circle text-primary me-2"></i> Standard School Grading Benchmark
        </h6>
    </div>
    <div class="card-body p-3">
        <div class="row g-2 text-center">
            <div class="col-sm-2 col-4">
                <div class="p-2 border rounded-3 bg-light">
                    <span class="badge bg-success px-2 py-1 mb-1">A (75 - 100%)</span>
                    <span class="d-block small text-muted">Distinction</span>
                </div>
            </div>
            <div class="col-sm-2 col-4">
                <div class="p-2 border rounded-3 bg-light">
                    <span class="badge bg-primary px-2 py-1 mb-1">B (65 - 74%)</span>
                    <span class="d-block small text-muted">Very Good</span>
                </div>
            </div>
            <div class="col-sm-2 col-4">
                <div class="p-2 border rounded-3 bg-light">
                    <span class="badge bg-info px-2 py-1 mb-1">C (50 - 64%)</span>
                    <span class="d-block small text-muted">Credit / Good</span>
                </div>
            </div>
            <div class="col-sm-2 col-4">
                <div class="p-2 border rounded-3 bg-light">
                    <span class="badge bg-warning text-dark px-2 py-1 mb-1">D (40 - 49%)</span>
                    <span class="d-block small text-muted">Pass</span>
                </div>
            </div>
            <div class="col-sm-2 col-4">
                <div class="p-2 border rounded-3 bg-light">
                    <span class="badge bg-danger px-2 py-1 mb-1">F (0 - 39%)</span>
                    <span class="d-block small text-muted">Fail</span>
                </div>
            </div>
            <div class="col-sm-2 col-4">
                <div class="p-2 border rounded-3 bg-light">
                    <span class="badge bg-secondary px-2 py-1 mb-1">Remarks</span>
                    <span class="d-block small text-muted">Promotion Basis</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection