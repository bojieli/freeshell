<?php
include_once "header.php";
include_once "nodes.inc.php";
include_once "db.php";

if (!is_numeric($_GET['appid']))
    die('Invalid request');
$appid = $_GET['appid'];

$info = mysql_fetch_array(mysql_query("SELECT * FROM shellinfo WHERE `id`='$appid'"));

if (empty($info))
    die('App does not exist. The link may have been expired.');
if ($info['isactive']) {
	echo "<script>window.location.href='admin.php'</script>";
}
if ($info['token'] !== $_GET['token'])
    die('Incorrect token. Please copy the link to the address bar of your browser and retry.');

mysql_query("UPDATE shellinfo SET `isactive`=1 WHERE `id`='$appid'");

// auto login
$_SESSION['email'] = $info['email'];
$_SESSION['appid'] = $info['id'];
?>
<div id="wrapper">
<div id="regtitle">
        	<h1>Congratulations!</h1>
        	<div id="progbar">
            </div>
<p>All things done.</p>
<p>You can login to your shell with SSH now:</p>
<p style="font:Courier New">ssh -p <?php echo $appid + 10000 ?> root@<?php echo get_node_ip(1); ?></p>
<p>The root password is the same as the login password of control panel.</p>
<p>It is recommended that you create your own account and login with it instead of root.</p>
<p>Also you can login to the control panel for more info:</p>
</div>
<div id="regbutton" onclick="javascript:document.location.href='admin.php'">
        	<p>Let's Rock!</p>
</div>
</div>
<?php
fastcgi_finish_request();
activate_vz($info['nodeno'], $appid);
?>
