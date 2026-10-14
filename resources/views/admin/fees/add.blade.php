<?php
// Start session

require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$feeTypeOptions = schema_enum_values('fee_structures', 'fee_type');
$frequencyOptions = schema_enum_values('fee_structures', 'frequency');
$categoryOptions = schema_enum_values('fee_structures', 'category');
$genderOptions = schema_enum_values('fee_structures', 'gender');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = validate((string) ($_POST['name'] ?? ''));
    $description = validate((string) ($_POST['description'] ?? ''));
    $classId = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT) ?: null;
    $sessionId = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT);
    $category = validate((string) ($_POST['category'] ?? 'NI'));
    $termId = filter_input(INPUT_POST, 'term_id', FILTER_VALIDATE_INT) ?: null;

    // CRITICAL BUSINESS RULE: New Intake fees ignore term
    if ($category === 'NI') {
        $termId = null;
    }

    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $feeType = validate((string) ($_POST['fee_type'] ?? ''));
    $frequency = validate((string) ($_POST['frequency'] ?? ''));
    $gender = validate((string) ($_POST['gender'] ?? 'All'));
    $effectiveFrom = validate((string) ($_POST['effective_from'] ?? ''));
    $effectiveTo = validate((string) ($_POST['effective_to'] ?? ''));
    $isMandatory = (int) ($_POST['is_mandatory'] ?? 1) === 1 ? 1 : 0;
    $isActive = (int) ($_POST['is_active'] ?? 1) === 1 ? 1 : 0;

    if (
        $name === '' ||
        !$sessionId ||
        $amount === false ||
        $effectiveFrom === '' ||
        !in_array($feeType, $feeTypeOptions, true) ||
        !in_array($frequency, $frequencyOptions, true) ||
        !in_array($category, $categoryOptions, true) ||
        !in_array($gender, $genderOptions, true)
    ) {
        $error = 'Please complete all required fee structure fields.';
    } else {
        try {
            // Check duplicates: only block if an active fee structure with the exact same name AND fee type already exists for this exact combination
            $termSql = $termId 
                ? "AND term_id = ?" 
                : "AND (term_id IS NULL OR term_id = 0)";
            $params = [$sessionId, $classId, $classId, $gender, $category, $feeType, $name];
            if ($termId) {
                $params[] = $termId;
            }

            $duplicateCheck = QueryDB(
                "SELECT id, name, fee_type FROM fee_structures 
                 WHERE session_id = ? 
                 AND (class_id = ? OR (class_id IS NULL AND ? IS NULL)) 
                 AND gender = ? 
                 AND category = ? 
                 AND fee_type = ? 
                 AND LOWER(TRIM(name)) = LOWER(TRIM(?)) 
                 AND is_active = 1 
                 {$termSql} 
                 LIMIT 1",
                $params
            )->fetch(PDO::FETCH_ASSOC);

            if ($duplicateCheck) {
                $error = "An active fee structure with this name ('" . htmlspecialchars($name) . "') and fee type ('" . htmlspecialchars($feeType) . "') already exists for this combination.";
            } else {
                $selectedClass = null;
                if ($classId) {
                    $selectedClass = QueryDB(
                        'SELECT class_name, grade_level FROM school_classes WHERE id = ? LIMIT 1',
                        [$classId]
                    )->fetch(PDO::FETCH_ASSOC);
                }

                QueryDB(
                    'INSERT INTO fee_structures (name, description, grade_level, class_name, amount, frequency, fee_type, category, gender, effective_from, effective_to, is_mandatory, is_active, additional_details, created_at, updated_at, session_id, term_id, class_id)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?)',
                    [
                        $name,
                        $description !== '' ? $description : null,
                        $selectedClass['grade_level'] ?? null,
                        $selectedClass['class_name'] ?? null,
                        $amount,
                        $frequency,
                        $feeType,
                        $category,
                        $gender,
                        $effectiveFrom,
                        $effectiveTo !== '' ? $effectiveTo : null,
                        $isMandatory,
                        $isActive,
                        null,
                        $sessionId,
                        $termId,
                        $classId,
                    ]
                );

                $success = 'Fee structure added successfully.';
            }
        } catch (Throwable $e) {
            $error = 'Failed to add fee structure: ' . $e->getMessage();
        }
    }
}

