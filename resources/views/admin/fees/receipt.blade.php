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

    // Check payment record first to support both student_fee and additional_charge polymorphic payments
    $p_check = $pdo->prepare("SELECT * FROM payments WHERE payment_reference = ?");
    $p_check->execute([$receipt_number]);
    $rawPayment = $p_check->fetch(PDO::FETCH_ASSOC);

    if (!$rawPayment && schema_has_table('receipts')) {
        $r_check = $pdo->prepare("SELECT p.* FROM receipts r JOIN payments p ON r.payment_id = p.id WHERE r.receipt_number = ? LIMIT 1");
        $r_check->execute([$receipt_number]);
        $rawPayment = $r_check->fetch(PDO::FETCH_ASSOC);
    }

    if (!$rawPayment && is_numeric($receipt_number)) {
        $pid_check = $pdo->prepare("SELECT * FROM payments WHERE id = ? LIMIT 1");
        $pid_check->execute([$receipt_number]);
        $rawPayment = $pid_check->fetch(PDO::FETCH_ASSOC);
    }

    if (!$rawPayment) {
        die('Receipt reference "' . htmlspecialchars($receipt_number) . '" not found.');
    }

    if (in_array($rawPayment['payable_type'], ['additional_charge', 'App\Models\AdditionalCharge']) || str_contains((string) $rawPayment['payable_type'], 'AdditionalCharge')) {
        $joins_ac = "JOIN additional_charges ac ON ac.id = p.payable_id\n";
        $joins_ac .= "        JOIN students s ON ac.student_id = s.id\n";
        if (schema_has_table('academic_sessions')) {
            $joins_ac .= "        LEFT JOIN academic_sessions ac_sess ON ac.session_id = ac_sess.id\n";
        }
        if (schema_has_table('academic_terms')) {
            $joins_ac .= "        LEFT JOIN academic_terms t_ac ON ac.term_id = t_ac.id\n";
        } elseif (schema_has_table('terms')) {
            $joins_ac .= "        LEFT JOIN terms t_ac ON ac.term_id = t_ac.id\n";
        }

        if ($hasSchoolClassesTable) {
            $scOn = [];
            if (schema_has_column('students', 'current_class_id')) $scOn[] = "s.current_class_id = sc.id";
            if (schema_has_column('students', 'class_link')) $scOn[] = "s.class_link = sc.id";
            if (!empty($scOn)) {
                $joins_ac .= "        LEFT JOIN school_classes sc ON (" . implode(" OR ", $scOn) . ")\n";
            }
        }

        if ($hasClassTable) {
            $cOn = [];
            if (schema_has_column('students', 'current_class_id')) $cOn[] = "s.current_class_id = c.id";
            if (schema_has_column('students', 'class_link')) $cOn[] = "s.class_link = c.id";
            if (!empty($cOn)) {
                $joins_ac .= "        LEFT JOIN classes c ON (" . implode(" OR ", $cOn) . ")\n";
            }
        }

        $termSelectAc = schema_has_table('academic_terms') || schema_has_table('terms') ? "t_ac.term_name" : "NULL AS term_name";
        $sessionSelectAc = schema_has_table('academic_sessions') ? "ac_sess.session_name" : "NULL AS session_name";

        $payment_stmt = $pdo->prepare("
            SELECT 
                p.*,
                p.payment_reference AS receipt_number,
                p.amount AS transaction_amount,
                p.description AS payment_description,
                s.id AS student_id,
                s.first_name,
                s.last_name,
                s.other_names,
                s.admission_no,
                {$classSelectName},
                {$classSelectArm},
                ac.amount AS amount_due,
                (SELECT COALESCE(SUM(p_sub.amount), 0) FROM payments p_sub WHERE (p_sub.payable_type = 'additional_charge' OR p_sub.payable_type LIKE '%AdditionalCharge%') AND p_sub.payable_id = ac.id AND p_sub.status = 'completed' AND p_sub.id <= p.id) AS total_fee_paid,
                GREATEST(0.00, ac.amount - (SELECT COALESCE(SUM(p_sub.amount), 0) FROM payments p_sub WHERE (p_sub.payable_type = 'additional_charge' OR p_sub.payable_type LIKE '%AdditionalCharge%') AND p_sub.payable_id = ac.id AND p_sub.status = 'completed' AND p_sub.id <= p.id)) AS fee_balance,
                ac.status AS fee_status,
                ac.reason AS fee_description,
                CONCAT(ac.fee_name, ' (Extra Fee)') AS type_name,
                {$sessionSelectAc},
                {$termSelectAc}
            FROM payments p
            {$joins_ac}
            WHERE p.id = ?
        ");
        $payment_stmt->execute([$rawPayment['id']]);
        $payment = $payment_stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $joins = "JOIN student_fees sf ON (p.payable_type = 'student_fee' OR p.payable_type LIKE '%StudentFee%') AND p.payable_id = sf.id\n";
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
                s.id AS student_id,
                s.first_name,
                s.last_name,
                s.other_names,
                s.admission_no,
                {$classSelectName},
                {$classSelectArm},
                sf.base_amount AS amount_due,
                (SELECT COALESCE(SUM(p_sub.amount), 0) FROM payments p_sub WHERE (p_sub.payable_type = 'student_fee' OR p_sub.payable_type LIKE '%StudentFee%') AND p_sub.payable_id = sf.id AND p_sub.status = 'completed' AND p_sub.id <= p.id) AS total_fee_paid,
                GREATEST(0.00, sf.base_amount - (SELECT COALESCE(SUM(p_sub.amount), 0) FROM payments p_sub WHERE (p_sub.payable_type = 'student_fee' OR p_sub.payable_type LIKE '%StudentFee%') AND p_sub.payable_id = sf.id AND p_sub.status = 'completed' AND p_sub.id <= p.id)) AS fee_balance,
                CASE 
                    WHEN GREATEST(0.00, sf.base_amount - (SELECT COALESCE(SUM(p_sub.amount), 0) FROM payments p_sub WHERE (p_sub.payable_type = 'student_fee' OR p_sub.payable_type LIKE '%StudentFee%') AND p_sub.payable_id = sf.id AND p_sub.status = 'completed' AND p_sub.id <= p.id)) <= 0.001 THEN 'paid'
                    WHEN (SELECT COALESCE(SUM(p_sub.amount), 0) FROM payments p_sub WHERE (p_sub.payable_type = 'student_fee' OR p_sub.payable_type LIKE '%StudentFee%') AND p_sub.payable_id = sf.id AND p_sub.status = 'completed' AND p_sub.id <= p.id) > 0 THEN 'partial'
                    ELSE 'pending'
                END AS fee_status,
                fs.description AS fee_description,
                COALESCE(fs.name, fs.description, 'Tuition Fee') AS type_name,
                {$sessionSelect},
                {$termSelect}
            FROM payments p
            {$joins}
            WHERE p.id = ?
        ");
        $payment_stmt->execute([$rawPayment['id']]);
        $payment = $payment_stmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($payment && schema_has_table('receipts')) {
        try {
            $rc_stmt = $pdo->prepare("SELECT receipt_number FROM receipts WHERE payment_id = ? ORDER BY id DESC LIMIT 1");
            $rc_stmt->execute([$rawPayment['id']]);
            $officialRec = $rc_stmt->fetchColumn();
            if ($officialRec) {
                $payment['official_receipt_number'] = $officialRec;
                $payment['receipt_number'] = $officialRec;
            }
        } catch (\Throwable $t) {}
    }

    if (!$payment) {
        die('Receipt reference "' . htmlspecialchars($receipt_number) . '" could not be formatted.');
    }

    // Calculate total remaining cumulative balance for this student across all fees & charges
    $student_id = (int) ($payment['student_id'] ?? 0);
    $totalCumulativeBalance = 0.00;
    if ($student_id > 0) {
        $cum_stmt = $pdo->prepare("
            SELECT 
                (SELECT COALESCE(SUM(balance), 0) FROM student_fees WHERE student_id = :sid1) +
                (SELECT COALESCE(SUM(balance), 0) FROM additional_charges WHERE student_id = :sid2 AND (student_fee_id IS NULL OR student_fee_id = 0))
            AS total_cum_balance
        ");
        $cum_stmt->execute(['sid1' => $student_id, 'sid2' => $student_id]);
        $totalCumulativeBalance = (float) $cum_stmt->fetchColumn();
    }

    // Completed status and payment balance flags
    $amountDue = (float) ($payment['amount_due'] ?? 0);
    $totalFeePaid = (float) ($payment['total_fee_paid'] ?? 0);
    $itemBalance = (float) ($payment['fee_balance'] ?? max(0.00, $amountDue - $totalFeePaid));
    $transactionAmount = (float) ($payment['transaction_amount'] ?? $payment['amount'] ?? 0);

    // CRITICAL: The payment completed watermark must ONLY appear on a receipt where the amount owed and amount paid are equal
    $isEqualPaid = ($amountDue > 0.001) 
        ? ($itemBalance <= 0.001 || abs($amountDue - $totalFeePaid) <= 0.01)
        : ($transactionAmount > 0.001 && $itemBalance <= 0.001);

    $isCompleted = $isEqualPaid;
    $isFullyPaid = $itemBalance <= 0.001;

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
            position: relative;
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
        .balance-summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 25px;
        }
        .balance-box {
            border-radius: 8px;
            padding: 16px 20px;
            text-align: center;
        }
        .balance-box.current-txn-box {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 1.5px dashed #22c55e;
        }
        .balance-box.current-txn-box .balance-label {
            font-size: 11px;
            font-weight: 700;
            color: #15803d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .balance-box.current-txn-box .balance-value {
            font-size: 26px;
            font-weight: 800;
            color: #14532d;
        }
        .balance-box.cumulative-box {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1.5px dashed #ef4444;
        }
        .balance-box.cumulative-box .balance-label {
            font-size: 11px;
            font-weight: 700;
            color: #b91c1c;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .balance-box.cumulative-box .balance-value {
            font-size: 26px;
            font-weight: 800;
            color: #991b1b;
        }
        .balance-sub {
            font-size: 11px;
            display: block;
            margin-top: 4px;
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
        /* ==================== COMPLETED STAMP & WATERMARK STYLES ==================== */
        .watermark-container {
            position: absolute;
            top: 52%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-24deg);
            pointer-events: none;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            user-select: none;
            opacity: 0.12;
            width: 100%;
        }
        .watermark-box {
            border: 6px dashed #15803d;
            border-radius: 20px;
            padding: 16px 48px;
            text-align: center;
            background: rgba(240, 253, 244, 0.4);
        }
        .watermark-main-text {
            font-size: 68px;
            font-weight: 900;
            font-family: 'Arial Black', Impact, 'Segoe UI Black', sans-serif;
            color: #15803d;
            letter-spacing: 8px;
            line-height: 1;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .watermark-sub-text {
            font-size: 14px;
            font-weight: 800;
            color: #166534;
            letter-spacing: 6px;
            text-transform: uppercase;
            margin-top: 8px;
            border-top: 2px solid #166534;
            padding-top: 4px;
            white-space: nowrap;
        }

        .completed-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background-color: #22c55e;
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 4px 12px;
            border-radius: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .stamp-banner-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
            position: relative;
            z-index: 3;
        }
        .official-stamp-badge {
            display: inline-block;
            padding: 8px 20px;
            border: 3.5px double #15803d;
            border-radius: 10px;
            background: rgba(240, 253, 244, 0.95);
            box-shadow: 0 0 0 2px #bbf7d0, 0 4px 14px rgba(21, 128, 61, 0.15);
            transform: rotate(-5deg);
            text-align: center;
            user-select: none;
        }
        .stamp-badge-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .stamp-school-name {
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: #166534;
            text-transform: uppercase;
        }
        .stamp-stars {
            font-size: 8px;
            letter-spacing: 3px;
            color: #15803d;
            margin: 1px 0;
        }
        .stamp-status-word {
            font-size: 20px;
            font-weight: 900;
            letter-spacing: 2.5px;
            color: #15803d;
            border-top: 2px solid #15803d;
            border-bottom: 2px solid #15803d;
            padding: 3px 14px;
            margin: 2px 0;
            text-transform: uppercase;
            font-family: 'Arial Black', Impact, sans-serif;
            line-height: 1.1;
        }
        .stamp-date-line {
            font-size: 9.5px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #166534;
            margin-top: 2px;
        }
        .stamp-sub-text {
            font-size: 7.5px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #15803d;
            opacity: 0.9;
            text-transform: uppercase;
        }

        .stamp-box.stamp-box-completed {
            border: 2px solid #15803d !important;
            background: #f0fdf4 !important;
            color: #15803d !important;
            padding: 8px 12px !important;
            box-shadow: 0 0 0 2px #dcfce7 !important;
            transform: rotate(-3deg);
        }
        .seal-ring {
            border: 1.5px dashed #16a34a;
            border-radius: 6px;
            padding: 6px 4px;
        }
        .seal-icon {
            font-size: 16px;
            font-weight: 900;
            color: #15803d;
            line-height: 1;
        }
        .seal-title {
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 1px;
            color: #15803d;
            margin-top: 2px;
        }
        .seal-org {
            font-size: 8px;
            font-weight: 700;
            color: #166534;
            letter-spacing: 0.5px;
        }
        .seal-date {
            font-size: 8px;
            font-weight: 700;
            color: #15803d;
            margin-top: 1px;
        }
        .seal-caption {
            font-size: 8px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #15803d;
            margin-top: 4px;
            text-transform: uppercase;
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            body {
                background-color: #ffffff;
                padding: 0;
            }
            .receipt-card {
                box-shadow: none;
                border: 1px solid #cbd5e1;
            }
            .watermark-container {
                opacity: 0.14 !important;
            }
            .official-stamp-badge {
                border-color: #15803d !important;
                background-color: #f0fdf4 !important;
            }
            .stamp-box-completed {
                border-color: #15803d !important;
                background-color: #f0fdf4 !important;
            }
            .action-bar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="receipt-card">
        <?php if ($isCompleted): ?>
            <div class="watermark-container" aria-hidden="true">
                <div class="watermark-box">
                    <div class="watermark-main-text"><?php echo ($isFullyPaid || $totalCumulativeBalance <= 0.001) ? 'PAID IN FULL' : 'PAYMENT COMPLETED'; ?></div>
                    <div class="watermark-sub-text">OFFICIAL RECEIPT • VALIDATED</div>
                </div>
            </div>
        <?php endif; ?>

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
                <?php if ($isCompleted): ?>
                    <div style="margin-top: 6px;">
                        <span class="completed-badge-pill">
                            <span style="font-size: 13px; line-height: 1;">✓</span> PAYMENT COMPLETED
                        </span>
                    </div>
                <?php else: ?>
                    <div style="margin-top: 6px;">
                        <span class="completed-badge-pill" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">
                            <span style="font-size: 13px; line-height: 1;">⏳</span> PARTIAL PAYMENT
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="receipt-body" style="position: relative; z-index: 2;">
            <?php if ($isCompleted): ?>
                <div class="stamp-banner-row">
                    <div class="official-stamp-badge">
                        <div class="stamp-badge-inner">
                            <div class="stamp-school-name"><?php echo htmlspecialchars($schoolName); ?></div>
                            <div class="stamp-stars">★ ★ ★ ★ ★</div>
                            <div class="stamp-status-word"><?php echo ($isFullyPaid ? 'PAID IN FULL' : 'PAYMENT COMPLETED'); ?></div>
                            <div class="stamp-date-line">OFFICIALLY VERIFIED • <?php echo date('d M Y', strtotime($payment['payment_date'] ?? 'now')); ?></div>
                            <div class="stamp-sub-text">BURSARY &amp; ACCOUNTS DEPARTMENT</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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
                        <th>Item Total Due (₦)</th>
                        <th>Amount Paid (₦)</th>
                        <th>Remaining Item Balance (₦)</th>
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

            <!-- Payment Transaction & Total Remaining Cumulative Balance -->
            <div class="balance-summary-grid">
                <div class="balance-box current-txn-box">
                    <div class="balance-label">Amount Paid in this Transaction</div>
                    <div class="balance-value">₦<?php echo number_format((float) $payment['transaction_amount'], 2); ?></div>
                    <small class="balance-sub" style="color: #166534;">
                        Item: <?php echo htmlspecialchars($payment['type_name']); ?> | Method: <?php echo ucfirst(htmlspecialchars(str_replace('_', ' ', $payment['payment_method']))); ?>
                    </small>
                </div>
                <div class="balance-box cumulative-box">
                    <div class="balance-label">Total Student Cumulative Balance</div>
                    <div class="balance-value">₦<?php echo number_format($totalCumulativeBalance, 2); ?></div>
                    <small class="balance-sub" style="color: #991b1b;">
                        <?php echo ($totalCumulativeBalance <= 0.001) ? '✓ All fees & charges cleared in full' : 'Total balance owed across all fees & extra charges'; ?>
                    </small>
                </div>
            </div>

            <!-- Footer Section -->
            <div class="receipt-footer">
                <div class="footer-note">
                    <p style="margin: 0 0 4px 0; font-weight: 700; color: #334155;">Thank you for your payment!</p>
                    <p style="margin: 0; font-size: 10px;">This is an officially generated electronic receipt. Generated on <?php echo date('Y-m-d H:i:s'); ?>.</p>
                </div>
                <div class="stamp-box <?php echo $isCompleted ? 'stamp-box-completed' : ''; ?>">
                    <?php if ($isCompleted): ?>
                        <div class="seal-ring">
                            <div class="seal-icon">✓</div>
                            <div class="seal-title">PAYMENT COMPLETED</div>
                            <div class="seal-org"><?php echo htmlspecialchars($schoolName); ?></div>
                            <div class="seal-date"><?php echo date('d-M-Y', strtotime($payment['payment_date'] ?? 'now')); ?></div>
                        </div>
                        <div class="seal-caption">BURSARY VALIDATED</div>
                    <?php else: ?>
                        <div style="height: 30px;"></div>
                        Authorized Stamp &amp; Signature
                    <?php endif; ?>
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
