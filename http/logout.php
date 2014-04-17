<?php
if (!function_exists('session_status') ||
    // requires PHP 5.4.0+
    session_status() == PHP_SESSION_NONE) {
    session_start();
}
unset($_SESSION['email']);
unset($_SESSION['appid']);
unset($_SESSION['isadmin']);
?>
<script>window.location.href='index.php';</script>
<?php
exit(); // other scripts may include this page to log out
?>
