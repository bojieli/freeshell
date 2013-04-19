<?php
include_once "header.php";
include_once "db.php";
include_once "nodes.inc.php";
include_once "admin.inc.php";

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

$_SESSION['email'] = $email;
$_SESSION['appid'] = $info['id'];

if ($info['isactive']) {
	echo "<script>window.location.href='admin.php';</script>";
	exit();
}

// not activated
?>
<div id="wrapper">
<div id="regtitle">
        	<h1>It's almost there!</h1>
        	<div id="progbar">
            </div>
<p>Please confirm your identify by clicking the link in <?=$email?>.</p>
<p>If you have not received confirmation mail, click below to resend:</p>
</div>
<div id="regbutton" onclick="javascript:window.location.href='resend_confirm_mail.php'">
        	<p>Resend Mail</p>
</div>
</div>
