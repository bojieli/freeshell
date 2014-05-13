<?php
include_once "header.php";
include_once "db.php";
include_once "nodes.inc.php";
include_once "admin.inc.php";

if (!isset($_SESSION['appid_to_active']) || !is_numeric($_SESSION['appid_to_active']))
    include "logout.php";
$rs = checked_mysql_query("SELECT token FROM shellinfo WHERE `id`='". $_SESSION['appid_to_active']. "'");
$token = mysql_result($rs,0);
if ($token == "")
    include "logout.php";
session_write_close();

send_activate_mail($_SESSION['email_to_active'], $_SESSION['appid_to_active'], $token);
?>
<div id="wrapper">
<div id="regtitle">
        	<h1>Confirmation Mail Resent</h1>
        	<div id="progbar">
            </div>
<p>We have resent you (<?=$_SESSION['email_to_active']?>) an confirmation mail.</p>
<p>Please activate your account by clicking the link inside the mail.</p>
</div>
<div id="regbutton" onclick="javascript:document.location.href='http://email.ustc.edu.cn'">
        	<p>Check now</p>
</div>
</div>