$classes = QueryDB(
    "SELECT id, class_name, section, grade_level
     FROM school_classes
     WHERE status = 'active'
     ORDER BY grade_level, class_name, section"
)->fetchAll(PDO::FETCH_ASSOC);
$sessions = QueryDB("SELECT id, session_name, is_active FROM academic_sessions ORDER BY is_active DESC, start_date DESC")->fetchAll(PDO::FETCH_ASSOC);
$terms = QueryDB("SELECT id, term_name, term_number, is_active FROM terms ORDER BY term_number ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Add Fee Structure</title>
    @include('admin.partials.links')
</head>

<body>
    <div class="wrapper">
        @include('admin.partials.sidebar')

        <div class="main-panel">
            @include('admin.partials.header')
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left flex-column flex-md-row">
                        <h2 class="text-dark pb-2 fw-bold">Add Fee Structure</h2>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Fee Structure Details</div>
                                    <div class="card-category">Note: New Intake (NI) fees are session-based (Term not applicable). Returning (OS) fees require Term.</div>
                                </div>
                                <div class="card-body">
                                    <?php if (isset($error)): ?>
                                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                    <?php endif; ?>
                                    <?php if (isset($success)): ?>
                                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                                    <?php endif; ?>
                                    <form method="POST" action="">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name">Fee Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="name" id="name" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="fee_type">Fee Type <span class="text-danger">*</span></label>
                                                    <select class="form-control" name="fee_type" id="fee_type" required>
                                                        <option value="">Select Fee Type</option>
                                                        <?php foreach ($feeTypeOptions as $option): ?>
                                                            <option value="<?php echo htmlspecialchars($option); ?>"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $option))); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="category">Student Type / Category <span class="text-danger">*</span></label>
                                                    <select class="form-control" name="category" id="category" required onchange="toggleTermField()">
                                                        <option value="NI">New Intake (NI - Session Based)</option>
                                                        <option value="OS">Returning Student (OS - Term Based)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="class_id">Class</label>
                                                    <select class="form-control" name="class_id" id="class_id">
                                                        <option value="">All Classes</option>
                                                        <?php foreach ($classes as $class): ?>
                                                            <option value="<?php echo (int) $class['id']; ?>">
                                                                <?php echo htmlspecialchars(trim($class['class_name'] . ' ' . ($class['section'] ?? ''))); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="session_id">Session <span class="text-danger">*</span></label>
                                                    <select class="form-control" name="session_id" id="session_id" required>
                                                        <option value="">Select Session</option>
                                                        <?php foreach ($sessions as $session): ?>
                                                            <option value="<?php echo (int) $session['id']; ?>" <?php echo (int) ($session['is_active'] ?? 0) === 1 ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($session['session_name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group" id="term_group">
                                                    <label for="term_id">Term <small class="text-muted">(Optional for whole session)</small></label>
                                                    <select class="form-control" name="term_id" id="term_id">
                                                        <option value="">All Terms / Whole Session</option>
                                                        <?php foreach ($terms as $term): ?>
                                                            <option value="<?php echo (int) $term['id']; ?>" <?php echo (int) ($term['is_active'] ?? 0) === 1 ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($term['term_name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="amount">Amount (₦) <span class="text-danger">*</span></label>
                                                    <input type="number" step="0.01" min="0" class="form-control" name="amount" id="amount" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="gender">Gender <span class="text-danger">*</span></label>
                                                    <select class="form-control" name="gender" id="gender" required>
                                                        <option value="All">All Genders</option>
                                                        <option value="M">Male</option>
                                                        <option value="F">Female</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="frequency">Frequency <span class="text-danger">*</span></label>
                                                    <select class="form-control" name="frequency" id="frequency" required>
                                                        <?php foreach ($frequencyOptions as $option): ?>
                                                            <option value="<?php echo htmlspecialchars($option); ?>"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $option))); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="effective_from">Effective From <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control" name="effective_from" id="effective_from" value="<?php echo date('Y-m-d'); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="effective_to">Effective To</label>
                                                    <input type="date" class="form-control" name="effective_to" id="effective_to">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="is_mandatory">Mandatory</label>
                                                    <select class="form-control" name="is_mandatory" id="is_mandatory">
                                                        <option value="1">Yes</option>
                                                        <option value="0">No</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="is_active">Status</label>
                                                    <select class="form-control" name="is_active" id="is_active">
                                                        <option value="1">Active</option>
                                                        <option value="0">Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="description">Description</label>
                                            <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary">Add Fee Structure</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin.partials.footer')

    <script>
        function toggleTermField() {
            var cat = document.getElementById('category').value;
            var termSelect = document.getElementById('term_id');
            var termStar = document.getElementById('term_req_star');

            if (cat === 'NI') {
                termSelect.disabled = true;
                termSelect.value = '';
                termSelect.removeAttribute('required');
                if (termStar) termStar.style.display = 'none';
            } else {
                termSelect.disabled = false;
                termSelect.removeAttribute('required');
                if (termStar) termStar.style.display = 'none';
            }
        }
        document.addEventListener('DOMContentLoaded', toggleTermField);
    </script>
</body>

</html>
