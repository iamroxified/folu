<?php
// Start session

// Include database configuration and functions
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$message = '';
$error = '';

$studentId = (int) ($_GET['student_id'] ?? 0);
if ($studentId <= 0) {
    die('Student ID is required');
}

$studentSessionColumn = schema_has_column('students', 'current_session_id')
    ? 'current_session_id'
    : (schema_has_column('students', 'academic_session_link') ? 'academic_session_link' : null);
$studentTermColumn = schema_has_column('students', 'current_term_id')
    ? 'current_term_id'
    : (schema_has_column('students', 'term_link') ? 'term_link' : null);
$studentClassColumn = schema_has_column('students', 'current_class_id')
    ? 'current_class_id'
    : (schema_has_column('students', 'class_link') ? 'class_link' : null);

$studentSelects = [
    's.*',
    "COALESCE(c.class_name, sc.class_name, '') AS class_name",
    "COALESCE(c.class_arm, sc.section, '') AS class_arm",
    "COALESCE(c.class_level, sc.grade_level, '') AS class_level",
    schema_has_table('academic_sessions') && $studentSessionColumn ? 'ac.session_name' : 'NULL AS session_name',
    schema_has_table('terms') && $studentTermColumn ? 't.term_name' : 'NULL AS term_name',
];

$studentJoins = [];
if (schema_has_table('classes') && schema_has_column('students', 'class_link')) {
    $studentJoins[] = 'LEFT JOIN classes c ON s.class_link = c.id';
} else {
    $studentJoins[] = 'LEFT JOIN classes c ON 1 = 0';
}

if (schema_has_table('school_classes') && schema_has_column('students', 'current_class_id')) {
    $studentJoins[] = 'LEFT JOIN school_classes sc ON s.current_class_id = sc.id';
} else {
    $studentJoins[] = 'LEFT JOIN school_classes sc ON 1 = 0';
}

if (schema_has_table('academic_sessions') && $studentSessionColumn) {
    $studentJoins[] = 'LEFT JOIN academic_sessions ac ON s.' . $studentSessionColumn . ' = ac.id';
}

if (schema_has_table('terms') && $studentTermColumn) {
    $studentJoins[] = 'LEFT JOIN terms t ON s.' . $studentTermColumn . ' = t.id';
}

