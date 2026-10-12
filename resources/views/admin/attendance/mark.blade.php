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

$message = $_SESSION['flash_success'] ?? '';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Sessions and terms
$sessions = QueryDB('SELECT * FROM academic_sessions ORDER BY id DESC')->fetchAll();
$currentSession = get_current_academic_session();
$selectedSessionId = (int) ($_REQUEST['academic_session_link'] ?? ($currentSession['id'] ?? ($sessions[0]['id'] ?? 0)));

// Terms for all sessions for dynamic JavaScript switching
$allTerms = [];
if (schema_has_table('academic_terms')) {
    $allTerms = QueryDB('SELECT * FROM academic_terms ORDER BY academic_session_link DESC, term_code ASC, id ASC')->fetchAll();
}
$termsForSelectedSession = array_values(array_filter($allTerms, function($t) use ($selectedSessionId) {
    return (int) ($t['academic_session_link'] ?? 0) === $selectedSessionId;
}));

// Default selected term
$currentTerm = get_current_academic_term($selectedSessionId);
$defaultTermId = $currentTerm['id'] ?? ($termsForSelectedSession[0]['id'] ?? 0);
$selectedTermId = (int) ($_REQUEST['term_link'] ?? $defaultTermId);

// Class, Subject, Date
$classes = QueryDB('SELECT * FROM classes ORDER BY class_level, class_name, class_arm')->fetchAll();
$selectedClassId = (int) ($_REQUEST['class_link'] ?? 0);
$selectedSubjectId = (int) ($_REQUEST['subject_link'] ?? 0);
$selectedDate = validate($_REQUEST['attendance_date'] ?? date('Y-m-d'));

$selectedClass = null;
if ($selectedClassId > 0) {
    $selectedClass = QueryDB('SELECT * FROM classes WHERE id = ? LIMIT 1', [$selectedClassId])->fetch(PDO::FETCH_ASSOC);
}

$subjects = [];
if ($selectedClass) {
    $subjects = QueryDB(
        "SELECT * FROM subjects WHERE class_level = ? OR class_level = 'ALL' ORDER BY subject_name",
        [$selectedClass['class_level']]
    )->fetchAll();
}

