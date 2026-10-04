<?php
// Start session if legacy context
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

require_once base_path('db/config.php');
require_once base_path('db/functions.php');

$feeTypeOptions = schema_enum_values('fee_structures', 'fee_type');
$frequencyOptions = schema_enum_values('fee_structures', 'frequency');
$categoryOptions = schema_enum_values('fee_structures', 'category');
$genderOptions = schema_enum_values('fee_structures', 'gender');

// Get Fee ID
$feeId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: (isset($fee) ? $fee->id : 0);
$feeRecord = null;

if (isset($fee) && $fee instanceof \App\Models\FeeStructure) {
    $feeRecord = $fee;
} elseif ($feeId) {
    $feeRecord = \App\Models\FeeStructure::find($feeId);
}

if (!$feeRecord && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/fees');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'fee_id', FILTER_VALIDATE_INT) ?: ($feeRecord ? $feeRecord->id : 0);
    $targetFee = \App\Models\FeeStructure::find($id);

    if (!$targetFee) {
        $error = 'Fee structure not found.';
    } else {
        $name = validate((string) ($_POST['name'] ?? ''));
        $description = validate((string) ($_POST['description'] ?? ''));
        $classId = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT) ?: null;
        $sessionId = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT);
        $category = validate((string) ($_POST['category'] ?? 'NI'));
        $termId = filter_input(INPUT_POST, 'term_id', FILTER_VALIDATE_INT) ?: null;

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
            ($category === 'OS' && !$termId) ||
            $amount === false ||
            $effectiveFrom === ''
        ) {
            $error = 'Please complete all required fields. Term is required for Returning Students (OS).';
        } else {
            // Duplicate check
            $duplicateCheck = \App\Models\FeeStructure::where('session_id', $sessionId)
                ->where('class_id', $classId)
                ->where('gender', $gender)
                ->where('category', $category)
                ->where('is_active', true)
                ->where('id', '!=', $targetFee->id);

            if ($category === 'OS' && $termId) {
                $duplicateCheck->where('term_id', $termId);
            }

            if ($duplicateCheck->exists()) {
                $error = 'An active fee structure already exists for this exact combination (Session, Class, Gender, Student Type, Term).';
            } else {
                $targetFee->update([
                    'name' => $name,
                    'description' => $description !== '' ? $description : null,
                    'amount' => $amount,
                    'frequency' => $frequency,
                    'fee_type' => $feeType,
                    'category' => $category,
                    'gender' => $gender,
                    'effective_from' => $effectiveFrom,
                    'effective_to' => $effectiveTo !== '' ? $effectiveTo : null,
                    'is_mandatory' => $isMandatory,
                    'is_active' => $isActive,
                    'session_id' => $sessionId,
                    'term_id' => $termId,
                    'class_id' => $classId,
                ]);

                $feeRecord = $targetFee;
                $success = 'Fee structure updated successfully.';
            }
        }
    }
}