try {
    $studentQuery = 'SELECT ' . implode(', ', $studentSelects) .
        ' FROM students s ' . implode(' ', $studentJoins) .
        ' WHERE s.id = ? LIMIT 1';

    $student = QueryDB($studentQuery, [$studentId])->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        die('Student not found');
    }

    $studentClassId = (int) ($student['current_class_id'] ?? $student['class_link'] ?? 0);
    $studentSessionId = (int) ($student['current_session_id'] ?? $student['academic_session_link'] ?? 0);
    $studentTermId = (int) ($student['current_term_id'] ?? $student['term_link'] ?? 0);
    $studentCategory = strtoupper(trim((string) ($student['category'] ?? 'NI')));
    $studentGenderCode = match (strtolower(trim((string) ($student['gender'] ?? '')))) {
        'male' => 'M',
        'female' => 'F',
        default => 'All',
    };

    if ($studentTermId <= 0 && schema_has_table('terms')) {
        $studentTermId = (int) (QueryDB(
            'SELECT id FROM terms WHERE is_active = 1 ORDER BY term_number ASC, id DESC LIMIT 1'
        )->fetchColumn() ?: 0);
    }

    if (($student['term_name'] ?? '') === '' && $studentTermId > 0 && schema_has_table('terms')) {
        $student['term_name'] = QueryDB(
            'SELECT term_name FROM terms WHERE id = ? LIMIT 1',
            [$studentTermId]
        )->fetchColumn() ?: null;
    }

    $sessionLabel = trim((string) ($student['session_name'] ?? 'No active session'));
    $termLabel = trim((string) ($student['term_name'] ?? 'No active term'));
    $studentSessionContext = $termLabel !== '' && $termLabel !== 'No active term'
        ? $sessionLabel . ' - ' . $termLabel
        : $sessionLabel;

    if (isset($_GET['deallocate_fee'])) {
        $feeStructureToDeallocate = (int) $_GET['deallocate_fee'];

        if ($feeStructureToDeallocate <= 0) {
            $error = 'Invalid fee selected for de-allocation.';
        } else {
            $feeRecord = QueryDB(
                'SELECT amount_paid FROM student_fees WHERE student_id = ? AND fee_structure_id = ? LIMIT 1',
                [$studentId, $feeStructureToDeallocate]
            )->fetch(PDO::FETCH_ASSOC);

            if (!$feeRecord) {
                $error = 'That fee is not currently allocated to this student.';
            } elseif ((float) ($feeRecord['amount_paid'] ?? 0) > 0) {
                $error = 'Cannot de-allocate fee. A payment has already been recorded for it.';
            } else {
                QueryDB(
                    'DELETE FROM student_fees WHERE student_id = ? AND fee_structure_id = ?',
                    [$studentId, $feeStructureToDeallocate]
                );

                $_SESSION['form_message'] = 'Fee successfully de-allocated.';
                header('Location: fee_structure.php?student_id=' . $studentId);
                exit;
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['allocate_fees'])) {
        $selectedFees = array_values(array_unique(array_map('intval', $_POST['fee_ids'] ?? [])));

        if ($selectedFees === []) {
            $error = 'Please select at least one fee to allocate.';
        } else {
            $allocatedCount = 0;
            $pdo->beginTransaction();

            try {
                foreach ($selectedFees as $feeStructureId) {
                    if ($feeStructureId <= 0) {
                        continue;
                    }

                    $alreadyAllocated = (int) QueryDB(
                        'SELECT COUNT(*) FROM student_fees WHERE student_id = ? AND fee_structure_id = ?',
                        [$studentId, $feeStructureId]
                    )->fetchColumn() > 0;

                    if ($alreadyAllocated) {
                        continue;
                    }

                    $feeStructure = QueryDB(
                        "SELECT fs.*, ac.session_name, t.term_name
                         FROM fee_structures fs
                         LEFT JOIN academic_sessions ac ON fs.session_id = ac.id
                         LEFT JOIN terms t ON fs.term_id = t.id
                         WHERE fs.id = ? AND fs.is_active = 1
                         LIMIT 1",
                        [$feeStructureId]
                    )->fetch(PDO::FETCH_ASSOC);

                    if (!$feeStructure) {
                        continue;
                    }

                    if ($studentSessionId > 0 && (int) ($feeStructure['session_id'] ?? 0) !== $studentSessionId) {
                        continue;
                    }

                    if ($studentTermId > 0 && (int) ($feeStructure['term_id'] ?? 0) !== $studentTermId) {
                        continue;
                    }

                    if (!empty($feeStructure['class_id']) && (int) $feeStructure['class_id'] !== $studentClassId) {
                        continue;
                    }

                    if ($studentCategory !== '' && strtoupper((string) ($feeStructure['category'] ?? '')) !== $studentCategory) {
                        continue;
                    }

                    $feeGender = strtoupper((string) ($feeStructure['gender'] ?? 'All'));
                    if ($feeGender !== 'ALL' && $feeGender !== strtoupper($studentGenderCode)) {
                        continue;
                    }

                    $amountDue = (float) ($feeStructure['amount'] ?? 0);
                    $dueDate = !empty($feeStructure['effective_to'])
                        ? (string) $feeStructure['effective_to']
                        : date('Y-m-d', strtotime('+30 days'));
                    $academicYear = trim((string) ($feeStructure['session_name'] ?? $sessionLabel));
                    $semester = trim((string) ($feeStructure['term_name'] ?? $termLabel));
                    $notes = trim((string) ($feeStructure['description'] ?? ''));

                    QueryDB(
                        'INSERT INTO student_fees (student_id, fee_structure_id, amount_due, amount_paid, balance, due_date, status, academic_year, semester, notes, created_at, updated_at)
                         VALUES (?, ?, ?, 0, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
                        [
                            $studentId,
                            $feeStructureId,
                            $amountDue,
                            $amountDue,
                            $dueDate,
                            'pending',
                            $academicYear !== '' ? $academicYear : 'N/A',
                            $semester !== '' ? $semester : null,
                            $notes !== '' ? $notes : null,
                        ]
                    );

                    $allocatedCount++;
                }

                $pdo->commit();

                if ($allocatedCount > 0) {
                    $message = 'Successfully allocated ' . $allocatedCount . ' new fee(s) to the student.';
                } else {
                    $error = 'No new fees were allocated. They may already be assigned or may not match the student context.';
                }
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $error = 'Failed to allocate fees: ' . $e->getMessage();
            }
        }
    }

    if (isset($_SESSION['form_message'])) {
        $message = (string) $_SESSION['form_message'];
        unset($_SESSION['form_message']);
    }

    $feeQuery = "
        SELECT
            fs.*,
            sc.class_name AS fee_class_name,
            sc.section AS fee_class_section,
            ac.session_name AS fee_session_name,
            t.term_name AS fee_term_name,
            sf.id AS student_fee_id,
            sf.amount_paid,
            sf.amount_due,
            sf.balance,
            sf.status AS payment_status,
            sf.due_date,
            CASE WHEN sf.id IS NOT NULL THEN 1 ELSE 0 END AS is_allocated
        FROM fee_structures fs
        LEFT JOIN school_classes sc ON fs.class_id = sc.id
        LEFT JOIN academic_sessions ac ON fs.session_id = ac.id
        LEFT JOIN terms t ON fs.term_id = t.id
        LEFT JOIN student_fees sf ON fs.id = sf.fee_structure_id AND sf.student_id = ?
        WHERE fs.is_active = 1
          AND fs.effective_from <= CURDATE()
          AND (fs.effective_to IS NULL OR fs.effective_to >= CURDATE())";

    $feeParams = [$studentId];

    if ($studentSessionId > 0) {
        $feeQuery .= ' AND fs.session_id = ?';
        $feeParams[] = $studentSessionId;
    }

    if ($studentTermId > 0) {
        $feeQuery .= ' AND fs.term_id = ?';
        $feeParams[] = $studentTermId;
    }

    if ($studentClassId > 0) {
        $feeQuery .= ' AND (fs.class_id = ? OR fs.class_id IS NULL)';
        $feeParams[] = $studentClassId;
    }

    if ($studentCategory !== '') {
        $feeQuery .= ' AND fs.category = ?';
        $feeParams[] = $studentCategory;
    }

    $feeQuery .= " AND (fs.gender = ? OR fs.gender = 'All')";
    $feeParams[] = $studentGenderCode;

    $feeQuery .= ' ORDER BY fs.is_mandatory DESC, fs.name ASC, fs.amount ASC';

    $feeStructures = QueryDB($feeQuery, $feeParams)->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die('Database error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Fee Structure Allocation</title>
  @include('admin.partials.links')
</head>

<body>
  <div class="wrapper">
    @include('admin.partials.sidebar')

    <div class="main-panel">
      @include('admin.partials.header')
      <div class="container">
        <div class="page-inner">
          <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
            <div>
              <h2 class="text-dark pb-2 fw-bold">Fee Allocation</h2>
              <h5 class="text-muted">For:
                <?php echo htmlspecialchars(trim($student['first_name'] . ' ' . $student['last_name'])); ?>
                (<?php echo htmlspecialchars(trim(($student['class_name'] ?? 'Unassigned') . ' ' . ($student['class_arm'] ?? ''))); ?>)</h5>
              <h6 class="text-muted">Session:
                <?php echo htmlspecialchars($studentSessionContext); ?>
              </h6>
            </div>
            <div class="ms-md-auto py-2 py-md-0">
              <a href="view_students.php?id=<?php echo $studentId; ?>" class="btn btn-secondary btn-round">Back to
                Profile</a>
            </div>
          </div>

          <?php if ($message !== ''): ?>
          <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
          <?php endif; ?>
          <?php if ($error !== ''): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>

          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <div class="card-title">Applicable Fees</div>
                  <div class="card-category">
                    Matching active fee structures for this student's session, term, class, category, and gender.
                  </div>
                </div>
                <div class="card-body">
                  <form method="POST" action="">
                    <div class="table-responsive">
                      <table class="table table-hover">
                        <thead>
                          <tr>
                            <th>
                              <input type="checkbox" id="select_all_fees">
                            </th>
                            <th>Fee Name</th>
                            <th>Amount (N)</th>
                            <th>Frequency</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if ($feeStructures === []): ?>
                          <tr>
                            <td colspan="7" class="text-center text-muted">No applicable fee structures were found for
                              this student's current session, term, and class.</td>
                          </tr>
                          <?php else: ?>
                          <?php foreach ($feeStructures as $fee): ?>
                          <tr>
                            <td>
                              <input type="checkbox" name="fee_ids[]" value="<?php echo (int) $fee['id']; ?>"
                                class="fee-checkbox" <?php echo !empty($fee['is_allocated']) ? 'disabled' : ''; ?>>
                            </td>
                            <td>
                              <?php echo htmlspecialchars((string) $fee['name']); ?>
                              <?php if (!empty($fee['is_mandatory'])): ?>
                              <span class="badge bg-info ms-1">Mandatory</span>
                              <?php endif; ?>
                            </td>
                            <td><?php echo number_format((float) ($fee['amount'] ?? 0), 2); ?></td>
                            <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($fee['frequency'] ?? '')))); ?></td>
                            <td><?php echo htmlspecialchars((string) ($fee['description'] ?? '')); ?></td>
                            <td>
                              <?php if (!empty($fee['is_allocated'])): ?>
                              <span class="badge bg-success">Allocated</span>
                              <?php if (isset($fee['balance'])): ?>
                              <div class="small text-muted mt-1">Balance:
                                <?php echo number_format((float) $fee['balance'], 2); ?></div>
                              <?php endif; ?>
                              <?php else: ?>
                              <span class="badge bg-warning text-dark">Not Allocated</span>
                              <?php endif; ?>
                            </td>
                            <td>
                              <?php if (!empty($fee['is_allocated'])): ?>
                              <a href="fee_structure.php?student_id=<?php echo $studentId; ?>&deallocate_fee=<?php echo (int) $fee['id']; ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Are you sure you want to de-allocate this fee? This cannot be undone once payment has been recorded.')">
                                <i class="fas fa-times-circle me-1"></i> De-allocate
                              </a>
                              <?php else: ?>
                              <span class="text-muted small">Select to allocate</span>
                              <?php endif; ?>
                            </td>
                          </tr>
                          <?php endforeach; ?>
                          <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                    <div class="card-action">
                      <button type="submit" name="allocate_fees" class="btn btn-primary">
                        <i class="fas fa-check-circle me-2"></i>Allocate Selected Fees
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      @include('admin.partials.footer')
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const selectAll = document.getElementById('select_all_fees');
      const checkboxes = document.querySelectorAll('.fee-checkbox:not(:disabled)');

      if (selectAll) {
        selectAll.addEventListener('change', function () {
          checkboxes.forEach(function (checkbox) {
            checkbox.checked = selectAll.checked;
          });
        });
      }
    });
  </script>
</body>

</html>
