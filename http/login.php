<?php
session_start();
include_once "db.php";
include_once "nodes.inc.php";

$email = addslashes($_POST['email']);
if (!strstr($email,'@'))
    $email .= '@mail.ustc.edu.cn';
$pass = $_POST['pass'];

$info = mysql_fetch_array(mysql_query("SELECT * FROM shellinfo WHERE `email`='$email'"));
if (empty($info))
    error('Email account does not exist.');
$passes = explode('/', $info['password']);
if (sha1(sha1($pass).$passes[1]) !== $passes[0])
    error('Wrong password!');
if (!$info['isactive'])
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
