<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');
require_once base_path('db/school_portal_helpers.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

// Flash messages (PRG)
$message = $_SESSION['flash_success'] ?? '';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$currentSessionId = get_current_academic_session_id() ?? 0;
$selectedSessionId = (int) ($_REQUEST['session_id'] ?? $currentSessionId);
$selectedClassId = (int) ($_REQUEST['class_id'] ?? 0);
$selectedSubjectId = (int) ($_REQUEST['subject_id'] ?? 0);
$selectedDepartment = strtolower(trim((string) ($_REQUEST['department'] ?? '')));

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    try {
        if ($action === 'drop_subject') {
            $studentId = (int) ($_POST['student_id'] ?? 0);
            $subjectId = (int) ($_POST['subject_id'] ?? 0);
            $reason = trim((string) ($_POST['reason'] ?? ''));

            if ($studentId < 1 || $subjectId < 1) {
                throw new Exception('Invalid student or subject specified.');
            }

            drop_student_subject($studentId, $subjectId, $selectedSessionId, $reason ?: 'Dropped by administrator');
            $_SESSION['flash_success'] = 'Subject dropped successfully for student.';

        } elseif ($action === 'enroll_subject') {
            $studentId = (int) ($_POST['student_id'] ?? 0);
            $subjectId = (int) ($_POST['subject_id'] ?? 0);

            if ($studentId < 1 || $subjectId < 1) {
                throw new Exception('Invalid student or subject specified.');
            }

            enroll_student_subject($studentId, $subjectId, $selectedSessionId);
            $_SESSION['flash_success'] = 'Subject enrolled / restored successfully for student.';

        } elseif ($action === 'update_student_stream') {
            $studentId = (int) ($_POST['student_id'] ?? 0);
            $newDept = strtolower(trim((string) ($_POST['department'] ?? 'general')));
            if (!in_array($newDept, ['general', 'science', 'arts', 'commercial'], true)) {
                $newDept = 'general';
            }

            if ($studentId < 1) {
                throw new Exception('Invalid student specified.');
            }

            QueryDB("UPDATE students SET department = ?, updated_at = NOW() WHERE id = ?", [$newDept, $studentId]);
            $_SESSION['flash_success'] = 'Student category / stream updated successfully.';
        }

        // Post/Redirect/Get pattern
        $queryString = http_build_query([
            'session_id' => $selectedSessionId,
            'class_id' => $selectedClassId,
            'subject_id' => $selectedSubjectId,
            'department' => $selectedDepartment,
        ]);
        $redirectUrl = '/admin/subjects/student_subjects.php' . ($queryString ? '?' . $queryString : '');

        if (!headers_sent()) {
            header("Location: {$redirectUrl}", true, 303);
            exit;
        } else {
            echo "<script>window.location.replace(" . json_encode($redirectUrl) . ");</script>";
            exit;
        }

    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

// Data fetching
$sessions = QueryDB('SELECT * FROM academic_sessions ORDER BY start_date DESC, id DESC')->fetchAll();
$classes = QueryDB('SELECT * FROM classes ORDER BY class_level, class_name, class_arm')->fetchAll();
$selectedClass = $selectedClassId > 0
    ? QueryDB('SELECT * FROM classes WHERE id = ? LIMIT 1', [$selectedClassId])->fetch(PDO::FETCH_ASSOC)
    : null;

$classSubjects = [];
$studentsList = [];

if ($selectedClass) {
    // Get all class-assigned subjects and level-compatible subjects
    $classLevel = (string) ($selectedClass['class_level'] ?? '');
    $classCol = get_csa_class_column();
    $subjCol = get_csa_subject_column();
    $hasSubjectClassLevel = schema_has_column('subjects', 'class_level');
    if ($hasSubjectClassLevel) {
        [$levelClause, $levelParams] = build_subject_class_level_sql_clause($classLevel, (string) ($selectedClass['class_name'] ?? ''));
        $levelSql = "(s.status = 'active' AND {$levelClause})";
        $queryParams = array_merge([$selectedClassId], $levelParams);
    } else {
        $levelSql = "(s.status = 'active')";
        $queryParams = [$selectedClassId];
    }

    $classSubjects = QueryDB(
        "SELECT DISTINCT s.*
         FROM subjects s
         LEFT JOIN class_subject_assignments csa ON csa.{$subjCol} = s.id
         WHERE csa.{$classCol} = ?
            OR {$levelSql}
         ORDER BY s.subject_name",
        $queryParams
    )->fetchAll(PDO::FETCH_ASSOC);

    // Fetch students in class
    $studentClassCol = schema_has_column('students', 'current_class_id') ? 's.current_class_id' : 's.class_link';
    $studentsQuery = "SELECT s.* FROM students s WHERE {$studentClassCol} = ?";
    $params = [$selectedClassId];
    if ($selectedDepartment !== '' && schema_has_column('students', 'department')) {
        $studentsQuery .= " AND s.department = ?";
        $params[] = $selectedDepartment;
    }
    $studentsQuery .= " ORDER BY s.first_name, s.last_name";
    $studentsList = QueryDB($studentsQuery, $params)->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Student Subjects & Dropping</title>
    @include('admin.partials.links')
</head>
<body>
    <div class="wrapper">
        @include('admin.partials.sidebar')
        <div class="main-panel">
            @include('admin.partials.header')
            <div class="container">
                <div class="page-inner">
                    <!-- Page Header -->
                    <div class="d-flex align-items-left flex-column flex-md-row mb-3">
                        <div>
                            <h2 class="text-dark pb-2 fw-bold">Student Subjects & Course Drops</h2>
                            <p class="text-muted">Manage individual student subject allocations, drop specific subjects, and assign streams (Sciences, Arts, Commercial).</p>
                        </div>
                        <div class="ml-md-auto py-2 py-md-0 d-flex gap-2">
                            <a href="{{ url('/admin/subjects/manage.php') }}" class="btn btn-secondary btn-round">
                                <i class="fas fa-book me-1"></i> Subject Catalogue
                            </a>
                            <a href="{{ url('/admin/subjects/assign.php') }}" class="btn btn-info btn-round">
                                <i class="fas fa-link me-1"></i> Class Assignments
                            </a>
                        </div>
                    </div>

                    <?php if ($message !== ''): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Filter Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <div class="card-title fw-bold"><i class="fas fa-filter me-2 text-primary"></i>Filter Class & Subject</div>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <label for="session_id" class="form-label fw-bold">Academic Session</label>
                                    <select class="form-control" id="session_id" name="session_id">
                                        <?php foreach ($sessions as $session): ?>
                                            <option value="<?php echo (int) $session['id']; ?>" <?php echo $selectedSessionId === (int) $session['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $session['session_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="class_id" class="form-label fw-bold">Class <span class="text-danger">*</span></label>
                                    <select class="form-control" id="class_id" name="class_id" required>
                                        <option value="">-- Select Class --</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo (int) $class['id']; ?>" <?php echo $selectedClassId === (int) $class['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars(trim(($class['class_name'] ?? '') . ' ' . ($class['class_arm'] ?? ''))); ?> (<?php echo htmlspecialchars($class['class_level'] ?? ''); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="department" class="form-label fw-bold">Stream Category</label>
                                    <select class="form-control" id="department" name="department">
                                        <option value="">All Categories / Streams</option>
                                        <option value="general" <?php echo $selectedDepartment === 'general' ? 'selected' : ''; ?>>General / All</option>
                                        <option value="science" <?php echo $selectedDepartment === 'science' ? 'selected' : ''; ?>>Sciences</option>
                                        <option value="arts" <?php echo $selectedDepartment === 'arts' ? 'selected' : ''; ?>>Arts & Humanities</option>
                                        <option value="commercial" <?php echo $selectedDepartment === 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="subject_id" class="form-label fw-bold">Subject View Mode</label>
                                    <select class="form-control" id="subject_id" name="subject_id">
                                        <option value="0">All Subjects (Overview per student)</option>
                                        <?php foreach ($classSubjects as $cSub): ?>
                                            <option value="<?php echo (int) $cSub['id']; ?>" <?php echo $selectedSubjectId === (int) $cSub['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cSub['subject_name'] . ' (' . $cSub['subject_code'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                                        <i class="fas fa-search me-1"></i> Load Students
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if (!$selectedClass): ?>
                        <div class="alert alert-info py-4 text-center">
                            <i class="fas fa-chalkboard fa-2x mb-2 d-block"></i>
                            Please select a class from the filter above to view and manage student subjects.
                        </div>
                    <?php elseif (empty($studentsList)): ?>
                        <div class="alert alert-warning py-4 text-center">
                            <i class="fas fa-users-slash fa-2x mb-2 d-block"></i>
                            No students found in this class matching the selected filter.
                        </div>
                    <?php else: ?>

                        <!-- Mode 1: Single Subject Class-wide View -->
                        <?php if ($selectedSubjectId > 0): ?>
                            <?php 
                                $focusedSubject = QueryDB("SELECT * FROM subjects WHERE id = ? LIMIT 1", [$selectedSubjectId])->fetch(PDO::FETCH_ASSOC);
                                $subjDept = strtolower((string) ($focusedSubject['department'] ?? 'general'));
                            ?>
                            <div class="card shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <h4 class="card-title fw-bold mb-0">
                                            Subject: <span class="text-primary"><?php echo htmlspecialchars((string) ($focusedSubject['subject_name'] ?? '')); ?> (<?php echo htmlspecialchars((string) ($focusedSubject['subject_code'] ?? '')); ?>)</span>
                                        </h4>
                                        <div class="card-category text-muted">
                                            Class: <?php echo htmlspecialchars(trim(($selectedClass['class_name'] ?? '') . ' ' . ($selectedClass['class_arm'] ?? ''))); ?> |
                                            Stream Target: <strong><?php echo ucfirst($subjDept ?: 'General'); ?></strong>
                                        </div>
                                    </div>
                                    <a href="?session_id=<?php echo $selectedSessionId; ?>&class_id=<?php echo $selectedClassId; ?>" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-th-list me-1"></i> View All Subjects
                                    </a>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 40px;">S/N</th>
                                                    <th>Admission No</th>
                                                    <th>Student Name</th>
                                                    <th>Student Stream</th>
                                                    <th>Enrollment Status</th>
                                                    <th class="text-end" style="min-width: 140px;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $sn = 1; foreach ($studentsList as $st): ?>
                                                    <?php 
                                                        $stDept = strtolower((string) ($st['department'] ?? 'general'));
                                                        $allocated = get_student_allocated_subjects((int) $st['id'], $selectedSessionId);
                                                        $matchedSub = null;
                                                        foreach ($allocated as $as) {
                                                            if ((int) $as['id'] === $selectedSubjectId) {
                                                                $matchedSub = $as;
                                                                break;
                                                            }
                                                        }
                                                        $isEnrolled = $matchedSub && !$matchedSub['is_dropped'];
                                                        $isDropped = $matchedSub && $matchedSub['is_dropped'];
                                                    ?>
                                                    <tr class="<?php echo $isDropped ? 'table-light text-muted' : ''; ?>">
                                                        <td><?php echo $sn++; ?></td>
                                                        <td><code><?php echo htmlspecialchars((string) $st['admission_no']); ?></code></td>
                                                        <td>
                                                            <a href="/admin/view_students.php?id=<?php echo (int) $st['id']; ?>" class="fw-bold text-dark">
                                                                <?php echo htmlspecialchars(trim($st['first_name'] . ' ' . $st['last_name'] . ' ' . ($st['other_names'] ?? ''))); ?>
                                                            </a>
                                                            <?php 
                                                                $stCat = strtoupper(trim((string) ($st['category'] ?? 'NI')));
                                                                $isOs = ($stCat === 'OS' || $stCat === 'RETURNING');
                                                            ?>
                                                            <span class="badge <?php echo $isOs ? 'bg-primary text-white' : 'bg-info text-dark'; ?> ms-1" style="font-size: 10px;" title="<?php echo $isOs ? 'Returning Student (OS)' : 'New Intake (NI)'; ?>">
                                                                <?php echo $isOs ? 'OS' : 'NI'; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php if ($stDept === 'science'): ?>
                                                                <span class="badge bg-info text-dark"><i class="fas fa-flask me-1"></i>Sciences</span>
                                                            <?php elseif ($stDept === 'arts'): ?>
                                                                <span class="badge text-white" style="background:#6f42c1;"><i class="fas fa-palette me-1"></i>Arts</span>
                                                            <?php elseif ($stDept === 'commercial'): ?>
                                                                <span class="badge bg-success"><i class="fas fa-calculator me-1"></i>Commercial</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">General</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($isEnrolled): ?>
                                                                <span class="badge bg-success text-white">
                                                                    <i class="fas fa-check me-1"></i>Enrolled (Active)
                                                                </span>
                                                            <?php elseif ($isDropped): ?>
                                                                <span class="badge bg-danger text-white">
                                                                    <i class="fas fa-ban me-1"></i>Dropped
                                                                </span>
                                                                <?php if (!empty($matchedSub['reason'])): ?>
                                                                    <br><small class="text-danger"><?php echo htmlspecialchars($matchedSub['reason']); ?></small>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <span class="badge bg-light text-muted border">
                                                                    Not Offering (Stream Mismatch)
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-end">
                                                            <?php if ($isEnrolled): ?>
                                                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#dropModal<?php echo (int) $st['id']; ?>">
                                                                    <i class="fas fa-times me-1"></i> Drop Subject
                                                                </button>

                                                                <div class="modal fade" id="dropModal<?php echo (int) $st['id']; ?>" tabindex="-1" aria-hidden="true" style="text-align: left;">
                                                                    <div class="modal-dialog">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header bg-danger text-white">
                                                                                <h5 class="modal-title fw-bold">Drop Subject</h5>
                                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                                            </div>
                                                                            <form method="POST">
                                                                                <div class="modal-body">
                                                                                    <input type="hidden" name="action" value="drop_subject">
                                                                                    <input type="hidden" name="student_id" value="<?php echo (int) $st['id']; ?>">
                                                                                    <input type="hidden" name="subject_id" value="<?php echo $selectedSubjectId; ?>">
                                                                                    <p class="mb-3">
                                                                                        Drop <strong><?php echo htmlspecialchars($focusedSubject['subject_name']); ?></strong> for <strong><?php echo htmlspecialchars($st['first_name'] . ' ' . $st['last_name']); ?></strong>?
                                                                                    </p>
                                                                                    <div class="form-group p-0">
                                                                                        <label class="form-label fw-bold">Reason (Optional)</label>
                                                                                        <input type="text" class="form-control" name="reason" placeholder="e.g. Switched elective, medical reason">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                                    <button type="submit" class="btn btn-danger fw-bold">Confirm Drop</button>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php else: ?>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="action" value="enroll_subject">
                                                                    <input type="hidden" name="student_id" value="<?php echo (int) $st['id']; ?>">
                                                                    <input type="hidden" name="subject_id" value="<?php echo $selectedSubjectId; ?>">
                                                                    <button type="submit" class="btn btn-outline-success btn-sm fw-bold">
                                                                        <i class="fas fa-check me-1"></i> Enroll / Restore
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        <!-- Mode 2: All Subjects Overview per student -->
                        <?php else: ?>
                            <div class="card shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <h4 class="card-title fw-bold mb-0">
                                            Students in <?php echo htmlspecialchars(trim(($selectedClass['class_name'] ?? '') . ' ' . ($selectedClass['class_arm'] ?? ''))); ?>
                                        </h4>
                                        <div class="card-category text-muted">
                                            Shows subject enrollments, dropped subjects, and student stream categories.
                                        </div>
                                    </div>
                                    <span class="badge bg-primary fs-6"><?php echo count($studentsList); ?> Students</span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 40px;">S/N</th>
                                                    <th>Admission No</th>
                                                    <th>Student Name</th>
                                                    <th>Stream Category</th>
                                                    <th>Active Subjects</th>
                                                    <th>Dropped Subjects</th>
                                                    <th class="text-end">Manage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $sn = 1; foreach ($studentsList as $st): ?>
                                                    <?php 
                                                        $stDept = strtolower((string) ($st['department'] ?? 'general'));
                                                        $allocated = get_student_allocated_subjects((int) $st['id'], $selectedSessionId);
                                                        $enrolledSubs = array_filter($allocated, fn($s) => !$s['is_dropped']);
                                                        $droppedSubs = array_filter($allocated, fn($s) => $s['is_dropped']);
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $sn++; ?></td>
                                                        <td><code><?php echo htmlspecialchars((string) $st['admission_no']); ?></code></td>
                                                        <td>
                                                            <a href="/admin/view_students.php?id=<?php echo (int) $st['id']; ?>" class="fw-bold text-dark">
                                                                <?php echo htmlspecialchars(trim($st['first_name'] . ' ' . $st['last_name'] . ' ' . ($st['other_names'] ?? ''))); ?>
                                                            </a>
                                                            <?php 
                                                                $stCat = strtoupper(trim((string) ($st['category'] ?? 'NI')));
                                                                $isOs = ($stCat === 'OS' || $stCat === 'RETURNING');
                                                            ?>
                                                            <span class="badge <?php echo $isOs ? 'bg-primary text-white' : 'bg-info text-dark'; ?> ms-1" style="font-size: 10px;" title="<?php echo $isOs ? 'Returning Student (OS)' : 'New Intake (NI)'; ?>">
                                                                <?php echo $isOs ? 'OS' : 'NI'; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <!-- Quick stream category changer form -->
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="action" value="update_student_stream">
                                                                <input type="hidden" name="student_id" value="<?php echo (int) $st['id']; ?>">
                                                                <select name="department" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                                                    <option value="general" <?php echo $stDept === 'general' ? 'selected' : ''; ?>>General</option>
                                                                    <option value="science" <?php echo $stDept === 'science' ? 'selected' : ''; ?>>Sciences</option>
                                                                    <option value="arts" <?php echo $stDept === 'arts' ? 'selected' : ''; ?>>Arts</option>
                                                                    <option value="commercial" <?php echo $stDept === 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                                                                </select>
                                                            </form>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success text-white fw-bold">
                                                                <?php echo count($enrolledSubs); ?> Enrolled
                                                            </span>
                                                            <button class="btn btn-link btn-sm p-0 ms-1 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#studentSubs<?php echo $st['id']; ?>">
                                                                <small><i class="fas fa-chevron-down"></i> view</small>
                                                            </button>
                                                        </td>
                                                        <td>
                                                            <?php if (count($droppedSubs) > 0): ?>
                                                                <span class="badge bg-danger text-white fw-bold">
                                                                    <?php echo count($droppedSubs); ?> Dropped
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="text-muted"><small>None</small></span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="/admin/view_students.php?id=<?php echo (int) $st['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                                <i class="fas fa-user me-1"></i> Profile & Subjects
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <!-- Collapsible Subject Details -->
                                                    <tr class="collapse bg-light" id="studentSubs<?php echo $st['id']; ?>">
                                                        <td colspan="7" class="p-3">
                                                            <div class="row">
                                                                <div class="col-md-6 border-end">
                                                                    <strong class="text-success d-block mb-2"><i class="fas fa-check-circle me-1"></i>Active Enrolled Subjects (<?php echo count($enrolledSubs); ?>):</strong>
                                                                    <div class="d-flex flex-wrap gap-1">
                                                                        <?php foreach ($enrolledSubs as $es): ?>
                                                                            <span class="badge bg-light text-dark border p-2">
                                                                                <?php echo htmlspecialchars($es['subject_name'] . ' (' . $es['subject_code'] . ')'); ?>
                                                                                <form method="POST" class="d-inline ms-1">
                                                                                    <input type="hidden" name="action" value="drop_subject">
                                                                                    <input type="hidden" name="student_id" value="<?php echo (int) $st['id']; ?>">
                                                                                    <input type="hidden" name="subject_id" value="<?php echo (int) $es['id']; ?>">
                                                                                    <button type="submit" class="btn btn-link btn-sm text-danger p-0" title="Drop this subject" onclick="return confirm('Drop <?php echo htmlspecialchars(addslashes($es['subject_name'])); ?> for this student?')">
                                                                                        <i class="fas fa-times"></i>
                                                                                    </button>
                                                                                </form>
                                                                            </span>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <strong class="text-danger d-block mb-2"><i class="fas fa-ban me-1"></i>Dropped Subjects (<?php echo count($droppedSubs); ?>):</strong>
                                                                    <?php if (empty($droppedSubs)): ?>
                                                                        <small class="text-muted">No dropped subjects.</small>
                                                                    <?php else: ?>
                                                                        <div class="d-flex flex-wrap gap-1">
                                                                            <?php foreach ($droppedSubs as $ds): ?>
                                                                                <span class="badge bg-light text-danger border border-danger p-2">
                                                                                    <span class="text-decoration-line-through"><?php echo htmlspecialchars($ds['subject_name'] . ' (' . $ds['subject_code'] . ')'); ?></span>
                                                                                    <form method="POST" class="d-inline ms-1">
                                                                                        <input type="hidden" name="action" value="enroll_subject">
                                                                                        <input type="hidden" name="student_id" value="<?php echo (int) $st['id']; ?>">
                                                                                        <input type="hidden" name="subject_id" value="<?php echo (int) $ds['id']; ?>">
                                                                                        <button type="submit" class="btn btn-link btn-sm text-success p-0" title="Restore / Re-enroll">
                                                                                            <i class="fas fa-undo"></i>
                                                                                        </button>
                                                                                    </form>
                                                                                </span>
                                                                            <?php endforeach; ?>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    <?php endif; ?>

                </div>
            </div>
            @include('admin.partials.footer')
        </div>
    </div>
</body>
</html>
