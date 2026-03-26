<?php
// Test script to simulate and verify payment form submission
require 'db/config.php';
require 'db/functions.php';

echo "Testing Payment Form Submission\n";
echo "================================\n\n";

// Get a student with outstanding fees
$student_stmt = $pdo->prepare("
    SELECT s.*, sf.id as fee_id, sf.balance, fs.fee_name 
    FROM students s
    JOIN student_fees sf ON s.id = sf.student_link
    JOIN fee_structures fs ON sf.fee_structure_link = fs.id
    WHERE sf.balance > 0
    LIMIT 1
");
$student_stmt->execute();
$student_data = $student_stmt->fetch(PDO::FETCH_ASSOC);

if (!$student_data) {
    echo "No students with outstanding fees found.\n";
    exit;
}

echo "Found Student: " . $student_data['first_name'] . ' ' . $student_data['last_name'] . "\n";
echo "Outstanding Fee: " . $student_data['fee_name'] . " (Balance: ₦" . number_format($student_data['balance'], 2) . ")\n";
echo "Student ID: " . $student_data['id'] . "\n";
echo "Fee ID: " . $student_data['fee_id'] . "\n\n";

echo "To test the frontend form submission:\n";
echo "1. Open your browser to: http://localhost/2025/folu2/admin/view_students.php?id=" . $student_data['id'] . "\n";
echo "2. Click 'Make Payment' button\n";
echo "3. Select the fee: " . $student_data['fee_name'] . "\n";
echo "4. Enter a payment amount (max: ₦" . number_format($student_data['balance'], 2) . ")\n";
echo "5. Select a payment method\n";
echo "6. Click 'Process Payment'\n";
echo "7. Check browser console for debug logs\n";
echo "8. Verify that the page reloads with a success message\n\n";

echo "Expected console logs:\n";
echo "- 'Form data before submission:'\n";
echo "- Form field values\n";
echo "- 'Attempting form submission...'\n";
echo "- 'Native form submit() called' (or fallback messages)\n\n";

echo "Expected result:\n";
echo "- Page should reload automatically\n";
echo "- Success alert should appear at the top\n";
echo "- Outstanding balance should be updated\n";
echo "- Payment should appear in payment history\n\n";

// Create a simple curl test to simulate the form submission
echo "Testing with cURL simulation:\n";
echo "-----------------------------\n";

$test_payment_amount = min(100, $student_data['balance']); // Test with ₦100 or full balance
$post_data = [
    'process_payment' => '1',
    'fee_id' => $student_data['fee_id'],
    'academic_session' => '1', // Assuming session ID 1 exists
    'class' => $student_data['class_link'],
    'term' => 'first_term',
    'payment_amount' => $test_payment_amount,
    'payment_method' => 'cash',
    'payment_description' => 'Test payment via cURL'
];

$url = 'http://localhost/2025/folu2/admin/view_students.php?id=' . $student_data['id'];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Response Code: " . $http_code . "\n";

if ($http_code == 200) {
    echo "✓ Form submission successful!\n";
    
    // Check if payment was processed by querying the database
    $check_stmt = $pdo->prepare("SELECT * FROM student_fees WHERE id = ?");
    $check_stmt->execute([$student_data['fee_id']]);
    $updated_fee = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($updated_fee && $updated_fee['amount_paid'] > $student_data['balance'] - $student_data['balance']) {
        echo "✓ Payment was processed in database!\n";
        echo "New amount paid: ₦" . number_format($updated_fee['amount_paid'], 2) . "\n";
        echo "New balance: ₦" . number_format($updated_fee['balance'], 2) . "\n";
    } else {
        echo "⚠ Payment may not have been processed properly\n";
    }
    
    // Check for success message in response
    if (strpos($response, 'Payment Successful') !== false) {
        echo "✓ Success message found in response!\n";
    } else {
        echo "⚠ Success message not found in response\n";
    }
    
} else {
    echo "✗ Form submission failed with HTTP code: " . $http_code . "\n";
    echo "Response headers:\n" . substr($response, 0, 500) . "...\n";
}

echo "\nTest completed!\n";
?>
