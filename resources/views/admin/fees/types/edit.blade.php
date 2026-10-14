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

$feeTypeOptions = array_values(schema_enum_values('fee_structures', 'fee_type', true));
$requestedId = $_REQUEST['id'] ?? null;
$selectedIndex = is_numeric($requestedId) ? ((int) $requestedId - 1) : null;
$selectedType = $requestedId !== null && !is_numeric($requestedId)
    ? (string) $requestedId
    : ($selectedIndex !== null && isset($feeTypeOptions[$selectedIndex]) ? $feeTypeOptions[$selectedIndex] : null);

if ($selectedType === null || !in_array($selectedType, $feeTypeOptions, true)) {
    $_SESSION['flash_error'] = "Fee type not found.";
    header('Location: fee_type.php');
    exit;
}

$usageCount = (int) QueryDB(
    'SELECT COUNT(*) FROM fee_structures WHERE fee_type = ?',
    [$selectedType]
)->fetchColumn();

$referencingStructures = QueryDB(
    'SELECT id, name, amount, session_id, term_id FROM fee_structures WHERE fee_type = ? ORDER BY id DESC LIMIT 10',
    [$selectedType]
)->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$displayName = ucwords(str_replace('_', ' ', $selectedType));

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = (string) ($_POST['action'] ?? 'rename');

    if ($action === 'delete') {
        if ($usageCount > 0) {
            $error = "Cannot delete '{$selectedType}' because it is currently used by {$usageCount} fee structure(s).";
        } else {
            try {
                schema_delete_enum_value('fee_structures', 'fee_type', $selectedType);
                $_SESSION['flash_message'] = "Fee type '{$selectedType}' was deleted successfully.";
                
                if (!headers_sent()) {
                    header('Location: fee_type.php', true, 303);
                    exit;
                } else {
                    echo "<script>window.location.replace('fee_type.php');</script>";
                    exit;
                }
            } catch (Throwable $e) {
                $error = 'Failed to delete fee type: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'rename') {
        $inputName = trim((string) ($_POST['fee_type_name'] ?? ''));
        $rawCode = trim((string) ($_POST['fee_type_code'] ?? ''));
        $candidate = $rawCode !== '' ? $rawCode : $inputName;
        $newSlug = schema_normalize_enum_identifier($candidate);

        if ($newSlug === '' || strlen($newSlug) < 2) {
            $error = 'Please enter a valid fee type name or code (at least 2 characters).';
        } elseif (strlen($newSlug) > 50) {
            $error = 'Fee type code must not exceed 50 characters.';
        } elseif ($newSlug === $selectedType) {
            $_SESSION['flash_message'] = "No changes were made to fee type '{$selectedType}'.";
            header('Location: fee_type.php');
            exit;
        } else {
            try {
                schema_rename_enum_value('fee_structures', 'fee_type', $selectedType, $newSlug);
                $migratedMsg = $usageCount > 0 ? " ({$usageCount} existing fee structure(s) were automatically updated)" : "";
                $_SESSION['flash_message'] = "Fee type renamed from '{$selectedType}' to '{$newSlug}' successfully{$migratedMsg}.";
                
                if (!headers_sent()) {
                    header('Location: fee_type.php', true, 303);
                    exit;
                } else {
                    echo "<script>window.location.replace('fee_type.php');</script>";
                    exit;
                }
            } catch (Throwable $e) {
                $error = 'Failed to rename fee type: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Edit Fee Type: <?php echo htmlspecialchars($displayName); ?></title>
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
              <h2 class="text-dark pb-2 fw-bold">Edit Fee Type</h2>
              <p class="text-muted mb-0">Modify or rename fee type <code><?php echo htmlspecialchars($selectedType); ?></code>.</p>
            </div>
            <div class="ms-md-auto py-2 py-md-0 d-flex gap-2">
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
                  <div class="card-title fw-bold">Edit Fee Type Details</div>
                </div>
                <div class="card-body">
                  <form method="POST" action="">
                    <input type="hidden" name="action" value="rename">

                    <div class="mb-3">
                      <label for="fee_type_name" class="form-label fw-bold">
                        Display Name <span class="text-danger">*</span>
                      </label>
                      <input type="text"
                             class="form-control"
                             id="fee_type_name"
                             name="fee_type_name"
                             value="<?php echo htmlspecialchars($displayName); ?>"
                             required>
                      <small class="form-text text-muted">A clear, readable title for this fee type.</small>
                    </div>

                    <div class="mb-3">
                      <label for="fee_type_code" class="form-label fw-bold">
                        Database Identifier / Slug <span class="text-danger">*</span>
                      </label>
                      <input type="text"
                             class="form-control font-monospace"
                             id="fee_type_code"
                             name="fee_type_code"
                             value="<?php echo htmlspecialchars($selectedType); ?>"
                             required>
                      <small class="form-text text-muted">The exact enum value stored in the database. Renaming this safely alters the enum and updates all referencing fee structures.</small>
                    </div>

                    <div class="alert alert-info py-2">
                      <i class="fas fa-info-circle me-1"></i>
                      Renaming this fee type will safely migrate all <strong><?php echo $usageCount; ?></strong> fee structure(s) currently using <code><?php echo htmlspecialchars($selectedType); ?></code> to the new identifier without data loss.
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                      <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                          <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                        <a href="fee_type.php" class="btn btn-outline-secondary px-3">Cancel</a>
                      </div>
                    </div>
                  </form>
                </div>
              </div>

              <?php if ($usageCount === 0): ?>
                <div class="card shadow-sm border-danger mt-3">
                  <div class="card-header bg-danger text-white">
                    <div class="card-title fw-bold"><i class="fas fa-trash-alt me-1"></i> Delete Fee Type</div>
                  </div>
                  <div class="card-body">
                    <p class="text-muted">This fee type is not currently in use by any fee structure. You can permanently remove it from the schema enum.</p>
                    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to permanently delete fee type \'<?php echo htmlspecialchars($selectedType); ?>\'?');">
                      <input type="hidden" name="action" value="delete">
                      <button type="submit" class="btn btn-outline-danger">
                        <i class="fas fa-trash-alt me-1"></i> Delete Fee Type '<?php echo htmlspecialchars($selectedType); ?>'
                      </button>
                    </form>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <div class="col-md-5">
              <div class="card shadow-sm">
                <div class="card-header bg-light">
                  <div class="card-title fw-bold">Usage Summary</div>
                </div>
                <div class="card-body">
                  <table class="table table-bordered">
                    <tr>
                      <th style="width: 160px;">Current Slug</th>
                      <td><code><?php echo htmlspecialchars($selectedType); ?></code></td>
                    </tr>
                    <tr>
                      <th>Display Name</th>
                      <td><?php echo htmlspecialchars($displayName); ?></td>
                    </tr>
                    <tr>
                      <th>Active Structures</th>
                      <td>
                        <?php if ($usageCount > 0): ?>
                          <span class="badge bg-success"><?php echo $usageCount; ?> structure(s)</span>
                        <?php else: ?>
                          <span class="badge bg-secondary">Unused</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  </table>

                  <?php if (!empty($referencingStructures)): ?>
                    <h6 class="fw-bold mt-3 mb-2">Sample Structures Using It:</h6>
                    <ul class="list-group list-group-flush small">
                      <?php foreach ($referencingStructures as $rs): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                          <span><?php echo htmlspecialchars($rs['name']); ?></span>
                          <span class="fw-bold">₦<?php echo number_format((float) $rs['amount'], 2); ?></span>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>

                  <div class="mt-3">
                    <a href="add_fees.php" class="btn btn-outline-primary btn-sm w-100">
                      <i class="fas fa-plus me-1"></i> Create Fee Structure with this Type
                    </a>
                  </div>
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
