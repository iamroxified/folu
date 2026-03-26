<?php
require 'db/config.php';

$stmt = $pdo->query('SELECT id, first_name, last_name, admission_no FROM students LIMIT 5');
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Available students for testing:\n";
foreach ($students as $student) {
    echo 'ID: ' . $student['id'] . ' - ' . $student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['admission_no'] . ")\n";
}

// Also check outstanding fees for first student
if (!empty($students)) {
    $studentId = $students[0]['id'];
    $outstanding_stmt = $pdo->prepare("SELECT sf.*, fs.fee_name, fs.term, ac.session_name FROM student_fees sf JOIN fee_structures fs ON sf.fee_structure_link = fs.id LEFT JOIN academic_sessions ac ON fs.academic_session_link = ac.id WHERE sf.student_link = ? AND sf.balance > 0 ORDER BY sf.due_date ASC");
    $outstanding_stmt->execute([$studentId]);
    $outstanding_fees = $outstanding_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nOutstanding fees for student ID {$studentId}:\n";
    foreach ($outstanding_fees as $fee) {
        echo "- {$fee['fee_name']}: Balance ₦" . number_format($fee['balance'], 2) . "\n";
    }
    
    if (empty($outstanding_fees)) {
        echo "No outstanding fees found for student ID {$studentId}\n";
    }
}
?>
