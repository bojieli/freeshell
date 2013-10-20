<?php
session_start();
unset($_SESSION['email']);
unset($_SESSION['appid']);
unset($_SESSION['isadmin']);
?>
<script>window.location.href='index.php';</script>
