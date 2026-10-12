<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid']) && !auth()->check() && php_sapi_name() !== 'cli') {
    header('Location: /admin/login.php');
    exit;
}

// Fetch School Settings
$schoolSettings = class_exists('\App\Models\SchoolSetting') ? \App\Models\SchoolSetting::first() : null;
$schoolName = $schoolSettings->school_name ?? 'Folu International Schools';
$schoolAddress = $schoolSettings->school_address ?? 'P.O Box 37, Itedo-Ijowa, Isanlu Kogi State Nigeria';
$schoolPhone = $schoolSettings->school_phone ?? '08165354191';
$schoolLogo = !empty($schoolSettings->school_logo) 
    ? (str_starts_with($schoolSettings->school_logo, '/') ? $schoolSettings->school_logo : '/storage/' . $schoolSettings->school_logo)
    : '/images/folu-logo.png';

// Sessions & Terms
$sessions = QueryDB('SELECT * FROM academic_sessions ORDER BY id DESC')->fetchAll();
$currentSession = get_current_academic_session();
$selectedSessionId = (int) ($_GET['academic_session_link'] ?? ($currentSession['id'] ?? ($sessions[0]['id'] ?? 0)));

$allTerms = [];
if (schema_has_table('academic_terms')) {
    $allTerms = QueryDB('SELECT * FROM academic_terms ORDER BY academic_session_link DESC, term_code ASC, id ASC')->fetchAll();
}
$termsForSelectedSession = array_values(array_filter($allTerms, function($t) use ($selectedSessionId) {
    return (int) ($t['academic_session_link'] ?? 0) === $selectedSessionId;
}));

$currentTerm = get_current_academic_term($selectedSessionId);
$defaultTermId = $currentTerm['id'] ?? ($termsForSelectedSession[0]['id'] ?? 0);
$selectedTermId = (int) ($_GET['term_link'] ?? $defaultTermId);

// Classes
$classes = QueryDB('SELECT * FROM classes ORDER BY class_level, class_name, class_arm')->fetchAll();
$selectedClassId = (int) ($_GET['class_link'] ?? ($classes[0]['id'] ?? 0));

$selectedClass = null;
if ($selectedClassId > 0) {
    $selectedClass = QueryDB('SELECT * FROM classes WHERE id = ? LIMIT 1', [$selectedClassId])->fetch(PDO::FETCH_ASSOC);
}

// Subjects
$subjects = [];
if ($selectedClass) {
    $subjects = QueryDB(
        "SELECT * FROM subjects WHERE class_level = ? OR class_level = 'ALL' ORDER BY subject_name",
        [$selectedClass['class_level']]
    )->fetchAll();
}
$selectedSubjectId = (int) ($_GET['subject_link'] ?? 0);

// Month and Year
$selectedMonth = (int) ($_GET['month'] ?? date('n'));
$selectedYear = (int) ($_GET['year'] ?? date('Y'));
if ($selectedMonth < 1 || $selectedMonth > 12) $selectedMonth = (int) date('n');
if ($selectedYear < 2020 || $selectedYear > 2035) $selectedYear = (int) date('Y');

// Month properties
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);
$startDate = sprintf('%04d-%02d-01', $selectedYear, $selectedMonth);
$endDate = sprintf('%04d-%02d-%02d', $selectedYear, $selectedMonth, $daysInMonth);
$monthName = date('F', mktime(0, 0, 0, $selectedMonth, 10));

// Navigation dates
$prevMonthTime = strtotime("-1 month", strtotime($startDate));
$prevMonth = date('n', $prevMonthTime);
$prevYear = date('Y', $prevMonthTime);

$nextMonthTime = strtotime("+1 month", strtotime($startDate));
$nextMonth = date('n', $nextMonthTime);
$nextYear = date('Y', $nextMonthTime);