$classes = QueryDB("SELECT id, class_name, section, grade_level FROM school_classes WHERE status = 'active' ORDER BY grade_level, class_name, section")->fetchAll(PDO::FETCH_ASSOC);
$sessions = QueryDB("SELECT id, session_name, is_active FROM academic_sessions ORDER BY is_active DESC, start_date DESC")->fetchAll(PDO::FETCH_ASSOC);
$terms = QueryDB("SELECT id, term_name, term_number, is_active FROM terms ORDER BY term_number ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Edit Fee Structure</title>
    @include('admin.partials.links')
</head>

<body>
    <div class="wrapper">
        @include('admin.partials.sidebar')

        <div class="main-panel">
            @include('admin.partials.header')
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pb-3">
                        <h2 class="text-dark pb-2 fw-bold">Edit Fee Structure</h2>
                        <div class="ms-md-auto">
                            <a href="{{ route('admin.fees') }}" class="btn btn-secondary btn-round"><i class="fas fa-arrow-left me-1"></i> Back to Fees List</a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Modify Fee Structure Details</div>
                                </div>
                                <div class="card-body">
                                    <?php if (isset($error)): ?>
                                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                    <?php endif; ?>
                                    <?php if (isset($success)): ?>
                                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                                    <?php endif; ?>

                                    @if(session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif

                                    @if(session('error'))
                                        <div class="alert alert-danger">{{ session('error') }}</div>
                                    @endif

                                    <form method="POST" action="">
                                        @csrf
                                        <input type="hidden" name="fee_id" value="<?php echo (int) ($feeRecord ? $feeRecord->id : 0); ?>">

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name">Fee Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="name" id="name" value="<?php echo htmlspecialchars((string)($feeRecord ? $feeRecord->name : '')); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="fee_type">Fee Type <span class="text-danger">*</span></label>
                                                    <select class="form-control" name="fee_type" id="fee_type" required>
                                                        <?php foreach ($feeTypeOptions as $option): ?>
                                                            <option value="<?php echo htmlspecialchars($option); ?>" <?php echo ($feeRecord && $feeRecord->fee_type === $option) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $option))); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="category">Category <span class="text-danger">*</span></label>
                                                    <select class="form-control" name="category" id="category" required onchange="toggleTermField()">
                                                        <option value="NI" <?php echo ($feeRecord && $feeRecord->category === 'NI') ? 'selected' : ''; ?>>New Intake (NI - Session Based)</option>
                                                        <option value="OS" <?php echo ($feeRecord && $feeRecord->category === 'OS') ? 'selected' : ''; ?>>Returning Student (OS - Term Based)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="class_id">Class</label>
                                                    <select class="form-control" name="class_id" id="class_id">
                                                        <option value="">All Classes</option>
                                                        <?php foreach ($classes as $class): ?>
                                                            <option value="<?php echo (int) $class['id']; ?>" <?php echo ($feeRecord && $feeRecord->class_id == $class['id']) ? 'selected' : ''; ?>>
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
                                                            <option value="<?php echo (int) $session['id']; ?>" <?php echo ($feeRecord && $feeRecord->session_id == $session['id']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($session['session_name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group" id="term_group">
                                                    <label for="term_id">Term <span class="text-danger" id="term_req_star">*</span></label>
                                                    <select class="form-control" name="term_id" id="term_id">
                                                        <option value="">Select Term</option>
                                                        <?php foreach ($terms as $term): ?>
                                                            <option value="<?php echo (int) $term['id']; ?>" <?php echo ($feeRecord && $feeRecord->term_id == $term['id']) ? 'selected' : ''; ?>>
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
                                                    <input type="number" step="0.01" min="0" class="form-control" name="amount" id="amount" value="<?php echo htmlspecialchars((string)($feeRecord ? $feeRecord->amount : '0')); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="gender">Gender <span class="text-danger">*</span></label>
                                                    <select class="form-control" name="gender" id="gender" required>
                                                        <option value="All" <?php echo ($feeRecord && $feeRecord->gender === 'All') ? 'selected' : ''; ?>>All Genders</option>
                                                        <option value="M" <?php echo ($feeRecord && $feeRecord->gender === 'M') ? 'selected' : ''; ?>>Male</option>
                                                        <option value="F" <?php echo ($feeRecord && $feeRecord->gender === 'F') ? 'selected' : ''; ?>>Female</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="frequency">Frequency <span class="text-danger">*</span></label>
                                                    <select class="form-control" name="frequency" id="frequency" required>
                                                        <?php foreach ($frequencyOptions as $option): ?>
                                                            <option value="<?php echo htmlspecialchars($option); ?>" <?php echo ($feeRecord && $feeRecord->frequency === $option) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $option))); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="effective_from">Effective From <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control" name="effective_from" id="effective_from" value="<?php echo htmlspecialchars((string)($feeRecord && $feeRecord->effective_from ? $feeRecord->effective_from->format('Y-m-d') : date('Y-m-d'))); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="effective_to">Effective To</label>
                                                    <input type="date" class="form-control" name="effective_to" id="effective_to" value="<?php echo htmlspecialchars((string)($feeRecord && $feeRecord->effective_to ? $feeRecord->effective_to->format('Y-m-d') : '')); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="is_mandatory">Mandatory</label>
                                                    <select class="form-control" name="is_mandatory" id="is_mandatory">
                                                        <option value="1" <?php echo ($feeRecord && $feeRecord->is_mandatory) ? 'selected' : ''; ?>>Yes</option>
                                                        <option value="0" <?php echo ($feeRecord && !$feeRecord->is_mandatory) ? 'selected' : ''; ?>>No</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="is_active">Status</label>
                                                    <select class="form-control" name="is_active" id="is_active">
                                                        <option value="1" <?php echo ($feeRecord && $feeRecord->is_active) ? 'selected' : ''; ?>>Active</option>
                                                        <option value="0" <?php echo ($feeRecord && !$feeRecord->is_active) ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="description">Description</label>
                                            <textarea class="form-control" name="description" id="description" rows="3"><?php echo htmlspecialchars((string)($feeRecord ? $feeRecord->description : '')); ?></textarea>
                                        </div>

                                        <div class="d-flex justify-content-between pt-3">
                                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Fee Structure</button>
                                            <a href="{{ route('admin.fees') }}" class="btn btn-secondary">Cancel</a>
                                        </div>
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
                termStar.style.display = 'none';
            } else {
                termSelect.disabled = false;
                termSelect.setAttribute('required', 'required');
                termStar.style.display = 'inline';
            }
        }
        document.addEventListener('DOMContentLoaded', toggleTermField);
    </script>
</body>

</html>
