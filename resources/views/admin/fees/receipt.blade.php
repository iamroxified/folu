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

// Get payment details
$receipt_number = $_GET['receipt'] ?? null;
if (!$receipt_number) {
    die('Receipt number is required');
}

try {
    // Fetch payment details
    $payment_stmt = $pdo->prepare("
        SELECT 
            sp.*,
            s.first_name,
            s.last_name,
            s.admission_no,
            c.class_name,
            f.fee_description,
            ft.fee_name as type_name,
            ac.session_name,
            CASE sf.term
                WHEN 'first' THEN 'First Term'
                WHEN 'second' THEN 'Second Term'
                WHEN 'third' THEN 'Third Term'
                WHEN 'annual' THEN 'Full Session'
                ELSE 'N/A'
            END as term_name
        FROM student_payments sp
        JOIN students s ON sp.student_link = s.id
        JOIN student_fees sf ON sp.student_fee_id = sf.id
        JOIN fees f ON sf.fee_structure_link = f.id
        LEFT JOIN fee_type ft ON f.fee_name = ft.id
        LEFT JOIN classes c ON s.class_link = c.id
        LEFT JOIN academic_sessions ac ON sp.academic_session_link = ac.id
        WHERE sp.receipt_number = ?
    ");
    
    $payment_stmt->execute([$receipt_number]);
    $payment = $payment_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        die('Receipt not found');
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
    <title>Payment Receipt - <?php echo $payment['receipt_number']; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .receipt {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        .receipt-header h1 {
            margin: 0;
            color: #333;
        }
        .receipt-header p {
            margin: 5px 0;
            color: #666;
        }
        .receipt-body {
            margin-bottom: 20px;
        }
        .receipt-body table {
            width: 100%;
            margin-bottom: 20px;
        }
        .receipt-body table td {
            padding: 5px 0;
        }
        .receipt-body table td:first-child {
            font-weight: bold;
            width: 200px;
        }
        .receipt-footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            text-align: center;
            padding: 20px;
            background: #f9f9f9;
            margin: 20px 0;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="receipt-header">
            <h1>PAYMENT RECEIPT</h1>
            <p>School Name Here</p>
            <p>School Address Here</p>
            <p>Phone: School Phone | Email: School Email</p>
        </div>

        <div class="receipt-body">
            <table>
                <tr>
                    <td>Receipt Number:</td>
                    <td><?php echo htmlspecialchars($payment['receipt_number']); ?></td>
                </tr>
                <tr>
                    <td>Date:</td>
                    <td><?php echo date('F j, Y', strtotime($payment['payment_date'])); ?></td>
                </tr>
                <tr>
                    <td>Student Name:</td>
                    <td><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                </tr>
                <tr>
                    <td>Admission Number:</td>
                    <td><?php echo htmlspecialchars($payment['admission_no']); ?></td>
                </tr>
                <tr>
                    <td>Class:</td>
                    <td><?php echo htmlspecialchars($payment['class_name']); ?></td>
                </tr>
                <tr>
                    <td>Session/Term:</td>
                    <td><?php echo htmlspecialchars($payment['session_name'] . ' - ' . $payment['term_name']); ?></td>
                </tr>
                <tr>
                    <td>Payment For:</td>
                    <td><?php echo htmlspecialchars($payment['type_name']); ?></td>
                </tr>
                <tr>
                    <td>Payment Method:</td>
                    <td><?php echo ucfirst(htmlspecialchars($payment['payment_method'])); ?></td>
                </tr>
                <?php if ($payment['transaction_reference']): ?>
                <tr>
                    <td>Transaction Reference:</td>
                    <td><?php echo htmlspecialchars($payment['transaction_reference']); ?></td>
                </tr>
                <?php endif; ?>
            </table>

            <div class="amount">
                Amount Paid: ₦<?php echo number_format($payment['amount_paid'], 2); ?>
            </div>

            <?php if ($payment['payment_description']): ?>
            <p><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($payment['payment_description'])); ?></p>
            <?php endif; ?>
        </div>

        <div class="receipt-footer">
            <p>Thank you for your payment!</p>
            <p><small>This is a computer-generated receipt and does not require a signature.</small></p>
            <p><small>Generated on: <?php echo date('Y-m-d H:i:s'); ?></small></p>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">Print Receipt</button>
        <button onclick="window.close()">Close</button>
    </div>
</body>
</html>



