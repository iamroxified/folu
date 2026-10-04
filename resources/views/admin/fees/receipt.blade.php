<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Include database configuration and functions
require_once base_path('db/config.php');
require_once base_path('db/functions.php');

global $pdo;
if (!isset($pdo) || !($pdo instanceof PDO)) {
    if (class_exists('\Illuminate\Support\Facades\DB')) {
        try {
            $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
        } catch (\Throwable $t) {}
    }
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        $pdo = $GLOBALS['pdo'] ?? null;
    }
}

// Check if user is logged in
if (!isset($_SESSION['adid']) && !auth()->check()) {
    if (!headers_sent()) {
        header('Location: /admin/login.php');
        exit;
    }
}

// Get payment details
$receipt_number = trim((string) ($_GET['receipt'] ?? request('receipt') ?? ''));
if ($receipt_number === '') {
    die('Receipt number is required.');
}

// Fetch school settings for logo and school information
try {
    $settings_stmt = $pdo->query("SELECT * FROM school_settings LIMIT 1");
    $schoolSettings = $settings_stmt ? $settings_stmt->fetch(PDO::FETCH_ASSOC) : [];
} catch (\Throwable $t) {
    $schoolSettings = [];
}

$schoolName = !empty($schoolSettings['school_name']) ? $schoolSettings['school_name'] : 'Folu International Schools';
$schoolAddress = !empty($schoolSettings['school_address']) ? $schoolSettings['school_address'] : 'P.O Box 37, Itedo-Ijowa, Isanlu Kogi State Nigeria';
$schoolPhone = !empty($schoolSettings['school_phone']) ? $schoolSettings['school_phone'] : '+234 816 535 4191';
$schoolEmail = !empty($schoolSettings['school_email']) ? $schoolSettings['school_email'] : 'info@foluinternationalschools.com.ng';
$schoolMotto = !empty($schoolSettings['school_motto']) ? $schoolSettings['school_motto'] : 'Excellence in Knowledge & Character';
$logoUrl = !empty($schoolSettings['school_logo']) ? (str_starts_with($schoolSettings['school_logo'], '/') ? $schoolSettings['school_logo'] : '/storage/' . $schoolSettings['school_logo']) : '/images/folu-logo.png';

