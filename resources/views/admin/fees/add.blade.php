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
    $sessionId = filter_var($_POST['session_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
    $category = validate((string) ($_POST['category'] ?? 'NI'));
    $termId = filter_var($_POST['term_id'] ?? null, FILTER_VALIDATE_INT) ?: null;

    // CRITICAL BUSINESS RULE: New Intake fees ignore term
    if ($category === 'NI') {
        $termId = null;
    }

    $amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_FLOAT);
    $feeType = validate((string) ($_POST['fee_type'] ?? ''));
    $frequency = validate((string) ($_POST['frequency'] ?? ''));
    $gender = validate((string) ($_POST['gender'] ?? 'All'));
    $effectiveFrom = validate((string) ($_POST['effective_from'] ?? ''));
    $effectiveTo = validate((string) ($_POST['effective_to'] ?? ''));
    $isMandatory = (int) ($_POST['is_mandatory'] ?? 1) === 1 ? 1 : 0;
    $isActive = (int) ($_POST['is_active'] ?? 1) === 1 ? 1 : 0;
    $prefixClassName = !empty($_POST['prefix_class_name']);
    $applyAllClasses = !empty($_POST['apply_all_classes']);

    // Multiple class selection resolution
    $targetClassIds = [];
    if ($applyAllClasses) {
        $targetClassIds = [null];
    } elseif (!empty($_POST['class_ids']) && is_array($_POST['class_ids'])) {
        $targetClassIds = array_values(array_unique(array_filter(array_map('intval', $_POST['class_ids']))));
        if (empty($targetClassIds)) {
            $targetClassIds = [null];
        }
    } elseif (!empty($_POST['class_id'])) {
        $targetClassIds = [(int) $_POST['class_id']];
    } else {
        $targetClassIds = [null];
    }

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
            $created = [];
            $skipped = [];

            // Pre-load class lookup table
            $classesById = [];
            $allSchoolClasses = QueryDB('SELECT id, class_name, section, grade_level FROM school_classes')->fetchAll(PDO::FETCH_ASSOC);
            foreach ($allSchoolClasses as $sc) {
                $classesById[(int) $sc['id']] = $sc;
            }

            foreach ($targetClassIds as $classId) {
                $selectedClass = $classId && isset($classesById[$classId]) ? $classesById[$classId] : null;
                if ($selectedClass) {
                    $rawClassName = trim($selectedClass['class_name'] ?? '');
                    $rawSection = trim($selectedClass['section'] ?? '');
                    if ($rawSection !== '' && substr(strtoupper($rawClassName), -strlen($rawSection)) !== strtoupper($rawSection)) {
                        $classNameStr = $rawClassName . ' ' . $rawSection;
                    } else {
                        $classNameStr = $rawClassName;
                    }
                } else {
                    $classNameStr = 'All Classes';
                }

                // Determine fee name for this specific class
                if ($selectedClass) {
                    if (stripos($name, '{class}') !== false || stripos($name, '{class_name}') !== false) {
                        $feeNameForClass = trim(str_ireplace(['{class_name}', '{class}'], $classNameStr, $name));
                    } elseif ($prefixClassName) {
                        $feeNameForClass = $classNameStr . ' - ' . $name;
                    } else {
                        $feeNameForClass = $name;
                    }
                } else {
                    $feeNameForClass = trim(str_ireplace(['{class_name}', '{class}'], 'All Classes', $name));
                }

                // Check duplicates
                $termSql = $termId 
                    ? "AND term_id = ?" 
                    : "AND (term_id IS NULL OR term_id = 0)";
                $params = [$sessionId, $classId, $classId, $gender, $category, $feeType, $feeNameForClass];
                if ($termId) {
                    $params[] = $termId;
                }

                $duplicateCheck = QueryDB(
                    "SELECT id, name FROM fee_structures 
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
                    $skipped[] = "{$classNameStr} ('{$feeNameForClass}')";
                    continue;
                }

                QueryDB(
                    'INSERT INTO fee_structures (name, description, grade_level, class_name, amount, frequency, fee_type, category, gender, effective_from, effective_to, is_mandatory, is_active, additional_details, created_at, updated_at, session_id, term_id, class_id)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?)',
                    [
                        $feeNameForClass,
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

                $created[] = $classNameStr;
            }

            if (count($created) > 0) {
                $success = "Successfully added fee structure for " . count($created) . " class(es): " . implode(', ', $created) . ".";
                if (count($skipped) > 0) {
                    $success .= " (Note: " . count($skipped) . " skipped as active duplicates: " . implode('; ', $skipped) . ")";
                }
            } elseif (count($skipped) > 0) {
                $error = "No fee structure added. An active fee structure already exists for: " . implode('; ', $skipped) . ".";
            } else {
                $error = "No valid class was selected.";
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

// Helper to group classes into meaningful categories for easy multi-selection
$gradeGroups = [];
foreach ($classes as $class) {
    $cName = $class['class_name'] ?? '';
    $gLevel = $class['grade_level'] ?? '';
    $combined = strtoupper($cName . ' ' . $gLevel);

    if (strpos($combined, 'NUR') !== false || strpos($combined, 'CRECHE') !== false || strpos($combined, 'PRE') !== false || strpos($combined, 'KG') !== false) {
        $group = 'Nursery & Pre-Primary';
    } elseif (strpos($combined, 'PRI') !== false || strpos($combined, 'BASIC') !== false) {
        $group = 'Primary Classes';
    } elseif (strpos($combined, 'JSS') !== false || strpos($combined, 'JUNIOR') !== false) {
        $group = 'Junior Secondary (JSS)';
    } elseif (strpos($combined, 'SSS') !== false || strpos($combined, 'SENIOR') !== false) {
        $group = 'Senior Secondary (SSS)';
    } else {
        $group = 'Other Classes';
    }

    if (!isset($gradeGroups[$group])) {
        $gradeGroups[$group] = [];
    }
    $gradeGroups[$group][] = $class;
}

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
                                    <div class="card-category">Note: New Intake (NI) fees are session-based (Term not applicable). Returning (OS) and All Students fees are term-based (or whole session).</div>
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
                                                     <input type="text" class="form-control" name="name" id="name" required placeholder="e.g. Tuition Fee, or {class} School fees" oninput="updateFeeNamePreview()">
                                                     <div class="d-flex flex-wrap align-items-center justify-content-between mt-1">
                                                         <div class="form-check mb-0 ps-0">
                                                             <input class="form-check-input ms-0 me-1" type="checkbox" name="prefix_class_name" id="prefix_class_name" value="1" onchange="updateFeeNamePreview()">
                                                             <label class="form-check-label small text-muted" for="prefix_class_name">
                                                                 Auto-prefix class name (e.g. <em>"Primary 1 - Tuition Fee"</em>)
                                                             </label>
                                                         </div>
                                                         <small class="text-muted"><i class="fas fa-info-circle me-1"></i>You can use <code>{class}</code> placeholder</small>
                                                     </div>
                                                     <div id="fee_name_preview_box" class="small mt-2 p-2 rounded bg-light border" style="display:none;">
                                                         <span class="text-muted fw-bold">Live Name Preview:</span>
                                                         <div id="fee_name_preview_list" class="mt-1 d-flex flex-wrap align-items-center"></div>
                                                     </div>
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
                                             <div class="col-md-4">
                                                 <div class="form-group">
                                                     <label for="category">Student Type / Category <span class="text-danger">*</span></label>
                                                     <select class="form-control" name="category" id="category" required onchange="toggleTermField()">
                                                         <option value="NI">New Intake (NI - Session Based)</option>
                                                         <option value="OS">Returning Student (OS - Term Based)</option>
                                                         <option value="ALL">All Students (New Intake & Returning - Per Term)</option>
                                                     </select>
                                                 </div>
                                             </div>
                                            <div class="col-md-4">
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
                                            <div class="col-md-4">
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

                                        <!-- Multi-Class Selection Card -->
                                        <div class="card mb-4 border shadow-none bg-light">
                                            <div class="card-body p-3">
                                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                                                    <div>
                                                        <label class="form-label fw-bold mb-0 text-dark">
                                                            <i class="fas fa-school text-primary me-1"></i> Target Class(es)
                                                        </label>
                                                        <small class="text-muted d-block">Choose single, multiple, or all classes to apply this fee structure to.</small>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                                                        <span class="badge bg-warning text-dark" id="selectedClassCountBadge">0 classes selected (Will default to School-wide)</span>
                                                    </div>
                                                </div>

                                                <div class="mb-3 p-2 bg-white rounded border">
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input" type="checkbox" name="apply_all_classes" id="apply_all_classes" value="1" onchange="toggleSchoolWideOption(this)">
                                                        <label class="form-check-label fw-bold text-primary" for="apply_all_classes">
                                                            <i class="fas fa-globe me-1"></i> Apply as School-wide Fee (All Classes / General)
                                                        </label>
                                                        <div class="small text-muted ps-1">Creates a single universal fee structure (class_id = NULL) applicable school-wide.</div>
                                                    </div>
                                                </div>

                                                <div id="class_selection_container">
                                                    <!-- Quick select buttons -->
                                                    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
                                                        <span class="small fw-bold text-muted me-1">Quick Select:</span>
                                                        <button type="button" class="btn btn-xs btn-outline-primary" onclick="selectAllClasses(true)">
                                                            <i class="fas fa-check-square me-1"></i> Select All Classes
                                                        </button>
                                                        <?php foreach ($gradeGroups as $groupName => $groupClasses): ?>
                                                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="selectClassGroup('<?php echo htmlspecialchars(addslashes($groupName)); ?>')">
                                                                <?php echo htmlspecialchars($groupName); ?> (<?php echo count($groupClasses); ?>)
                                                            </button>
                                                        <?php endforeach; ?>
                                                        <button type="button" class="btn btn-xs btn-outline-danger" onclick="selectAllClasses(false)">
                                                            <i class="fas fa-times me-1"></i> Clear
                                                        </button>
                                                    </div>

                                                    <!-- Grouped class checkboxes -->
                                                    <div class="row g-3">
                                                        <?php foreach ($gradeGroups as $groupName => $groupClasses): ?>
                                                            <div class="col-md-4 col-sm-6">
                                                                <div class="bg-white rounded border p-2 h-100">
                                                                    <div class="fw-bold text-secondary small border-bottom pb-1 mb-2 d-flex justify-content-between align-items-center">
                                                                        <span><?php echo htmlspecialchars($groupName); ?></span>
                                                                        <a href="javascript:void(0)" class="small text-primary text-decoration-none fw-bold" onclick="selectClassGroup('<?php echo htmlspecialchars(addslashes($groupName)); ?>')">Toggle All</a>
                                                                    </div>
                                                                    <div class="d-flex flex-column gap-1">
                                                                        <?php 
                                                                            foreach ($groupClasses as $cls): 
                                                                                $rCName = trim($cls['class_name'] ?? '');
                                                                                $rSec = trim($cls['section'] ?? '');
                                                                                $dispName = ($rSec !== '' && substr(strtoupper($rCName), -strlen($rSec)) !== strtoupper($rSec)) ? $rCName . ' ' . $rSec : $rCName;
                                                                        ?>
                                                                            <div class="form-check">
                                                                                <input class="form-check-input class-checkbox" 
                                                                                       type="checkbox" 
                                                                                       name="class_ids[]" 
                                                                                       id="class_<?php echo (int) $cls['id']; ?>" 
                                                                                       value="<?php echo (int) $cls['id']; ?>" 
                                                                                       data-group="<?php echo htmlspecialchars($groupName); ?>"
                                                                                       data-name="<?php echo htmlspecialchars($dispName); ?>"
                                                                                       onchange="onClassCheckboxChanged()">
                                                                                <label class="form-check-label small" for="class_<?php echo (int) $cls['id']; ?>">
                                                                                    <?php echo htmlspecialchars($dispName); ?>
                                                                                </label>
                                                                            </div>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
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

        function toggleSchoolWideOption(elem) {
            var isSchoolWide = elem.checked;
            var container = document.getElementById('class_selection_container');
            var classCheckboxes = document.querySelectorAll('.class-checkbox');
            var badge = document.getElementById('selectedClassCountBadge');

            if (isSchoolWide) {
                classCheckboxes.forEach(function(cb) {
                    cb.checked = false;
                    cb.disabled = true;
                });
                container.style.opacity = '0.5';
                container.style.pointerEvents = 'none';
                badge.className = 'badge bg-secondary';
                badge.textContent = 'School-wide (All Classes)';
            } else {
                classCheckboxes.forEach(function(cb) {
                    cb.disabled = false;
                });
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
                onClassCheckboxChanged();
            }
            updateFeeNamePreview();
        }

        function selectAllClasses(checked) {
            var schoolWide = document.getElementById('apply_all_classes');
            if (schoolWide && schoolWide.checked) {
                schoolWide.checked = false;
                toggleSchoolWideOption(schoolWide);
            }
            var classCheckboxes = document.querySelectorAll('.class-checkbox');
            classCheckboxes.forEach(function(cb) {
                cb.checked = checked;
            });
            onClassCheckboxChanged();
        }

        function selectClassGroup(groupName) {
            var schoolWide = document.getElementById('apply_all_classes');
            if (schoolWide && schoolWide.checked) {
                schoolWide.checked = false;
                toggleSchoolWideOption(schoolWide);
            }
            var groupCheckboxes = document.querySelectorAll('.class-checkbox[data-group="' + CSS.escape(groupName) + '"]');
            var allChecked = true;
            groupCheckboxes.forEach(function(cb) {
                if (!cb.checked) allChecked = false;
            });
            groupCheckboxes.forEach(function(cb) {
                cb.checked = !allChecked;
            });
            onClassCheckboxChanged();
        }

        function onClassCheckboxChanged() {
            var checked = document.querySelectorAll('.class-checkbox:checked');
            var count = checked.length;
            var badge = document.getElementById('selectedClassCountBadge');
            var schoolWide = document.getElementById('apply_all_classes');

            if (schoolWide && schoolWide.checked) {
                badge.className = 'badge bg-secondary';
                badge.textContent = 'School-wide (All Classes)';
            } else if (count === 0) {
                badge.className = 'badge bg-warning text-dark';
                badge.textContent = '0 classes selected (Will default to School-wide)';
            } else {
                badge.className = 'badge bg-primary';
                badge.textContent = count + ' class(es) selected';
            }
            updateFeeNamePreview();
        }

        function updateFeeNamePreview() {
            var nameInput = document.getElementById('name');
            var prefixCheck = document.getElementById('prefix_class_name');
            var schoolWide = document.getElementById('apply_all_classes');
            var previewBox = document.getElementById('fee_name_preview_box');
            var previewList = document.getElementById('fee_name_preview_list');

            var rawName = nameInput ? nameInput.value.trim() : '';
            if (!rawName) {
                previewBox.style.display = 'none';
                return;
            }

            var checked = Array.from(document.querySelectorAll('.class-checkbox:checked'));
            var isSchoolWide = schoolWide && schoolWide.checked;
            var shouldPrefix = prefixCheck && prefixCheck.checked;

            var previews = [];
            if (isSchoolWide || checked.length === 0) {
                var n = rawName.replace(/\{class_name\}|\{class\}/gi, 'All Classes');
                previews.push(n + ' <span class="badge bg-light text-muted border ms-1">School-wide / All Classes</span>');
            } else {
                checked.forEach(function(cb) {
                    var cName = cb.getAttribute('data-name') || '';
                    var feeTitle = rawName;
                    if (/\{class_name\}|\{class\}/i.test(feeTitle)) {
                        feeTitle = feeTitle.replace(/\{class_name\}|\{class\}/gi, cName);
                    } else if (shouldPrefix) {
                        feeTitle = cName + ' - ' + feeTitle;
                    }
                    previews.push(feeTitle);
                });
            }

            if (previews.length > 0) {
                previewBox.style.display = 'block';
                if (previews.length <= 4) {
                    previewList.innerHTML = previews.map(function(p) {
                        return '<span class="badge bg-white text-dark border me-1 mb-1 p-2">' + p + '</span>';
                    }).join('');
                } else {
                    var firstThree = previews.slice(0, 3).map(function(p) {
                        return '<span class="badge bg-white text-dark border me-1 mb-1 p-2">' + p + '</span>';
                    }).join('');
                    var remaining = previews.length - 3;
                    previewList.innerHTML = firstThree + ' <span class="badge bg-primary text-white p-2 mb-1">+' + remaining + ' more class(es)</span>';
                }
            } else {
                previewBox.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            toggleTermField();
            onClassCheckboxChanged();
        });
    </script>
</body>

</html>
