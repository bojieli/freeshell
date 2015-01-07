<?php
include_once "header.php";
include_once "db.php";
include_once "verify.inc.php";
include_once "admin.inc.php";

$password = $_POST['regpassword'];
if ($password !== $_POST['regconfpass'])
    alert('Passwords mismatch.');
$email = $_POST['regemail'];
$hostname = $_POST['hostname'];
$distribution = $_POST['distribution'];
$gallery_id = isset($_POST['gallery-id']) ? $_POST['gallery-id'] : null;
$nodeno = $_POST['nodeno'];

if (!isset($_POST['agree-policy'])) {
    alert('You must agree freeshell policy.');
}
if (checkhost($hostname) || strlen($password)<6 || checkemail($email)) {
    alert('Sorry, sanity check failed.');
}

$token = random_string(40);
$time = time();
$reginfo = compact('password', 'email', 'hostname', 'distribution', 'gallery_id', 'nodeno', 'token', 'time');
checked_mysql_query("INSERT INTO pending_register SET reginfo='".mysql_real_escape_string(serialize($reginfo))."'");
$regid = mysql_insert_id();
if ($regid)
    send_activate_mail($email, $regid, $token);
else
    alert('Service Unavailable: SQL error');
?>
<div id="wrapper">
<div id="regtitle">
        	<h1>It's almost there!</h1>
        	<div id="progbar">
            </div>
<p>We have sent you: <?=$email?> an confirmation mail.</p>
<p>Please activate your account by clicking the link inside the mail.</p>
</div>
<div id="regbutton" onclick="javascript:document.location.href='http://email.ustc.edu.cn'">
        	<p>Check now</p>
</div>
</div>