// Fetch Students enrolled in class
$students = [];
if ($selectedClassId > 0) {
    $students = QueryDB(
        "SELECT s.* FROM students s
         WHERE (s.current_class_id = ? OR s.class_link = ?)
           AND (? = 0 OR s.current_session_id = ? OR s.academic_session_link = ?)
           AND (s.status = 'active' OR s.status IS NULL)
         ORDER BY s.last_name ASC, s.first_name ASC",
        [$selectedClassId, $selectedClassId, $selectedSessionId, $selectedSessionId, $selectedSessionId]
    )->fetchAll();

    if (empty($students)) {
        $students = QueryDB(
            "SELECT s.* FROM students s
             WHERE (s.current_class_id = ? OR s.class_link = ?)
               AND (s.status = 'active' OR s.status IS NULL)
             ORDER BY s.last_name ASC, s.first_name ASC",
            [$selectedClassId, $selectedClassId]
        )->fetchAll();
    }
}

// Fetch Attendance records for the entire month
$attendanceMatrix = [];
$subjectFilter = $selectedSubjectId > 0 ? $selectedSubjectId : null;
$daysWithAttendance = [];

if ($selectedClassId > 0) {
    if (schema_has_table('student_attendance')) {
        $records = QueryDB(
            'SELECT 
                student_id AS student_link,
                DAY(COALESCE(date, attendance_date)) AS day_num,
                COALESCE(date, attendance_date) AS att_date,
                status,
                COALESCE(remarks, reason) AS remarks
             FROM student_attendance
             WHERE class_id = ?
               AND COALESCE(date, attendance_date) BETWEEN ? AND ?
               AND ((subject_id IS NULL AND ? IS NULL) OR subject_id = ?)
               AND (? = 0 OR session_id IS NULL OR session_id = ?)',
            [$selectedClassId, $startDate, $endDate, $subjectFilter, $subjectFilter, $selectedSessionId, $selectedSessionId]
        )->fetchAll();

        foreach ($records as $r) {
            $sid = (int) $r['student_link'];
            $day = (int) $r['day_num'];
            $attendanceMatrix[$sid][$day] = [
                'status' => $r['status'],
                'remarks' => $r['remarks'],
                'date' => $r['att_date'],
            ];
            $daysWithAttendance[$day] = true;
        }
    } elseif (schema_has_table('attendance')) {
        $records = QueryDB(
            'SELECT 
                student_link,
                DAY(date) AS day_num,
                date AS att_date,
                status,
                remarks
             FROM attendance
             WHERE class_link = ?
               AND date BETWEEN ? AND ?
               AND ((subject_link IS NULL AND ? IS NULL) OR subject_link = ?)
               AND (? = 0 OR academic_session_link IS NULL OR academic_session_link = ?)',
            [$selectedClassId, $startDate, $endDate, $subjectFilter, $subjectFilter, $selectedSessionId, $selectedSessionId]
        )->fetchAll();

        foreach ($records as $r) {
            $sid = (int) $r['student_link'];
            $day = (int) $r['day_num'];
            $attendanceMatrix[$sid][$day] = [
                'status' => $r['status'],
                'remarks' => $r['remarks'],
                'date' => $r['att_date'],
            ];
            $daysWithAttendance[$day] = true;
        }
    }
}

// Compute aggregates & summaries
$totalSchoolDaysConducted = count($daysWithAttendance);
$grandPresent = 0;
$grandAbsent = 0;
$grandLate = 0;
$grandExcused = 0;
$studentStats = [];
$dailyStats = [];

for ($d = 1; $d <= $daysInMonth; $d++) {
    $dailyStats[$d] = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'total' => 0];
}

foreach ($students as $student) {
    $sid = (int) $student['id'];
    $p = 0; $a = 0; $l = 0; $e = 0;

    for ($d = 1; $d <= $daysInMonth; $d++) {
        if (isset($attendanceMatrix[$sid][$d])) {
            $st = $attendanceMatrix[$sid][$d]['status'];
            if ($st === 'present') { $p++; $dailyStats[$d]['present']++; }
            elseif ($st === 'absent') { $a++; $dailyStats[$d]['absent']++; }
            elseif ($st === 'late') { $l++; $dailyStats[$d]['late']++; }
            elseif ($st === 'excused') { $e++; $dailyStats[$d]['excused']++; }
            $dailyStats[$d]['total']++;
        }
    }

    $totalMarked = $p + $a + $l + $e;
    // Percentage: Present + Late counts towards attendance
    $rate = $totalMarked > 0 ? round((($p + $l) / $totalMarked) * 100) : 0;

    $studentStats[$sid] = [
        'present' => $p,
        'absent' => $a,
        'late' => $l,
        'excused' => $e,
        'total' => $totalMarked,
        'rate' => $rate,
    ];

    $grandPresent += $p;
    $grandAbsent += $a;
    $grandLate += $l;
    $grandExcused += $e;
}

