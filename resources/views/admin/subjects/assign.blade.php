<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');
require_once base_path('db/school_portal_helpers.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$message = $_SESSION['flash_success'] ?? '';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$selectedSessionId = (int) ($_REQUEST['academic_session_link'] ?? (get_current_academic_session_id() ?? 0));
$selectedClassId = (int) ($_REQUEST['class_link'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    try {
        $selectedSessionId = (int) ($_POST['academic_session_link'] ?? $selectedSessionId);
        $selectedClassId = (int) ($_POST['class_link'] ?? $selectedClassId);

        if ($selectedSessionId < 1) {
            throw new Exception('Please select an academic session first.');
        }

        if ($action === 'assign_subject') {
            $subjectId = (int) ($_POST['subject_link'] ?? 0);

            if ($selectedClassId < 1 || $subjectId < 1) {
                throw new Exception('Please select both a class and a subject.');
            }

            $saved = assign_subject_to_class($selectedClassId, $subjectId, $selectedSessionId);
            if ($saved) {
                $_SESSION['flash_success'] = 'Subject assigned to class successfully.';
            } else {
                $_SESSION['flash_error'] = 'That subject is already assigned to the class, or it does not match the class level.';
            }
        } elseif ($action === 'remove_assignment') {
            $assignmentId = (int) ($_POST['assignment_id'] ?? 0);

            if ($assignmentId < 1) {
                throw new Exception('Please select a valid subject assignment to remove.');
            }

            $removed = remove_class_subject_assignment($assignmentId);
            if ($removed) {
                $_SESSION['flash_success'] = 'Subject assignment removed successfully.';
            } else {
                $_SESSION['flash_error'] = 'No matching assignment was found.';
            }
        }

        // Post/Redirect/Get pattern
        $redirectUrl = '/admin/subjects/assign.php?academic_session_link=' . $selectedSessionId . '&class_link=' . $selectedClassId;
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

$sessions = QueryDB('SELECT * FROM academic_sessions ORDER BY id DESC')->fetchAll();
$classes = QueryDB('SELECT * FROM classes ORDER BY class_level, class_name, class_arm')->fetchAll();
$selectedClass = $selectedClassId > 0
    ? QueryDB('SELECT * FROM classes WHERE id = ? LIMIT 1', [$selectedClassId])->fetch(PDO::FETCH_ASSOC)
    : null;
$assignments = get_class_subject_assignments($selectedClassId > 0 ? $selectedClassId : null, $selectedSessionId);
$subjects = [];

if ($selectedClass) {
    $classLevel = (string) ($selectedClass['class_level'] ?? '');
    $classCol = get_csa_class_column();
    $subjCol = get_csa_subject_column();
    $hasSessionCol = schema_has_column('class_subject_assignments', 'academic_session_link');

    $alreadyAssignedSql = "(SELECT COUNT(*) FROM class_subject_assignments csa WHERE csa.{$classCol} = ? AND csa.{$subjCol} = s.id";
    $params = [$selectedClassId];
    if ($hasSessionCol) {
        $alreadyAssignedSql .= " AND (? = 0 OR csa.academic_session_link = ?)";
        $params[] = $selectedSessionId;
        $params[] = $selectedSessionId;
    }
    $alreadyAssignedSql .= ") AS already_assigned";

    $hasSubjectClassLevel = schema_has_column('subjects', 'class_level');
    if ($hasSubjectClassLevel) {
        [$levelClause, $levelParams] = build_subject_class_level_sql_clause($classLevel, (string) ($selectedClass['class_name'] ?? ''));
        $queryParams = array_merge($params, $levelParams);
    } else {
        $levelClause = "1 = 1";
        $queryParams = $params;
    }

    $subjects = QueryDB(
        "SELECT s.*, {$alreadyAssignedSql}
         FROM subjects s
         WHERE (s.status IS NULL OR s.status = 'active')
           AND {$levelClause}
         ORDER BY s.subject_name, s.subject_code",
        $queryParams
    )->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Assign Subjects to Classes</title>
    @include('admin.partials.links')
</head>
<body>
    <div class="wrapper">
        @include('admin.partials.sidebar')
        <div class="main-panel">
            @include('admin.partials.header')
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left flex-column flex-md-row mb-3">
                        <div>
                            <h2 class="text-dark pb-2 fw-bold">Assign Subjects to Classes</h2>
                            <p class="text-muted">Map school catalogue subjects to classes. Subjects will automatically apply to students of that class & stream.</p>
                        </div>
                        <div class="ml-md-auto py-2 py-md-0 d-flex gap-2">
                            <a href="{{ url('/admin/subjects/manage.php') }}" class="btn btn-secondary btn-round">
                                <i class="fas fa-book me-1"></i> Manage Subjects
                            </a>
                            <a href="{{ url('/admin/subjects/student_subjects.php') }}" class="btn btn-primary btn-round">
                                <i class="fas fa-user-tag me-1"></i> Student Subjects & Drops
                            </a>
                        </div>
                    </div>

                    <?php if ($message !== ''): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <div class="card-title fw-bold"><i class="fas fa-filter me-2 text-primary"></i>Session and Class Filter</div>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <label for="academic_session_link" class="form-label fw-bold">Academic Session</label>
                                    <select class="form-control" id="academic_session_link" name="academic_session_link">
                                        <?php foreach ($sessions as $session): ?>
                                            <option value="<?php echo (int) $session['id']; ?>" <?php echo $selectedSessionId === (int) $session['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $session['session_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="class_link" class="form-label fw-bold">Class</label>
                                    <select class="form-control" id="class_link" name="class_link">
                                        <option value="">All Classes</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo (int) $class['id']; ?>" <?php echo $selectedClassId === (int) $class['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars(trim(($class['class_name'] ?? '') . ' ' . ($class['class_arm'] ?? ''))); ?> (<?php echo htmlspecialchars($class['class_level'] ?? ''); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                                        <i class="fas fa-search me-1"></i> Load
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5">
                            <div class="card shadow-sm border-top border-primary border-3">
                                <div class="card-header bg-light">
                                    <div class="card-title fw-bold text-dark">
                                        <i class="fas fa-plus-circle me-2 text-primary"></i>Assign Subject
                                    </div>
                                    <div class="card-category text-muted">Filtered to match class level and streams.</div>
                                </div>
                                <div class="card-body">
                                    <?php if (!$selectedClass): ?>
                                        <div class="alert alert-info mb-0">Select a specific class from the filter above to assign subjects.</div>
                                    <?php else: ?>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="assign_subject">
                                            <input type="hidden" name="academic_session_link" value="<?php echo $selectedSessionId; ?>">
                                            <input type="hidden" name="class_link" value="<?php echo $selectedClassId; ?>">
                                            <div class="form-group mb-3">
                                                <label class="fw-bold">Target Class</label>
                                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars(trim(($selectedClass['class_name'] ?? '') . ' ' . ($selectedClass['class_arm'] ?? ''))); ?> (<?php echo htmlspecialchars($selectedClass['class_level'] ?? ''); ?>)" readonly>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label for="subject_link" class="fw-bold">Subject to Assign <span class="text-danger">*</span></label>
                                                <select class="form-control" id="subject_link" name="subject_link" required>
                                                    <option value="">-- Select Subject from Catalogue --</option>
                                                    <?php foreach ($subjects as $subject): ?>
                                                        <?php 
                                                            $isAssigned = (int) ($subject['already_assigned'] ?? 0) > 0;
                                                            $subDept = strtolower((string) ($subject['department'] ?? 'general'));
                                                            $deptLabel = match($subDept) {
                                                                'science' => 'Sciences',
                                                                'arts' => 'Arts',
                                                                'commercial' => 'Commercial',
                                                                default => 'All / General',
                                                            };
                                                        ?>
                                                        <option value="<?php echo (int) $subject['id']; ?>" <?php echo $isAssigned ? 'disabled' : ''; ?>>
                                                            <?php echo htmlspecialchars($subject['subject_name'] . ' (' . $subject['subject_code'] . ') [' . $deptLabel . ']'); ?>
                                                            <?php if ($isAssigned): ?> [Already Assigned] <?php endif; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <small class="form-text text-muted">Subjects matching this class's level are listed above.</small>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100 fw-bold">
                                                <i class="fas fa-check me-1"></i> Assign Subject to Class
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <div class="card-title fw-bold">Current Class Subject Assignments</div>
                                    <span class="badge bg-primary"><?php echo count($assignments); ?> Assignments</span>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (empty($assignments)): ?>
                                        <div class="text-center py-4 text-muted">
                                            <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                                            No subject assignments were found for this filter.
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table id="basic-datatables" class="display table-striped table-hover table table-bordered mb-0">
                                                <thead class="table-light">
                                                 <tr>
                                                    <th style="width: 40px;">S/N</th>
                                                    <th>Class</th>
                                                    <th>Subject</th>
                                                    <th>Code</th>
                                                    <th>Stream Category</th>
                                                    <th>Type</th>
                                                    <th class="text-end">Action</th>
                                                 </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $sn = 1; foreach ($assignments as $assignment): ?>
                                                        <?php 
                                                            $subDept = strtolower((string) ($assignment['department'] ?? 'general'));
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $sn++; ?></td>
                                                            <td><strong><?php echo htmlspecialchars(trim(($assignment['class_name'] ?? '') . ' ' . ($assignment['class_arm'] ?? ''))); ?></strong></td>
                                                            <td><strong><?php echo htmlspecialchars((string) ($assignment['subject_name'] ?? '')); ?></strong></td>
                                                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars((string) ($assignment['subject_code'] ?? '')); ?></span></td>
                                                            <td>
                                                                <?php if ($subDept === 'science'): ?>
                                                                    <span class="badge bg-info text-dark"><i class="fas fa-flask me-1"></i>Sciences</span>
                                                                <?php elseif ($subDept === 'arts'): ?>
                                                                    <span class="badge text-white" style="background:#6f42c1;"><i class="fas fa-palette me-1"></i>Arts</span>
                                                                <?php elseif ($subDept === 'commercial'): ?>
                                                                    <span class="badge bg-success"><i class="fas fa-calculator me-1"></i>Commercial</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-light text-dark border">General / Core</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-<?php echo ((int) ($assignment['is_core'] ?? 0) === 1) ? 'primary' : 'secondary'; ?>">
                                                                    <?php echo ((int) ($assignment['is_core'] ?? 0) === 1) ? 'Core' : 'Elective'; ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-end">
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="action" value="remove_assignment">
                                                                    <input type="hidden" name="assignment_id" value="<?php echo (int) ($assignment['id'] ?? 0); ?>">
                                                                    <input type="hidden" name="academic_session_link" value="<?php echo $selectedSessionId; ?>">
                                                                    <input type="hidden" name="class_link" value="<?php echo $selectedClassId; ?>">
                                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Remove this subject assignment from this class?')">
                                                                        <i class="fas fa-trash me-1"></i> Remove
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.partials.footer')
        </div>
    </div>
</body>
</html>
