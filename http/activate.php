<?php
include_once "header.php";
include_once "nodes.inc.php";
include_once "db.php";

if (!is_numeric($_GET['appid']))
    die('Invalid request');
$appid = $_GET['appid'];

$info = mysql_fetch_array(checked_mysql_query("SELECT * FROM shellinfo WHERE `id`='$appid'"));

if (empty($info))
    die('App does not exist. The link may have been expired.');
if ($info['isactive']) {
	die("<script>window.location.href='admin.php'</script>");
}
if (!isset($_GET['token']) || !isset($info['token']) || sha1($info['token']) !== sha1($_GET['token']))
    die('Incorrect token. Please copy the link to the address bar of your browser and retry.');

lock_shell_or_die($info['id']);

// auto login
$_SESSION['email'] = $info['email'];
$_SESSION['appid'] = $info['id'];
session_write_close();
?>
<div id="wrapper">
<div id="regtitle">
        	<h1>Freeshell Activated</h1>
        	<div id="progbar">
            </div>
<p>Your freeshell is starting. You can login with SSH in a minute:</p>
<p style="font:Courier New">ssh root@<?=get_shell_v6_dns_name($info['hostname'])?></p>
<p>The root password is the same as the login password you have just set.</p>
<p>Also you go to the control panel for more info:</p>
</div>
<div id="regbutton" onclick="javascript:document.location.href='admin.php'">
        	<p>Let's Rock!</p>
</div>
</div>
</body>
</html>
<?php
fastcgi_finish_request();
$status = activate_vz($info['nodeno'], $appid, $info['distribution']);
unlock_shell($info['id']);
if (!$status) {
    send_manage_notify_email(false, $info['email'], $appid, "ACTIVATE", "If you have tried several times and always fail, maybe the installation is corrupted, please contact us and/or register another freeshell, thanks.");
}
?>
