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
    @include('admin.partials.footer')
</body>

</html>
