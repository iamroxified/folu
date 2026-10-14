<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');
require_once base_path('db/school_portal_helpers.php');

if (!isset($_SESSION['adid']) && !auth()->check()) {
    header('Location: /admin/login.php');
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$error = '';
$inputName = '';
$inputCode = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $inputName = trim((string) ($_POST['fee_type_name'] ?? ''));
    $rawCode = trim((string) ($_POST['fee_type_code'] ?? ''));
    
    $candidate = $rawCode !== '' ? $rawCode : $inputName;
    $slug = schema_normalize_enum_identifier($candidate);

    if ($slug === '' || strlen($slug) < 2) {
        $error = 'Please enter a valid fee type name or code (at least 2 characters).';
    } elseif (strlen($slug) > 50) {
        $error = 'Fee type code must not exceed 50 characters.';
    } else {
        $existingTypes = schema_enum_values('fee_structures', 'fee_type', true);
        if (in_array($slug, $existingTypes, true)) {
            $error = "The fee type '{$slug}' already exists in the system.";
        } else {
            try {
                schema_add_enum_value('fee_structures', 'fee_type', $slug);
                $_SESSION['flash_message'] = "Fee type '" . ucwords(str_replace('_', ' ', $slug)) . "' ({$slug}) was added successfully.";
                
                if (!headers_sent()) {
                    header('Location: fee_type.php', true, 303);
                    exit;
                } else {
                    echo "<script>window.location.replace('fee_type.php');</script>";
                    exit;
                }
            } catch (Throwable $e) {
                $error = 'Failed to add fee type: ' . $e->getMessage();
            }
        }
    }
}

$feeTypeOptions = schema_enum_values('fee_structures', 'fee_type', true);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Add New Fee Type</title>
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
                        <div>
                            <h2 class="text-dark pb-2 fw-bold">Add New Fee Type</h2>
                            <p class="text-muted mb-0">Create a new fee type for use in fee structures, student allocations, and payment imports.</p>
                        </div>
                        <div class="ms-md-auto py-2 py-md-0">
                            <a href="fee_type.php" class="btn btn-secondary btn-round">
                                <i class="fas fa-arrow-left me-1"></i> Back to Fee Types
                            </a>
                        </div>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-1"></i> <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-7">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light">
                                    <div class="card-title fw-bold">New Fee Type Details</div>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label for="fee_type_name" class="form-label fw-bold">
                                                Fee Type Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="fee_type_name"
                                                   name="fee_type_name"
                                                   placeholder="e.g. Medical Fee, Graduation Fee, Excursion"
                                                   value="<?php echo htmlspecialchars($inputName); ?>"
                                                   required
                                                   autofocus>
                                            <small class="form-text text-muted">A clear, readable title for this fee type.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="fee_type_code" class="form-label fw-bold">
                                                Database Identifier / Slug <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="text"
                                                       class="form-control font-monospace"
                                                       id="fee_type_code"
                                                       name="fee_type_code"
                                                       placeholder="e.g. medical_fee"
                                                       value="<?php echo htmlspecialchars($inputCode); ?>"
                                                       required>
                                            </div>
                                            <small class="form-text text-muted">Lowercase letters, numbers, and underscores only. Automatically generated from name.</small>
                                        </div>

                                        <div class="alert alert-info py-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Fee types are stored as predefined schema enum values on <code>fee_structures.fee_type</code>. Adding a fee type safely updates the database definition and makes it instantly available for fee creation, student allocations, and CSV balance imports.
                                        </div>

                                        <div class="d-flex gap-2 mt-4">
                                            <button type="submit" class="btn btn-primary px-4">
                                                <i class="fas fa-save me-1"></i> Save Fee Type
                                            </button>
                                            <a href="fee_type.php" class="btn btn-outline-secondary px-3">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light">
                                    <div class="card-title fw-bold">Existing Fee Types (<?php echo count($feeTypeOptions); ?>)</div>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small">The following fee types are currently configured:</p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($feeTypeOptions as $feeType): ?>
                                            <span class="badge bg-light text-dark border p-2 font-monospace">
                                                <code><?php echo htmlspecialchars($feeType); ?></code>
                                                <span class="text-muted ms-1">(<?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $feeType))); ?>)</span>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
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
        document.addEventListener('DOMContentLoaded', function () {
            const nameInput = document.getElementById('fee_type_name');
            const codeInput = document.getElementById('fee_type_code');
            let manualEdit = false;

            codeInput.addEventListener('input', function () {
                manualEdit = true;
                this.value = this.value.toLowerCase().replace(/[^a-z0-9_]/g, '_').replace(/_+/g, '_');
            });

            nameInput.addEventListener('input', function () {
                if (!manualEdit) {
                    codeInput.value = this.value
                        .trim()
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, '_')
                        .replace(/^_+|_+$/g, '');
                }
            });
        });
    </script>
</body>

</html>