// Handle POST: Save attendance with PRG redirect
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_attendance') {
    try {
        $selectedSessionId = (int) ($_POST['academic_session_link'] ?? $selectedSessionId);
        $selectedTermId = (int) ($_POST['term_link'] ?? $selectedTermId);
        $selectedClassId = (int) ($_POST['class_link'] ?? $selectedClassId);
        $selectedSubjectId = (int) ($_POST['subject_link'] ?? $selectedSubjectId);
        $selectedDate = validate($_POST['attendance_date'] ?? $selectedDate);

        if ($selectedSessionId < 1 || $selectedClassId < 1) {
            throw new Exception('Please select both an academic session and a class before saving attendance.');
        }

        $records = [];
        foreach (($_POST['attendance'] ?? []) as $studentId => $status) {
            $statusVal = validate((string) $status);
            if ($statusVal !== '') {
                $records[(int) $studentId] = [
                    'status' => $statusVal,
                    'remarks' => validate((string) ($_POST['remarks'][$studentId] ?? '')),
                ];
            }
        }

        if (empty($records)) {
            throw new Exception('Please mark attendance for at least one student.');
        }

        $markedBy = (int) ($_SESSION['adid'] ?? auth()->id() ?? 1);
        $saved = save_bulk_attendance_records(
            $selectedClassId,
            $selectedSubjectId > 0 ? $selectedSubjectId : null,
            $selectedSessionId,
            $selectedDate,
            $records,
            $markedBy,
            $selectedTermId > 0 ? $selectedTermId : null
        );

        $_SESSION['flash_success'] = "Attendance successfully saved for {$saved} student(s) on " . date('D, M d, Y', strtotime($selectedDate)) . ".";

        // Post/Redirect/Get pattern
        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/admin/attendance/mark.php', PHP_URL_PATH) ?: '/admin/attendance/mark.php';
        $redirectUrl = $currentPath . '?' . http_build_query([
            'academic_session_link' => $selectedSessionId,
            'term_link' => $selectedTermId,
            'class_link' => $selectedClassId,
            'subject_link' => $selectedSubjectId,
            'attendance_date' => $selectedDate,
        ]);

        if (!headers_sent()) {
            header("Location: " . $redirectUrl, true, 303);
            exit;
        } else {
            echo "<script>window.location.replace(" . json_encode($redirectUrl) . ");</script>";
            exit;
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

// Fetch students for the chosen class, session, and term
$students = [];
if ($selectedClassId > 0) {
    // First query with session matching
    $students = QueryDB(
        "SELECT s.* FROM students s
         WHERE (s.current_class_id = ? OR s.class_link = ?)
           AND (? = 0 OR s.current_session_id = ? OR s.academic_session_link = ?)
           AND (s.status = 'active' OR s.status IS NULL)
         ORDER BY s.last_name ASC, s.first_name ASC",
        [$selectedClassId, $selectedClassId, $selectedSessionId, $selectedSessionId, $selectedSessionId]
    )->fetchAll();

    // Fallback if session filter produced 0 students
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

// Fetch existing attendance records for the selected date, class, session, term, and subject
$existingAttendance = [];
if ($selectedClassId > 0) {
    $subjectFilter = $selectedSubjectId > 0 ? $selectedSubjectId : null;

    if (schema_has_table('student_attendance')) {
        $rows = QueryDB(
            'SELECT student_id AS student_link, status, COALESCE(remarks, reason) AS remarks
             FROM student_attendance
             WHERE class_id = ? 
               AND COALESCE(date, attendance_date) = ? 
               AND ((subject_id IS NULL AND ? IS NULL) OR subject_id = ?) 
               AND (? = 0 OR session_id IS NULL OR session_id = ?)
               AND (? = 0 OR term_id IS NULL OR term_id = ?)',
            [$selectedClassId, $selectedDate, $subjectFilter, $subjectFilter, $selectedSessionId, $selectedSessionId, $selectedTermId, $selectedTermId]
        )->fetchAll();

        // If no records found with strict term matching, try without term constraint for that day
        if (empty($rows)) {
            $rows = QueryDB(
                'SELECT student_id AS student_link, status, COALESCE(remarks, reason) AS remarks
                 FROM student_attendance
                 WHERE class_id = ? 
                   AND COALESCE(date, attendance_date) = ? 
                   AND ((subject_id IS NULL AND ? IS NULL) OR subject_id = ?) 
                   AND (? = 0 OR session_id IS NULL OR session_id = ?)',
                [$selectedClassId, $selectedDate, $subjectFilter, $subjectFilter, $selectedSessionId, $selectedSessionId]
            )->fetchAll();
        }

        foreach ($rows as $row) {
            $existingAttendance[(int) $row['student_link']] = $row;
        }
    } elseif (schema_has_table('attendance')) {
        $rows = QueryDB(
            'SELECT student_link, status, remarks
             FROM attendance
             WHERE class_link = ? 
               AND date = ? 
               AND ((subject_link IS NULL AND ? IS NULL) OR subject_link = ?) 
               AND (? = 0 OR academic_session_link IS NULL OR academic_session_link = ?)',
            [$selectedClassId, $selectedDate, $subjectFilter, $subjectFilter, $selectedSessionId, $selectedSessionId]
        )->fetchAll();
        foreach ($rows as $row) {
            $existingAttendance[(int) $row['student_link']] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Mark Attendance - School Portal</title>
    @include('admin.partials.links')
    <style>
        .filter-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
        }
        .stat-chip {
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .status-btn-group .btn {
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px !important;
            margin: 0 2px;
            transition: all 0.15s ease;
        }
        .status-btn-group .btn.active-present {
            background-color: #16a34a !important;
            color: #ffffff !important;
            border-color: #16a34a !important;
            box-shadow: 0 2px 6px rgba(22, 163, 74, 0.35);
        }
        .status-btn-group .btn.active-absent {
            background-color: #dc2626 !important;
            color: #ffffff !important;
            border-color: #dc2626 !important;
            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.35);
        }
        .status-btn-group .btn.active-late {
            background-color: #d97706 !important;
            color: #ffffff !important;
            border-color: #d97706 !important;
            box-shadow: 0 2px 6px rgba(217, 119, 6, 0.35);
        }
        .status-btn-group .btn.active-excused {
            background-color: #2563eb !important;
            color: #ffffff !important;
            border-color: #2563eb !important;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.35);
        }
        .student-row:hover {
            background-color: #f8fafc;
        }
        .student-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #e2e8f0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #475569;
            font-size: 13px;
        }
        .calendar-badge {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            color: #1e293b;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        @include('admin.partials.sidebar')
        <div class="main-panel">
            @include('admin.partials.header')
            <div class="container">
                <div class="page-inner">
                    <!-- Page Header -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap pb-3">
                        <div>
                            <h2 class="text-dark fw-bold mb-1">Student Attendance Register</h2>
                            <p class="text-muted mb-0">Select session, term, class, and date from the calendar to record or update attendance.</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ url('/admin/attendance/view.php') }}" class="btn btn-outline-primary">
                                <i class="fas fa-calendar-alt me-1"></i> Attendance Broadsheet
                            </a>
                            <a href="{{ url('/admin/attendance.php') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-list me-1"></i> Attendance Overview
                            </a>
                        </div>
                    </div>

                    <!-- Flash Notifications -->
                    <?php if ($message !== ''): ?>
                        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                            <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Filter Card: Session, Term, Class, Subject & Calendar Date -->
                    <div class="card filter-card mb-4">
                        <div class="card-header bg-transparent border-bottom">
                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                <h5 class="fw-bold text-dark mb-0"><i class="fas fa-sliders-h text-primary me-2"></i> Attendance Selection Filter</h5>
                                <div class="calendar-badge mt-2 mt-sm-0">
                                    <i class="fas fa-calendar-day text-primary"></i>
                                    <span><?php echo date('l, F d, Y', strtotime($selectedDate)); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="GET" id="filterForm" class="row g-3 align-items-end">
                                <!-- Academic Session -->
                                <div class="col-md-3 col-sm-6">
                                    <label for="academic_session_link" class="form-label fw-bold text-dark">Academic Session</label>
                                    <select class="form-select form-control" id="academic_session_link" name="academic_session_link" onchange="updateTermsDropdown(this.value)">
                                        <?php foreach ($sessions as $session): ?>
                                            <option value="<?php echo (int) $session['id']; ?>" <?php echo $selectedSessionId === (int) $session['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $session['session_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Academic Term -->
                                <div class="col-md-2 col-sm-6">
                                    <label for="term_link" class="form-label fw-bold text-dark">Academic Term</label>
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
                                <div class="col-md-3 col-sm-6">
                                    <label for="class_link" class="form-label fw-bold text-dark">Class / Arm</label>
                                    <select class="form-select form-control" id="class_link" name="class_link" required>
                                        <option value="">-- Choose Class --</option>
                                        <?php foreach ($classes as $cls): ?>
                                            <option value="<?php echo (int) $cls['id']; ?>" <?php echo $selectedClassId === (int) $cls['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars(trim(($cls['class_name'] ?? '') . ' ' . ($cls['class_arm'] ?? ''))); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Subject Selection -->
                                <div class="col-md-2 col-sm-6">
                                    <label for="subject_link" class="form-label fw-bold text-dark">Subject / Period</label>
                                    <select class="form-select form-control" id="subject_link" name="subject_link">
                                        <option value="0">General Class Register</option>
                                        <?php foreach ($subjects as $sbj): ?>
                                            <option value="<?php echo (int) $sbj['id']; ?>" <?php echo $selectedSubjectId === (int) $sbj['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $sbj['subject_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Calendar Date Selection -->
                                <div class="col-md-2 col-sm-12">
                                    <label for="attendance_date" class="form-label fw-bold text-dark">Attendance Date</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" id="attendance_date" name="attendance_date" value="<?php echo htmlspecialchars($selectedDate); ?>" required>
                                    </div>
                                </div>

                                <!-- Quick Date & Submit Actions -->
                                <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2 border-top">
                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                        <span class="text-muted small me-1">Quick Date:</span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDate('<?php echo date('Y-m-d'); ?>')">Today</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDate('<?php echo date('Y-m-d', strtotime('-1 day')); ?>')">Yesterday</button>
                                    </div>
                                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                                        <i class="fas fa-search me-1"></i> Display Students
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Attendance Marking Sheet -->
                    <?php if ($selectedClassId > 0 && !empty($students)): ?>
                        <div class="card filter-card mb-4">
                            <div class="card-header bg-transparent border-bottom">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div>
                                        <h5 class="fw-bold text-dark mb-1">
                                            <i class="fas fa-user-check text-success me-2"></i>
                                            Class Register: <?php echo htmlspecialchars(trim(($selectedClass['class_name'] ?? '') . ' ' . ($selectedClass['class_arm'] ?? ''))); ?>
                                        </h5>
                                        <span class="text-muted small">
                                            Found <strong><?php echo count($students); ?></strong> student(s) enrolled &bull; Date: <strong><?php echo date('F d, Y', strtotime($selectedDate)); ?></strong>
                                        </span>
                                    </div>
                                    <!-- Dynamic Live Summary Counters -->
                                    <div class="d-flex align-items-center gap-2 flex-wrap" id="counterStats">
                                        <span class="stat-chip bg-dark text-white"><i class="fas fa-users"></i> Total: <span id="cnt-total"><?php echo count($students); ?></span></span>
                                        <span class="stat-chip bg-success text-white"><i class="fas fa-check"></i> Present: <span id="cnt-present">0</span></span>
                                        <span class="stat-chip bg-danger text-white"><i class="fas fa-times"></i> Absent: <span id="cnt-absent">0</span></span>
                                        <span class="stat-chip bg-warning text-dark"><i class="fas fa-clock"></i> Late: <span id="cnt-late">0</span></span>
                                        <span class="stat-chip bg-primary text-white"><i class="fas fa-envelope-open-text"></i> Excused: <span id="cnt-excused">0</span></span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Multi-Student Marking Control Bar -->
                                <div class="p-3 mb-3 bg-light rounded border d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="form-check me-2">
                                            <input class="form-check-input" type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this.checked)">
                                            <label class="form-check-label fw-bold small text-dark" for="selectAllCheckbox">Select All</label>
                                        </div>
                                        <span class="text-muted small me-1">Mark Selected:</span>
                                        <button type="button" class="btn btn-sm btn-success" onclick="applyBulkStatusToSelected('present')">
                                            <i class="fas fa-check me-1"></i> Present
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="applyBulkStatusToSelected('absent')">
                                            <i class="fas fa-times me-1"></i> Absent
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning text-dark" onclick="applyBulkStatusToSelected('late')">
                                            <i class="fas fa-clock me-1"></i> Late
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="applyBulkStatusToSelected('excused')">
                                            <i class="fas fa-envelope-open-text me-1"></i> Excused
                                        </button>
                                    </div>

                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted small">Global Action:</span>
                                        <button type="button" class="btn btn-sm btn-outline-success fw-bold" onclick="markAllStudents('present')">
                                            Mark All Present
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger fw-bold" onclick="markAllStudents('absent')">
                                            Mark All Absent
                                        </button>
                                    </div>
                                </div>

                                <!-- Attendance Form -->
                                <form method="POST" id="attendanceSaveForm">
                                    <input type="hidden" name="action" value="save_attendance">
                                    <input type="hidden" name="academic_session_link" value="<?php echo $selectedSessionId; ?>">
                                    <input type="hidden" name="term_link" value="<?php echo $selectedTermId; ?>">
                                    <input type="hidden" name="class_link" value="<?php echo $selectedClassId; ?>">
                                    <input type="hidden" name="subject_link" value="<?php echo $selectedSubjectId; ?>">
                                    <input type="hidden" name="attendance_date" value="<?php echo htmlspecialchars($selectedDate); ?>">

                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 40px;" class="text-center">
                                                        <i class="fas fa-check-square text-muted"></i>
                                                    </th>
                                                    <th style="width: 50px;">S/N</th>
                                                    <th style="width: 140px;">Admission No</th>
                                                    <th>Student Name</th>
                                                    <th style="min-width: 280px;" class="text-center">Attendance Status</th>
                                                    <th style="min-width: 220px;">Notes / Remarks</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $sn = 1; ?>
                                                <?php foreach ($students as $student): ?>
                                                    <?php 
                                                        $sid = (int) $student['id'];
                                                        $existing = $existingAttendance[$sid] ?? [];
                                                        $curStatus = $existing['status'] ?? '';
                                                        $initials = strtoupper(substr($student['first_name'] ?? 'S', 0, 1) . substr($student['last_name'] ?? 'T', 0, 1));
                                                    ?>
                                                    <tr class="student-row" id="row-<?php echo $sid; ?>">
                                                        <td class="text-center">
                                                            <input type="checkbox" class="form-check-input student-checkbox" value="<?php echo $sid; ?>">
                                                        </td>
                                                        <td class="text-muted fw-bold"><?php echo $sn++; ?></td>
                                                        <td>
                                                            <span class="badge bg-light text-dark font-monospace border">
                                                                <?php echo htmlspecialchars((string) ($student['admission_no'] ?? 'N/A')); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div class="student-avatar"><?php echo $initials; ?></div>
                                                                <div>
                                                                    <div class="fw-bold text-dark">
                                                                        <?php echo htmlspecialchars(trim(($student['last_name'] ?? '') . ' ' . ($student['first_name'] ?? '') . ' ' . ($student['other_names'] ?? ''))); ?>
                                                                    </div>
                                                                    <small class="text-muted">
                                                                        <?php echo ucfirst($student['gender'] ?? 'Student'); ?> &bull; <?php echo ucfirst($student['student_type'] ?? 'Day'); ?>
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <!-- Hidden input to submit form data -->
                                                            <input type="hidden" name="attendance[<?php echo $sid; ?>]" id="status-input-<?php echo $sid; ?>" value="<?php echo htmlspecialchars($curStatus); ?>" class="student-status-input">

                                                            <!-- Interactive 1-Click Segmented Buttons -->
                                                            <div class="btn-group status-btn-group" role="group">
                                                                <button type="button" 
                                                                        class="btn btn-outline-success <?php echo $curStatus === 'present' ? 'active-present' : ''; ?>" 
                                                                        onclick="setStudentStatus(<?php echo $sid; ?>, 'present')">
                                                                    <i class="fas fa-check me-1"></i> Present
                                                                </button>
                                                                <button type="button" 
                                                                        class="btn btn-outline-danger <?php echo $curStatus === 'absent' ? 'active-absent' : ''; ?>" 
                                                                        onclick="setStudentStatus(<?php echo $sid; ?>, 'absent')">
                                                                    <i class="fas fa-times me-1"></i> Absent
                                                                </button>
                                                                <button type="button" 
                                                                        class="btn btn-outline-warning <?php echo $curStatus === 'late' ? 'active-late' : ''; ?>" 
                                                                        onclick="setStudentStatus(<?php echo $sid; ?>, 'late')">
                                                                    <i class="fas fa-clock me-1"></i> Late
                                                                </button>
                                                                <button type="button" 
                                                                        class="btn btn-outline-primary <?php echo $curStatus === 'excused' ? 'active-excused' : ''; ?>" 
                                                                        onclick="setStudentStatus(<?php echo $sid; ?>, 'excused')">
                                                                    <i class="fas fa-envelope-open-text me-1"></i> Excused
                                                                </button>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm" name="remarks[<?php echo $sid; ?>]" value="<?php echo htmlspecialchars((string) ($existing['remarks'] ?? '')); ?>" placeholder="Optional remark (e.g. sick, traffic)">
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Save Actions Bar -->
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-4 border-top">
                                        <div class="text-muted small">
                                            <i class="fas fa-info-circle me-1"></i> Ready to commit attendance register to database.
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="reset" class="btn btn-outline-secondary" onclick="setTimeout(updateLiveCounters, 50)">
                                                Reset
                                            </button>
                                            <button type="submit" class="btn btn-success btn-lg px-4 fw-bold shadow">
                                                <i class="fas fa-save me-1"></i> Save Attendance Register
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php elseif ($selectedClassId > 0): ?>
                        <div class="card filter-card p-5 text-center">
                            <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                            <h4 class="fw-bold text-dark">No Active Students Found</h4>
                            <p class="text-muted mb-0">No active students were found enrolled in this class for the selected academic session. Please verify student enrollments or choose another class.</p>
                        </div>
                    <?php else: ?>
                        <div class="card filter-card p-5 text-center">
                            <i class="fas fa-calendar-check fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold text-dark">Choose Class and Session to Begin</h4>
                            <p class="text-muted mb-0">Select your academic session, term, class, and date above, then click <strong>Display Students</strong> to populate the attendance register.</p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
            @include('admin.partials.footer')
        </div>
    </div>

    <!-- Interactive Client Scripts -->
    <script>
        // Store all academic terms mapped by session ID
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

        function setDate(dateStr) {
            const dateInput = document.getElementById('attendance_date');
            if (dateInput) {
                dateInput.value = dateStr;
            }
        }

        function setStudentStatus(studentId, status) {
            const input = document.getElementById('status-input-' + studentId);
            const row = document.getElementById('row-' + studentId);
            if (!input || !row) return;

            input.value = status;

            // Update button styles in that row
            const buttons = row.querySelectorAll('.status-btn-group .btn');
            buttons.forEach(btn => {
                btn.classList.remove('active-present', 'active-absent', 'active-late', 'active-excused');
            });

            const activeBtn = row.querySelector('.btn-outline-' + (status === 'present' ? 'success' : status === 'absent' ? 'danger' : status === 'late' ? 'warning' : 'primary'));
            if (activeBtn) {
                activeBtn.classList.add('active-' + status);
            }

            updateLiveCounters();
        }

        function toggleSelectAll(isChecked) {
            document.querySelectorAll('.student-checkbox').forEach(cb => {
                cb.checked = isChecked;
            });
        }

        function applyBulkStatusToSelected(status) {
            const selectedCheckboxes = document.querySelectorAll('.student-checkbox:checked');
            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one student checkbox first.');
                return;
            }

            selectedCheckboxes.forEach(cb => {
                setStudentStatus(cb.value, status);
            });
        }

        function markAllStudents(status) {
            document.querySelectorAll('.student-status-input').forEach(input => {
                const studentId = input.id.replace('status-input-', '');
                setStudentStatus(studentId, status);
            });
        }

        function updateLiveCounters() {
            let present = 0, absent = 0, late = 0, excused = 0;
            document.querySelectorAll('.student-status-input').forEach(input => {
                const val = input.value;
                if (val === 'present') present++;
                else if (val === 'absent') absent++;
                else if (val === 'late') late++;
                else if (val === 'excused') excused++;
            });

            const cntPresent = document.getElementById('cnt-present');
            const cntAbsent = document.getElementById('cnt-absent');
            const cntLate = document.getElementById('cnt-late');
            const cntExcused = document.getElementById('cnt-excused');

            if (cntPresent) cntPresent.textContent = present;
            if (cntAbsent) cntAbsent.textContent = absent;
            if (cntLate) cntLate.textContent = late;
            if (cntExcused) cntExcused.textContent = excused;
        }

        // Initialize counters on page load
        document.addEventListener('DOMContentLoaded', () => {
            updateLiveCounters();
        });
    </script>
</body>
</html>
