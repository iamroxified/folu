<?php
// Redirect legacy .php requests to the named route controller
$query = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
if (!headers_sent()) {
    header("Location: /admin/reset{$query}", true, 302);
    exit;
} else {
    echo "<script>window.location.replace('/admin/reset" . json_encode($query) . "');</script>";
    echo "<noscript><meta http-equiv='refresh' content='0;url=/admin/reset" . htmlspecialchars($query, ENT_QUOTES) . "'></noscript>";
    exit;
}

