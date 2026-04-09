<?php
unset(
    $_SESSION['teacher_user_id'],
    $_SESSION['teacher_id'],
    $_SESSION['teacher_username'],
    $_SESSION['teacher_name']
);

header('Location: /teacher/login.php');
exit;
?>
