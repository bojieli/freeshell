<?php
include_once "header.php";
include_once "db.php";
include_once "nodes.inc.php";
include_once "admin.inc.php";

$appid = $_SESSION['appid'];
if (empty($appid))
    exit();
$rs = mysql_query("SELECT * FROM shellinfo WHERE `id`='$appid'");
$info = mysql_fetch_array($rs);
if (empty($info))
    exit();
if (empty($_POST['oldpwd']))
    goto print_table;
if ($_POST['newpwd'] !== $_POST['newpwd_repeat']) {
    echo "New Passwords Mismatch";
    goto print_table;
}
if (strlen($_POST['newpwd']) < 6) {
    echo "New Password Length should not be less than 6";
    goto print_table;
}
if (!check_password($_POST['oldpwd'], $info['password'])) {
    echo "Old Password Error";
    goto print_table;
}
change_password($appid, generate_password($_POST['newpwd']));
echo "<script>alert('Password Changed Successfully');</script>";
echo "<script>window.location.href='logout.php';</script>";

print_table:
?>
<div id="wrapper">
<div id="regtitle">
        	<h1>Change Web Password</h1>
        	<div id="progbar">
            </div>
<style>
table.form {
	font-size: 16px !important;
	font-family: "Segoe UI","Helvetica Neue", Helvetica, Ubuntu;
	margin-left: 100px !important;
	margin-top: 20px;
}
table.form td {
    padding: 3px;
}
p.note {
    width: 700px;
}
</style>
<p class="note">This action changes password to your Web Control Panel, not freeshell node.<br />If you wish to reset root password of your freeshell, goto "Manage freeshell" in Control Panel.</p>
<form action="change-web-pass.php" method="post">
<table class="form">
<tr><td>Old Password</td><td><input name="oldpwd" type="password" /></td></tr>
<tr><td>New Password</td><td><input name="newpwd" type="password" /></td></tr>
<tr><td>Repeat New Password</td><td><input name="newpwd_repeat" type="password" /></td></tr>
<tr><td></td><td><button type="submit">Submit</button></td></tr>
</table>
</form>
</div>
</div>
