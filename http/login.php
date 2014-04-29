<?php
include_once "header.php";
include_once "db.php";
include_once "nodes.inc.php";
include_once "admin.inc.php";

if (!isset($_SESSION['appid'])) {

    if (isset($_SESSION['email'])) {
    	die("<script>window.location.href='select-shell.php';</script>");
    }

    $email = addslashes($_POST['email']);

    if (!strstr($email,'@'))
        $email .= '@mail.ustc.edu.cn';
    $pass = $_POST['pass'];
    
    $rs = checked_mysql_query("SELECT * FROM shellinfo WHERE `email`='$email'");
    $info = mysql_fetch_array($rs);
    $verified = array();
    do {
        if (empty($info))
            error('Email account does not exist.');
        if (check_password($pass, $info['password']))
            $verified[] = $info;
    } while ($info = mysql_fetch_array($rs));

    if (empty($verified))
        error('Wrong password!');
    if (count($verified) >= 2) {
        $_SESSION['email'] = $email;
    	die("<script>window.location.href='select-shell.php';</script>");
    } else {
        // only one shell is verified
        $info = $verified[0];
        
        $_SESSION['email'] = $email;
        $_SESSION['appid'] = $info['id'];
        $_SESSION['isadmin'] = $info['isadmin'];
    }
}
else { // appid has been set
    $info = mysql_fetch_array(checked_mysql_query("SELECT * FROM shellinfo WHERE `id`='".$_SESSION['appid']."'"));
    if (empty($info))
        die("<script>window.location.href='logout.php';</script>");
    $_SESSION['isadmin'] = $info['isadmin'];
}
    
if ($info['isactive']) {
	die("<script>window.location.href='admin.php';</script>");
}

// not activated
?>
<div id="wrapper">
<div id="regtitle">
        	<h1>It's almost there!</h1>
        	<div id="progbar">
            </div>
<p>Please confirm your identity by clicking the link in <?=$_SESSION['email']?>.</p>
<p>If you have not received confirmation mail, click below to resend:</p>
</div>
<div id="regbutton" onclick="javascript:window.location.href='resend_confirm_mail.php'">
        	<p>Resend Mail</p>
</div>
</div>
