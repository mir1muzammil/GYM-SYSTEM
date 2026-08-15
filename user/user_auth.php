<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}
$timeout = 3600;
if (isset($_SESSION['user_last_activity']) && (time() - $_SESSION['user_last_activity'] > $timeout)) {
    session_unset(); session_destroy();
    header("Location: user_login.php?timeout=1");
    exit();
}
$_SESSION['user_last_activity'] = time();
?>
