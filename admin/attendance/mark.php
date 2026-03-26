<?php
// Start the session
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';

function send_absence_notification($student_id, $student_name, $student_email, $parent_phone, $date) {
    // Sending SMS using Twilio
    $account_sid = 'your_twilio_sid';
    $auth_token = 'your_twilio_auth_token';
    $twilio_number = 'your_twilio_phone';

    $client = new \\Twilio\\Rest\\Client($account_sid, $auth_token);
    
    $sms_message = "Your ward $student_name was absent on $date.";

    try {
        $client->messages->create(
            $parent_phone,
            ['from' => $twilio_number, 'body' => $sms_message]
        );
    } catch (Exception $e) {
        error_log("SMS error: " . $e->getMessage());
    }

    // Sending Email using PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email';
        $mail->Password = 'your_password';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('your_email', 'School Admin');
        $mail->addAddress($student_email, $student_name);

        $mail->isHTML(true);
        $mail->Subject = 'Attendance Notification';
        $mail->Body = "Dear Parent, your ward $student_name was absent on $date.";

        $mail->send();
    } catch (Exception $e) {
        error_log("Mail error: " . $mail->ErrorInfo);
    }
}

// Include database configuration and utility functions
require '../../db/config.php';
require '../../db/functions.php';

// Check if user is logged in
if (!isset($_SESSION['adid'])) {
    header('location:../../login.php');
    exit;
}

// Handle bulk attendance submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_mark'])) {
    $date = filter_input(INPUT_POST, 'bulk_date', FILTER_SANITIZE_STRING);
    $class_id = filter_input(INPUT_POST, 'class_id', FILTER_SANITIZE_NUMBER_INT);
    
    if (isset($_POST['student_status']) && is_array($_POST['student_status'])) {
        foreach ($_POST['student_status'] as $student_id => $status) {
            if (!empty($status)) {
                try {
                    $query = "REPLACE INTO attendance (student_id, class_id, date, status) VALUES (?, ?, ?, ?)";
                    QueryDB($query, [$student_id, $class_id, $date, $status]);
                } catch (Exception $e) {
                    echo "Error marking attendance for student ID $student_id: " . $e->getMessage();
                }
            }
        }
        echo "<div class='alert alert-success'>Bulk attendance marked successfully!</div>";
    }
}

// Handle form submission for marking attendance
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = filter_input(INPUT_POST, 'student_id', FILTER_SANITIZE_NUMBER_INT);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

    if ($student_id && $date && $status) {
        try {
            $query = "INSERT INTO attendance (student_id, class_id, date, status) VALUES (?, ?, ?, ?)";
            QueryDB($query, [$student_id, $_SESSION['class_id'], $date, $status]);
            echo "Attendance marked successfully.";
        } catch (Exception $e) {
            echo "Failed to mark attendance: " . $e->getMessage();
        }
    } else {
        echo "Invalid input.";
    }
}

// Fetch students from database
$students = QueryDB("SELECT id, first_name, last_name FROM students WHERE class_id = ?", [$_SESSION['class_id']])->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Mark Attendance</title>
    <?php include('../../nav/links.php'); ?>
</head>
<body>
    <div class="wrapper">
        <?php include('../../nav/sidebar.php'); ?>

        <div class="main-panel">
            <?php include('../../nav/header.php'); ?>
            <div class="container">
                <div class="page-inner">
                    <form method="POST">
                        <div class="form-group">
                            <label for="student">Student</label>
                            <select name="student_id" id="student" required>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= $student['id'] ?>">
                                        <?= $student['first_name'] . ' ' . $student['last_name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="date">Date</label>
                            <input type="date" name="date" id="date" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" required>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                                <option value="excused">Excused</option>
                            </select>
                        </div>
                        <button type="submit">Mark Attendance</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
