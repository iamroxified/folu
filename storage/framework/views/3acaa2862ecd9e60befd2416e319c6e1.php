<?php
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

if (!isset($_SESSION['adid'])) {
    header('Location: /admin/login.php');
    exit;
}

$feeTypeOptions = schema_enum_values('fee_structures', 'fee_type');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Fee Type Reference</title>
    <?php echo $__env->make('admin.partials.links', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>

<body>
    <div class="wrapper">
        <?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="main-panel">
            <?php echo $__env->make('admin.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left flex-column flex-md-row">
                        <h2 class="text-dark pb-2 fw-bold">Fee Type Reference</h2>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Current Schema Guidance</div>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        The old `fee_type` table is not part of the live database anymore. Fee types are now predefined enum values on `fee_structures.fee_type`.
                                    </div>

                                    <p class="mb-3">Available fee types in this installation:</p>
                                    <ul>
                                        <?php foreach ($feeTypeOptions as $feeType): ?>
                                            <li><code><?php echo htmlspecialchars($feeType); ?></code> - <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $feeType))); ?></li>
                                        <?php endforeach; ?>
                                    </ul>

                                    <a href="add_fees.php" class="btn btn-primary">Create a Fee Structure</a>
                                    <a href="fee_type.php" class="btn btn-secondary">Back to Fee Types</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php echo $__env->make('admin.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>

</html>
<?php /**PATH C:\laragon\www\folu\resources\views/admin/fees/types/add.blade.php ENDPATH**/ ?>