$grandTotalMarks = $grandPresent + $grandAbsent + $grandLate + $grandExcused;
$overallClassRate = $grandTotalMarks > 0 ? round((($grandPresent + $grandLate) / $grandTotalMarks) * 100) : 0;

$selectedSessionName = '';
foreach ($sessions as $s) {
    if ((int)$s['id'] === $selectedSessionId) {
        $selectedSessionName = $s['session_name'];
        break;
    }
}

$selectedTermName = '';
foreach ($termsForSelectedSession as $t) {
    if ((int)$t['id'] === $selectedTermId) {
        $selectedTermName = $t['term_name'];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Broadsheet - <?php echo htmlspecialchars($monthName . ' ' . $selectedYear); ?> - <?php echo htmlspecialchars(trim(($selectedClass['class_name'] ?? 'Class') . ' ' . ($selectedClass['class_arm'] ?? ''))); ?></title>
    @include('admin.partials.links')
    
    <style>
        :root {
            --brand-primary: #720922;
            --brand-primary-dark: #500618;
            --brand-gold: #d97706;
            --color-present: #16a34a;
            --color-absent: #dc2626;
            --color-late: #d97706;
            --color-excused: #2563eb;
        }

        .broadsheet-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        .stat-badge-chip {
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        /* Broadsheet Calendar Table */
        .broadsheet-table-wrapper {
            position: relative;
            overflow-x: auto;
            max-width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
        }

        .broadsheet-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            font-size: 12px;
            text-align: center;
            background: #ffffff;
        }

        .broadsheet-table th, 
        .broadsheet-table td {
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 6px 3px;
            vertical-align: middle;
            white-space: nowrap;
        }

        /* Sticky Left Columns */
        .sticky-col-sn {
            position: sticky;
            left: 0;
            z-index: 2;
            background: #f8fafc;
            min-width: 40px;
            width: 40px;
            font-weight: bold;
        }
        .sticky-col-adm {
            position: sticky;
            left: 40px;
            z-index: 2;
            background: #f8fafc;
            min-width: 110px;
            font-family: monospace;
            font-size: 11px;
            text-align: left !important;
            padding-left: 6px !important;
        }
        .sticky-col-name {
            position: sticky;
            left: 150px;
            z-index: 2;
            background: #f8fafc;
            min-width: 190px;
            text-align: left !important;
            padding-left: 8px !important;
            font-weight: 600;
            box-shadow: 3px 0 6px -2px rgba(0,0,0,0.08);
        }

        thead .sticky-col-sn,
        thead .sticky-col-adm,
        thead .sticky-col-name {
            background: #0f172a !important;
            color: #ffffff;
            z-index: 4;
        }

        /* Sticky Header */
        .broadsheet-table thead th {
            background: #0f172a;
            color: #ffffff;
            font-size: 11px;
            padding: 6px 4px;
        }

        .day-header-cell {
            min-width: 28px;
            width: 28px;
            font-weight: 700;
        }

        .day-num {
            font-size: 12px;
            display: block;
        }

        .day-name {
            font-size: 9px;
            opacity: 0.8;
            text-transform: uppercase;
        }

        /* Weekend Highlighting */
        .is-weekend {
            background-color: #f1f5f9 !important;
            color: #94a3b8 !important;
        }
        .is-weekend-hdr {
            background-color: #334155 !important;
            color: #cbd5e1 !important;
        }

        /* Today Highlighting */
        .is-today-col {
            border-left: 1.5px solid var(--brand-primary) !important;
            border-right: 1.5px solid var(--brand-primary) !important;
        }
        .is-today-hdr {
            background-color: var(--brand-primary) !important;
            color: #ffffff !important;
        }

        /* Status Badges */
        .att-cell {
            font-weight: 800;
            font-size: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 4px;
        }

        .att-p {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }
        .att-a {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        .att-l {
            background-color: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        .att-e {
            background-color: #dbeafe;
            color: #1d4ed8;
            border: 1px solid #93c5fd;
        }
        .att-empty {
            color: #cbd5e1;
            font-size: 12px;
        }

        /* Right Summary Columns */
        .summary-col-hdr {
            background: #1e293b !important;
            color: #ffffff;
            min-width: 42px;
            font-size: 11px;
        }
        .summary-cell {
            font-weight: 700;
            font-size: 12px;
            background: #f8fafc;
        }

        .rate-badge {
            font-size: 11px;
            font-weight: 800;
            padding: 3px 6px;
            border-radius: 4px;
        }
        .rate-high { background-color: #dcfce7; color: #166534; }
        .rate-mid { background-color: #fef3c7; color: #92400e; }
        .rate-low { background-color: #fee2e2; color: #991b1b; }

        /* Printable Sheet Formatting */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                color: #000000 !important;
            }
            .sidebar, .main-header, .no-print, .btn, .filter-section {
                display: none !important;
            }
            .main-panel {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .container, .page-inner {
                padding: 0 !important;
                max-width: 100% !important;
            }
            .broadsheet-card {
                box-shadow: none !important;
                border: none !important;
            }
            .broadsheet-table-wrapper {
                border: 1px solid #94a3b8 !important;
                overflow: visible !important;
            }
            .print-header {
                display: block !important;
                margin-bottom: 12px;
            }
            .sticky-col-sn, .sticky-col-adm, .sticky-col-name {
                position: static !important;
                box-shadow: none !important;
            }
            .broadsheet-table th, .broadsheet-table td {
                padding: 3px 1px !important;
                font-size: 9px !important;
            }
            .att-cell {
                width: 16px !important;
                height: 16px !important;
                font-size: 8px !important;
                border-radius: 2px !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            @page {
                size: A4 landscape;
                margin: 6mm;
            }
        }

        .print-header {
            display: none;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="no-print">
            @include('admin.partials.sidebar')
        </div>

        <div class="main-panel">
            <div class="no-print">
                @include('admin.partials.header')
            </div>

            <div class="container">
                <div class="page-inner">

                    <!-- Official Print Header (Visible only when printed) -->
                    <div class="print-header text-center pb-3 border-bottom mb-3">
                        <h2 class="fw-bold mb-0" style="color: var(--brand-primary);"><?php echo htmlspecialchars($schoolName); ?></h2>
                        <p class="mb-1 text-muted" style="font-size: 11px;"><?php echo htmlspecialchars($schoolAddress); ?> | Tel: <?php echo htmlspecialchars($schoolPhone); ?></p>
                        <h4 class="fw-bold text-dark mt-2 mb-1">
                            STUDENT ATTENDANCE BROADSHEET REGISTER - <?php echo strtoupper($monthName . ' ' . $selectedYear); ?>
                        </h4>
                        <p class="mb-0 fw-semibold" style="font-size: 12px;">
                            Class: <u><?php echo htmlspecialchars(trim(($selectedClass['class_name'] ?? 'N/A') . ' ' . ($selectedClass['class_arm'] ?? ''))); ?></u>
                            &nbsp;|&nbsp;
                            Session: <u><?php echo htmlspecialchars($selectedSessionName ?: 'Current'); ?></u>
                            &nbsp;|&nbsp;
                            Term: <u><?php echo htmlspecialchars($selectedTermName ?: '1st Term'); ?></u>
                            &nbsp;|&nbsp;
                            Overall Attendance: <u><?php echo $overallClassRate; ?>%</u>
                        </p>
                    </div>

                    <!-- Page Actions & Header (Screen) -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap pb-3 no-print">
                        <div>
                            <h2 class="text-dark fw-bold mb-1">Monthly Attendance Broadsheet</h2>
                            <p class="text-muted mb-0">Calendar grid view displaying complete student attendance patterns and at-a-glance monthly totals.</p>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                            <button onclick="window.print();" class="btn btn-primary shadow-sm">
                                <i class="fas fa-print me-1"></i> Print Broadsheet
                            </button>
                            <button onclick="exportBroadsheetToCSV();" class="btn btn-outline-success">
                                <i class="fas fa-file-csv me-1"></i> Export to Excel
                            </button>
                            <a href="<?php echo url('/admin/attendance/mark.php') . '?' . http_build_query(['class_link' => $selectedClassId, 'academic_session_link' => $selectedSessionId, 'term_link' => $selectedTermId]); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-check-double me-1"></i> Mark Attendance
                            </a>
                        </div>
                    </div>

                    <!-- Filter & Month Controls -->
                    <div class="card broadsheet-card filter-section mb-4 no-print">
                        <div class="card-body">
                            <form method="GET" class="row g-3 align-items-end" id="broadsheetFilterForm">
                                <!-- Academic Session -->
                                <div class="col-md-2 col-sm-6">
                                    <label for="academic_session_link" class="form-label fw-bold text-dark small">Session</label>
                                    <select class="form-select form-control" id="academic_session_link" name="academic_session_link" onchange="updateTermsDropdown(this.value)">
                                        <?php foreach ($sessions as $session): ?>
                                            <option value="<?php echo (int) $session['id']; ?>" <?php echo $selectedSessionId === (int) $session['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $session['session_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Term -->
                                <div class="col-md-2 col-sm-6">
                                    <label for="term_link" class="form-label fw-bold text-dark small">Term</label>
                                    <select class="form-select form-control" id="term_link" name="term_link">
                                        <?php if (!empty($termsForSelectedSession)): ?>
                                            <?php foreach ($termsForSelectedSession as $t): ?>
                                                <option value="<?php echo (int) $t['id']; ?>" <?php echo $selectedTermId === (int) $t['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars((string) $t['term_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="1" <?php echo $selectedTermId === 1 ? 'selected' : ''; ?>>1st Term</option>
                                            <option value="2" <?php echo $selectedTermId === 2 ? 'selected' : ''; ?>>2nd Term</option>
                                            <option value="3" <?php echo $selectedTermId === 3 ? 'selected' : ''; ?>>3rd Term</option>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <!-- Class Selection -->
                                <div class="col-md-2 col-sm-6">
                                    <label for="class_link" class="form-label fw-bold text-dark small">Class / Arm</label>
                                    <select class="form-select form-control" id="class_link" name="class_link" required>
                                        <?php foreach ($classes as $cls): ?>
                                            <option value="<?php echo (int) $cls['id']; ?>" <?php echo $selectedClassId === (int) $cls['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars(trim(($cls['class_name'] ?? '') . ' ' . ($cls['class_arm'] ?? ''))); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Month Selection -->
                                <div class="col-md-2 col-sm-6">
                                    <label for="month" class="form-label fw-bold text-dark small">Month</label>
                                    <select class="form-select form-control" id="month" name="month">
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <option value="<?php echo $m; ?>" <?php echo $selectedMonth === $m ? 'selected' : ''; ?>>
                                                <?php echo date('F', mktime(0, 0, 0, $m, 10)); ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <!-- Year Selection -->
                                <div class="col-md-2 col-sm-6">
                                    <label for="year" class="form-label fw-bold text-dark small">Year</label>
                                    <select class="form-select form-control" id="year" name="year">
                                        <?php for ($y = (int)date('Y') - 2; $y <= (int)date('Y') + 2; $y++): ?>
                                            <option value="<?php echo $y; ?>" <?php echo $selectedYear === $y ? 'selected' : ''; ?>>
                                                <?php echo $y; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <!-- Filter Submit Button -->
                                <div class="col-md-2 col-sm-12 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                                        <i class="fas fa-filter me-1"></i> Load Sheet
                                    </button>
                                </div>
                            </form>

                            <!-- Month Quick Prev / Next Navigator -->
                            <div class="d-flex align-items-center justify-content-between flex-wrap pt-3 mt-3 border-top">
                                <div class="d-flex align-items-center gap-2">
                                    <a href="<?php echo '?class_link='.$selectedClassId.'&academic_session_link='.$selectedSessionId.'&term_link='.$selectedTermId.'&month='.$prevMonth.'&year='.$prevYear; ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-chevron-left me-1"></i> <?php echo date('M Y', $prevMonthTime); ?>
                                    </a>
                                    <h5 class="fw-bold text-dark mb-0 mx-2">
                                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                                        <?php echo $monthName . ' ' . $selectedYear; ?>
                                    </h5>
                                    <a href="<?php echo '?class_link='.$selectedClassId.'&academic_session_link='.$selectedSessionId.'&term_link='.$selectedTermId.'&month='.$nextMonth.'&year='.$nextYear; ?>" class="btn btn-sm btn-outline-secondary">
                                        <?php echo date('M Y', $nextMonthTime); ?> <i class="fas fa-chevron-right ms-1"></i>
                                    </a>
                                </div>

                                <!-- Quick Live Filter Search -->
                                <div class="mt-2 mt-md-0" style="max-width: 250px;">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                        <input type="text" id="studentSearchInput" class="form-control" placeholder="Search student name..." onkeyup="filterStudentRows(this.value)">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Summary KPI Chips -->
                    <div class="row g-2 mb-3 no-print">
                        <div class="col-6 col-md-2">
                            <div class="p-2 bg-white rounded border text-center">
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 10px;">Enrolled Students</small>
                                <h4 class="fw-bold mb-0 text-dark"><?php echo count($students); ?></h4>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-2 bg-white rounded border text-center">
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 10px;">Days Recorded</small>
                                <h4 class="fw-bold mb-0 text-primary"><?php echo $totalSchoolDaysConducted; ?> / <?php echo $daysInMonth; ?></h4>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-2 bg-white rounded border text-center">
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 10px;">Avg Attendance</small>
                                <h4 class="fw-bold mb-0 text-success"><?php echo $overallClassRate; ?>%</h4>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-2 bg-white rounded border text-center">
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 10px;">Total Present</small>
                                <h4 class="fw-bold mb-0 text-success"><?php echo number_format($grandPresent); ?></h4>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-2 bg-white rounded border text-center">
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 10px;">Total Absent</small>
                                <h4 class="fw-bold mb-0 text-danger"><?php echo number_format($grandAbsent); ?></h4>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-2 bg-white rounded border text-center">
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 10px;">Total Late / Excused</small>
                                <h4 class="fw-bold mb-0 text-warning"><?php echo number_format($grandLate + $grandExcused); ?></h4>
                            </div>
                        </div>
                    </div>

                    <!-- Broadsheet Calendar Matrix Card -->
                    <div class="card broadsheet-card mb-4">
                        <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between flex-wrap py-2">
                            <div class="fw-bold text-dark">
                                Class: <span class="text-primary"><?php echo htmlspecialchars(trim(($selectedClass['class_name'] ?? 'Class') . ' ' . ($selectedClass['class_arm'] ?? ''))); ?></span>
                                &bull; Month: <span class="text-primary"><?php echo $monthName . ' ' . $selectedYear; ?></span>
                            </div>
                            <!-- Legend -->
                            <div class="d-flex align-items-center gap-2 flex-wrap" style="font-size: 11px;">
                                <span><span class="att-cell att-p" style="width: 16px; height: 16px; font-size: 9px;">P</span> Present</span>
                                <span><span class="att-cell att-a" style="width: 16px; height: 16px; font-size: 9px;">A</span> Absent</span>
                                <span><span class="att-cell att-l" style="width: 16px; height: 16px; font-size: 9px;">L</span> Late</span>
                                <span><span class="att-cell att-e" style="width: 16px; height: 16px; font-size: 9px;">E</span> Excused</span>
                                <span class="text-muted"><i class="fas fa-slash me-1"></i> Weekend</span>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <?php if (!empty($students)): ?>
                                <div class="broadsheet-table-wrapper">
                                    <table class="broadsheet-table" id="broadsheetMatrixTable">
                                        <thead>
                                            <tr>
                                                <th class="sticky-col-sn">S/N</th>
                                                <th class="sticky-col-adm">Adm No</th>
                                                <th class="sticky-col-name">Student Name</th>

                                                <!-- 1 to N Month Days -->
                                                <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                                                    <?php 
                                                        $timestamp = mktime(0, 0, 0, $selectedMonth, $d, $selectedYear);
                                                        $dayOfWeek = date('N', $timestamp); // 1 = Monday, 7 = Sunday
                                                        $isWeekend = ($dayOfWeek == 6 || $dayOfWeek == 7);
                                                        $dayShortName = substr(date('D', $timestamp), 0, 1);
                                                        $isToday = (date('Y-m-d', $timestamp) === date('Y-m-d'));
                                                    ?>
                                                    <th class="day-header-cell <?php echo $isWeekend ? 'is-weekend-hdr' : ''; ?> <?php echo $isToday ? 'is-today-hdr is-today-col' : ''; ?>" title="<?php echo date('l, M d, Y', $timestamp); ?>">
                                                        <span class="day-num"><?php echo $d; ?></span>
                                                        <span class="day-name"><?php echo $dayShortName; ?></span>
                                                    </th>
                                                <?php endfor; ?>

                                                <!-- Right Summary Headers -->
                                                <th class="summary-col-hdr" title="Total Days Present" style="background: #14532d !important;">P</th>
                                                <th class="summary-col-hdr" title="Total Days Absent" style="background: #7f1d1d !important;">A</th>
                                                <th class="summary-col-hdr" title="Total Days Late" style="background: #78350f !important;">L</th>
                                                <th class="summary-col-hdr" title="Total Days Excused" style="background: #1e3a8a !important;">E</th>
                                                <th class="summary-col-hdr" title="Attendance Percentage">%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $sn = 1; ?>
                                            <?php foreach ($students as $student): ?>
                                                <?php 
                                                    $sid = (int) $student['id'];
                                                    $stStats = $studentStats[$sid] ?? ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'rate' => 0];
                                                    $fullName = trim(($student['last_name'] ?? '') . ' ' . ($student['first_name'] ?? '') . ' ' . ($student['other_names'] ?? ''));
                                                    $rateClass = $stStats['rate'] >= 80 ? 'rate-high' : ($stStats['rate'] >= 60 ? 'rate-mid' : 'rate-low');
                                                ?>
                                                <tr class="broadsheet-row" data-name="<?php echo strtolower($fullName); ?>" data-adm="<?php echo strtolower($student['admission_no'] ?? ''); ?>">
                                                    <td class="sticky-col-sn"><?php echo $sn++; ?></td>
                                                    <td class="sticky-col-adm">
                                                        <span><?php echo htmlspecialchars((string) ($student['admission_no'] ?? 'N/A')); ?></span>
                                                    </td>
                                                    <td class="sticky-col-name">
                                                        <div class="text-truncate" style="max-width: 180px;" title="<?php echo htmlspecialchars($fullName); ?>">
                                                            <?php echo htmlspecialchars($fullName); ?>
                                                        </div>
                                                    </td>

                                                    <!-- Attendance Day Cells -->
                                                    <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                                                        <?php 
                                                            $timestamp = mktime(0, 0, 0, $selectedMonth, $d, $selectedYear);
                                                            $dayOfWeek = date('N', $timestamp);
                                                            $isWeekend = ($dayOfWeek == 6 || $dayOfWeek == 7);
                                                            $isToday = (date('Y-m-d', $timestamp) === date('Y-m-d'));
                                                            $record = $attendanceMatrix[$sid][$d] ?? null;
                                                        ?>
                                                        <td class="<?php echo $isWeekend ? 'is-weekend' : ''; ?> <?php echo $isToday ? 'is-today-col' : ''; ?>">
                                                            <?php if ($record): ?>
                                                                <?php 
                                                                    $status = $record['status'];
                                                                    $badgeClass = 'att-' . substr($status, 0, 1);
                                                                    $letter = strtoupper(substr($status, 0, 1));
                                                                    $tooltip = date('M d', $timestamp) . ': ' . ucfirst($status) . ($record['remarks'] ? ' (' . $record['remarks'] . ')' : '');
                                                                ?>
                                                                <span class="att-cell <?php echo $badgeClass; ?>" title="<?php echo htmlspecialchars($tooltip); ?>">
                                                                    <?php echo $letter; ?>
                                                                </span>
                                                            <?php elseif ($isWeekend): ?>
                                                                <span class="text-muted" style="font-size: 10px; opacity: 0.3;">/</span>
                                                            <?php else: ?>
                                                                <span class="att-empty">&bull;</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endfor; ?>

                                                    <!-- Student Totals -->
                                                    <td class="summary-cell text-success"><?php echo $stStats['present']; ?></td>
                                                    <td class="summary-cell text-danger"><?php echo $stStats['absent']; ?></td>
                                                    <td class="summary-cell text-warning"><?php echo $stStats['late']; ?></td>
                                                    <td class="summary-cell text-primary"><?php echo $stStats['excused']; ?></td>
                                                    <td class="summary-cell">
                                                        <span class="rate-badge <?php echo $rateClass; ?>">
                                                            <?php echo $stStats['rate']; ?>%
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <!-- Footer: Daily Totals -->
                                        <tfoot class="border-top">
                                            <tr style="background: #f8fafc; font-weight: 700;">
                                                <td colspan="3" class="text-end pe-3 fw-bold sticky-col-name" style="background: #f1f5f9;">
                                                    Daily Present Total:
                                                </td>
                                                <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                                                    <?php 
                                                        $timestamp = mktime(0, 0, 0, $selectedMonth, $d, $selectedYear);
                                                        $isWeekend = (date('N', $timestamp) >= 6);
                                                        $pCount = $dailyStats[$d]['present'];
                                                    ?>
                                                    <td class="<?php echo $isWeekend ? 'is-weekend' : ''; ?> text-success" style="font-size: 11px;">
                                                        <?php echo $pCount > 0 ? $pCount : ($isWeekend ? '' : '-'); ?>
                                                    </td>
                                                <?php endfor; ?>
                                                <td class="text-success fw-bold"><?php echo $grandPresent; ?></td>
                                                <td class="text-danger fw-bold"><?php echo $grandAbsent; ?></td>
                                                <td class="text-warning fw-bold"><?php echo $grandLate; ?></td>
                                                <td class="text-primary fw-bold"><?php echo $grandExcused; ?></td>
                                                <td class="fw-bold text-dark"><?php echo $overallClassRate; ?>%</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="p-5 text-center text-muted">
                                    <i class="fas fa-users-slash fa-3x mb-3 text-secondary"></i>
                                    <h5>No Enrolled Students Found</h5>
                                    <p class="mb-0">Please choose another class or academic session from the filter above.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

            <div class="no-print">
                @include('admin.partials.footer')
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        const allTermsBySession = <?php echo json_encode($allTerms); ?>;

        function updateTermsDropdown(sessionId) {
            sessionId = parseInt(sessionId, 10);
            const termSelect = document.getElementById('term_link');
            if (!termSelect) return;

            termSelect.innerHTML = '';
            const matchingTerms = allTermsBySession.filter(t => parseInt(t.academic_session_link, 10) === sessionId);

            if (matchingTerms.length > 0) {
                matchingTerms.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.id;
                    opt.textContent = t.term_name;
                    termSelect.appendChild(opt);
                });
            } else {
                const terms = [{id: 1, name: '1st Term'}, {id: 2, name: '2nd Term'}, {id: 3, name: '3rd Term'}];
                terms.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.id;
                    opt.textContent = t.name;
                    termSelect.appendChild(opt);
                });
            }
        }

        function filterStudentRows(query) {
            query = query.toLowerCase().trim();
            const rows = document.querySelectorAll('.broadsheet-row');
            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                const adm = row.getAttribute('data-adm') || '';
                if (name.includes(query) || adm.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Export broadsheet data to downloadable CSV file
        function exportBroadsheetToCSV() {
            const table = document.getElementById('broadsheetMatrixTable');
            if (!table) return;

            let csv = [];
            const rows = table.querySelectorAll('tr');

            rows.forEach(row => {
                let rowData = [];
                const cols = row.querySelectorAll('th, td');
                cols.forEach(col => {
                    let text = col.innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
                    text = text.replace(/"/g, '""');
                    rowData.push('"' + text + '"');
                });
                if (rowData.length > 0) {
                    csv.push(rowData.join(','));
                }
            });

            const csvContent = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv.join('\n'));
            const downloadLink = document.createElement('a');
            const fileName = 'Attendance_Broadsheet_<?php echo $monthName . '_' . $selectedYear; ?>.csv';
            downloadLink.setAttribute('href', csvContent);
            downloadLink.setAttribute('download', fileName);
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }
    </script>
</body>
</html>
