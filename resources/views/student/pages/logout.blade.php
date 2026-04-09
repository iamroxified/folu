<?php
unset(
    $_SESSION['student_user_id'],
    $_SESSION['student_id'],
    $_SESSION['student_username'],
    $_SESSION['student_name']
);

header('Location: /student/login.php');
exit;
?>
