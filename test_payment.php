<?php
// Simulate the payment processing
session_start();
require 'db/config.php';

// Simulate session login
$_SESSION['adid'] = 1;

$studentId = 1; // Adamu Ibrahim

// Find a fee with outstanding balance
$outstanding_stmt = $pdo->prepare("SELECT id, amount_due, amount_paid, balance FROM student_fees WHERE student_link = ? AND balance > 0 LIMIT 1");
$outstanding_stmt->execute([$studentId]);
$outstanding_fee = $outstanding_stmt->fetch(PDO::FETCH_ASSOC);

if (!$outstanding_fee) {
    echo "No outstanding fees found for student ID $studentId\n";
    exit;
}

$fee_id = $outstanding_fee['id'];

// Simulate POST data
$_POST = [
    'process_payment' => '1',
    'fee_id' => $fee_id,
    'academic_session' => '1',
    'class' => '1',
    'term' => 'first_term',
    'payment_amount' => '1000.00',
    'payment_method' => 'cash',
    'payment_description' => 'Test payment via script'
];

echo "Testing payment processing...\n";
echo "Student ID: $studentId\n";
echo "Fee ID: $fee_id\n";
echo "Payment Amount: ₦" . number_format($_POST['payment_amount'], 2) . "\n";

// Get current fee info
$existing_fee_stmt = $pdo->prepare("SELECT * FROM student_fees WHERE id = ? AND student_link = ?");
$existing_fee_stmt->execute([$fee_id, $studentId]);
$existing_fee = $existing_fee_stmt->fetch(PDO::FETCH_ASSOC);

if (!$existing_fee) {
    echo "ERROR: Fee record not found!\n";
    exit;
}

echo "\nBefore payment:\n";
echo "Amount Due: ₦" . number_format($existing_fee['amount_due'], 2) . "\n";
echo "Amount Paid: ₦" . number_format($existing_fee['amount_paid'], 2) . "\n";
echo "Balance: ₦" . number_format($existing_fee['balance'], 2) . "\n";

try {
    $pdo->beginTransaction();
    
    $payment_amount = floatval($_POST['payment_amount']);
    $payment_method = $_POST['payment_method'];
    $receipt_number = 'RCP' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Validate payment amount doesn't exceed balance
    if ($payment_amount > $existing_fee['balance']) {
        throw new Exception('Payment amount cannot exceed the outstanding balance of ₦' . number_format($existing_fee['balance'], 2));
    }

    // Update existing fee record
    $new_amount_paid = $existing_fee['amount_paid'] + $payment_amount;
    $new_balance = max(0, $existing_fee['amount_due'] - $new_amount_paid);

    $status = 'partial';
    if ($new_balance == 0) {
        $status = 'paid';
    } elseif ($new_amount_paid == 0) {
        $status = 'pending';
    }

    $update_fee_stmt = $pdo->prepare("UPDATE student_fees SET amount_paid = ?, balance = ?, status = ?, payment_date = CURDATE(), payment_method = ?, receipt_number = ?, updated_at = NOW() WHERE id = ?");

    $update_fee_stmt->execute([
        $new_amount_paid,
        $new_balance,
        $status,
        $payment_method,
        $receipt_number,
        $existing_fee['id']
    ]);

    $pdo->commit();
    
    echo "\nPayment processed successfully!\n";
    echo "Receipt Number: $receipt_number\n";
    echo "\nAfter payment:\n";
    echo "Amount Paid: ₦" . number_format($new_amount_paid, 2) . "\n";
    echo "New Balance: ₦" . number_format($new_balance, 2) . "\n";
    echo "Status: $status\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: Payment processing failed: " . $e->getMessage() . "\n";
}
?>
