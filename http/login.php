<?php
session_start();
include_once "db.php";
include_once "nodes.inc.php";

$email = addslashes($_POST['email']);
$pass = $_POST['pass'];

$rs = mysql_query("SELECT * FROM shellinfo WHERE `email`='$email'");
if (!$rs)
    error('Email account does not exist.');
$info = mysql_fetch_array($rs);
$passes = explode('/', $info['password']);
if (sha1(sha1($pass).$passes[1]) !== $passes[0])
    error('Wrong password!');
if (!$rs['isactive'])
    error('Your account is not activated! Please check your email.');

$_SESSION['email'] = $email;
$_SESSION['appid'] = $info['id'];

function error($msg) {
    echo "<script>";
    echo "alert('$msg');";
    echo "window.location.href='index.php';";
    echo "</script>";
    exit();
}
?>
<script>window.location.href='admin.php';</script>