try {
    $hasClassTable = schema_has_table('classes');
    $hasSchoolClassesTable = schema_has_table('school_classes');

    $classSelectName = "COALESCE(";
    if ($hasSchoolClassesTable) {
        $classSelectName .= "sc.class_name, ";
    }
    if ($hasClassTable) {
        $classSelectName .= "c.class_name, ";
    }
    $classSelectName .= "'Not Assigned') AS class_name";

    $classSelectArm = "COALESCE(";
    if ($hasSchoolClassesTable) {
        $classSelectArm .= "sc.section, ";
    }
    if ($hasClassTable) {
        $classSelectArm .= "c.class_arm, ";
    }
    $classSelectArm .= "'') AS class_arm";

    $joins = "JOIN student_fees sf ON p.payable_type = 'student_fee' AND p.payable_id = sf.id\n";
    $joins .= "        JOIN students s ON sf.student_id = s.id\n";
    $joins .= "        LEFT JOIN fee_structures fs ON sf.fee_structure_id = fs.id\n";

    if ($hasSchoolClassesTable) {
        $scOn = [];
        if (schema_has_column('students', 'current_class_id')) $scOn[] = "s.current_class_id = sc.id";
        if (schema_has_column('students', 'class_link')) $scOn[] = "s.class_link = sc.id";
        if (!empty($scOn)) {
            $joins .= "        LEFT JOIN school_classes sc ON (" . implode(" OR ", $scOn) . ")\n";
        }
    }

    if ($hasClassTable) {
        $cOn = [];
        if (schema_has_column('students', 'current_class_id')) $cOn[] = "s.current_class_id = c.id";
        if (schema_has_column('students', 'class_link')) $cOn[] = "s.class_link = c.id";
        if (!empty($cOn)) {
            $joins .= "        LEFT JOIN classes c ON (" . implode(" OR ", $cOn) . ")\n";
        }
    }

    if (schema_has_table('academic_sessions')) {
        $joins .= "        LEFT JOIN academic_sessions ac ON fs.session_id = ac.id\n";
    }

    if (schema_has_table('terms')) {
        $joins .= "        LEFT JOIN terms t ON fs.term_id = t.id\n";
    } elseif (schema_has_table('academic_terms')) {
        $joins .= "        LEFT JOIN academic_terms t ON fs.term_id = t.id\n";
    }

    $termSelect = schema_has_table('terms') || schema_has_table('academic_terms') ? "t.term_name" : "NULL AS term_name";
    $sessionSelect = schema_has_table('academic_sessions') ? "ac.session_name" : "NULL AS session_name";

    $payment_stmt = $pdo->prepare("
        SELECT 
            p.*,
            p.payment_reference AS receipt_number,
            p.amount AS transaction_amount,
            p.description AS payment_description,
            p.payment_method,
            p.payment_date,
            s.first_name,
            s.last_name,
            s.other_names,
            s.admission_no,
            {$classSelectName},
            {$classSelectArm},
            sf.amount_due,
            sf.amount_paid AS total_fee_paid,
            sf.balance AS fee_balance,
            sf.status AS fee_status,
            fs.description AS fee_description,
            COALESCE(fs.name, fs.description, 'School Fee') AS type_name,
            {$sessionSelect},
            {$termSelect}
        FROM payments p
        {$joins}
        WHERE p.payment_reference = ? AND p.status = 'completed'
    ");
    
    $payment_stmt->execute([$receipt_number]);
    $payment = $payment_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        die('Receipt reference "' . htmlspecialchars($receipt_number) . '" not found or not completed.');
    }

} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Payment Receipt - <?php echo htmlspecialchars($payment['receipt_number']); ?></title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.5;
            color: #1e293b;
            background-color: #f8fafc;
            margin: 0;
            padding: 30px 15px;
        }
        .receipt-card {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .receipt-header {
            background: linear-gradient(135deg, #720922 0%, #8e1532 100%);
            color: #ffffff;
            padding: 25px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .brand-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .school-logo-img {
            max-height: 70px;
            width: auto;
            border-radius: 8px;
            background: #ffffff;
            padding: 5px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .school-info h2 {
            margin: 0 0 4px 0;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .school-info p {
            margin: 2px 0;
            font-size: 12px;
            opacity: 0.9;
        }
        .receipt-badge {
            text-align: right;
        }
        .receipt-badge .title {
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #fca5a5;
        }
        .receipt-badge .ref-no {
            font-size: 14px;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.15);
            padding: 4px 10px;
            border-radius: 6px;
            margin-top: 6px;
            display: inline-block;
        }
        .receipt-body {
            padding: 30px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .meta-item {
            font-size: 13px;
        }
        .meta-item .label {
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .meta-item .value {
            font-weight: 700;
            color: #0f172a;
            font-size: 14px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .details-table th, .details-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .details-table th {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .details-table td {
            font-size: 14px;
        }
        .amount-highlight-box {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 1.5px dashed #22c55e;
            border-radius: 8px;
            padding: 16px 20px;
            text-align: center;
            margin-bottom: 25px;
        }
        .amount-highlight-box .amount-label {
            font-size: 12px;
            font-weight: 700;
            color: #15803d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .amount-highlight-box .amount-value {
            font-size: 28px;
            font-weight: 800;
            color: #14532d;
        }
        .receipt-footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .footer-note {
            font-size: 11px;
            color: #64748b;
            max-width: 450px;
        }
        .stamp-box {
            border: 2px dashed #94a3b8;
            border-radius: 8px;
            padding: 10px 20px;
            text-align: center;
            width: 180px;
            color: #64748b;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .action-bar {
            max-width: 800px;
            margin: 20px auto 0 auto;
            display: flex;
            justify-content: center;
            gap: 12px;
        }
        .btn-action {
            padding: 10px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .btn-print {
            background-color: #720922;
            color: #ffffff;
        }
        .btn-print:hover {
            background-color: #5c071b;
        }
        .btn-close-window {
            background-color: #e2e8f0;
            color: #334155;
        }
        .btn-close-window:hover {
            background-color: #cbd5e1;
        }
        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
            }
            .receipt-card {
                box-shadow: none;
                border: 1px solid #cbd5e1;
            }
            .action-bar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="receipt-card">
        <!-- Header with Brand Logo & School Info -->
        <div class="receipt-header">
            <div class="brand-section">
                <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="School Logo" class="school-logo-img" onerror="this.src='/images/folu-logo.png';">
                <div class="school-info">
                    <h2><?php echo htmlspecialchars($schoolName); ?></h2>
                    <p><?php echo htmlspecialchars($schoolAddress); ?></p>
                    <p>Phone: <?php echo htmlspecialchars($schoolPhone); ?> | Email: <?php echo htmlspecialchars($schoolEmail); ?></p>
                    <p style="font-style: italic; font-size: 11px; opacity: 0.8; margin-top: 3px;">"<?php echo htmlspecialchars($schoolMotto); ?>"</p>
                </div>
            </div>
            <div class="receipt-badge">
                <div class="title">Official Payment Receipt</div>
                <div class="ref-no"><?php echo htmlspecialchars($payment['receipt_number']); ?></div>
            </div>
        </div>

        <div class="receipt-body">
            <!-- Student & Transaction Metadata -->
            <div class="meta-grid">
                <div class="meta-item">
                    <div class="label">Student Full Name</div>
                    <div class="value"><?php echo htmlspecialchars(trim($payment['first_name'] . ' ' . ($payment['other_names'] ?? '') . ' ' . $payment['last_name'])); ?></div>
                </div>
                <div class="meta-item">
                    <div class="label">Admission Number</div>
                    <div class="value"><?php echo htmlspecialchars($payment['admission_no']); ?></div>
                </div>
                <div class="meta-item">
                    <div class="label">Class &amp; Section</div>
                    <div class="value"><?php echo htmlspecialchars(trim($payment['class_name'] . ' ' . ($payment['class_arm'] ?? ''))); ?></div>
                </div>
                <div class="meta-item">
                    <div class="label">Session / Term</div>
                    <div class="value">
                        <?php echo htmlspecialchars(($payment['session_name'] ?? 'Current Academic Session') . (!empty($payment['term_name']) ? ' - ' . $payment['term_name'] : '')); ?>
                    </div>
                </div>
                <div class="meta-item">
                    <div class="label">Payment Date</div>
                    <div class="value"><?php echo date('F j, Y', strtotime($payment['payment_date'])); ?></div>
                </div>
                <div class="meta-item">
                    <div class="label">Payment Method</div>
                    <div class="value"><?php echo ucfirst(htmlspecialchars(str_replace('_', ' ', $payment['payment_method']))); ?></div>
                </div>
            </div>

            <!-- Payment Details Table -->
            <table class="datatable details-table datatable">
                <thead>
                    <tr>
                                                     <th style="width: 50px;">S/N</th>
                        <th>Description / Fee Item</th>
                        <th>Total Fee Due (₦)</th>
                        <th>Amount Paid (₦)</th>
                        <th>Remaining Balance (₦)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $sn = 1; ?>
                    <tr>
                        <td><?php echo $sn++; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($payment['type_name']); ?></strong>
                            <?php if (!empty($payment['payment_description'])): ?>
                                <br><small style="color: #64748b;"><?php echo htmlspecialchars($payment['payment_description']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>₦<?php echo number_format((float) ($payment['amount_due'] ?? $payment['transaction_amount']), 2); ?></td>
                        <td style="color: #15803d; font-weight: 700;">₦<?php echo number_format((float) $payment['transaction_amount'], 2); ?></td>
                        <td style="color: #b91c1c; font-weight: 700;">₦<?php echo number_format((float) ($payment['fee_balance'] ?? 0), 2); ?></td>
                    </tr>
                </tbody>
            </table>

            <!-- Amount Highlight Box -->
            <div class="amount-highlight-box">
                <div class="amount-label">Amount Paid in this Transaction</div>
                <div class="amount-value">₦<?php echo number_format((float) $payment['transaction_amount'], 2); ?></div>
            </div>

            <!-- Footer Section -->
            <div class="receipt-footer">
                <div class="footer-note">
                    <p style="margin: 0 0 4px 0; font-weight: 700; color: #334155;">Thank you for your payment!</p>
                    <p style="margin: 0; font-size: 10px;">This is an officially generated electronic receipt. Generated on <?php echo date('Y-m-d H:i:s'); ?>.</p>
                </div>
                <div class="stamp-box">
                    <div style="height: 30px;"></div>
                    Authorized Stamp &amp; Signature
                </div>
            </div>
        </div>
    </div>

    <!-- No-Print Action Buttons -->
    <div class="action-bar">
        <button type="button" class="btn-action btn-print" onclick="window.print()">
            Print Receipt
        </button>
        <button type="button" class="btn-action btn-close-window" onclick="window.close()">
            Close
        </button>
    </div>

</body>
</html>
