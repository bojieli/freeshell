<?php
session_start();
include_once "../db.php";
include_once "../nodes.inc.php";

function alert_and_jump($msg) {
    echo "<script>alert('$msg');window.location.href='../admin.php';</script>";
    exit();
}

if (empty($_SESSION['isadmin']))
    exit();

if (is_numeric($_POST['appid']) && isset($_POST['diskspace_softlimit']) && isset($_POST['diskspace_hardlimit']))
{
    $info = mysql_fetch_array(mysql_query("SELECT * FROM shellinfo WHERE id=".$_POST['appid']));
    if (empty($info))
        alert_and_jump("Shell Not Exist");
    mysql_query("UPDATE shellinfo SET diskspace_softlimit='".addslashes($_POST['diskspace_softlimit'])."', diskspace_hardlimit='".addslashes($_POST['diskspace_hardlimit'])."' WHERE id=".$_POST['appid']);
    set_vz($info['nodeno'], $_POST['appid'], "diskspace", $_POST['diskspace_softlimit'].":".$_POST['diskspace_hardlimit']);
    alert_and_jump("OK");
}
?>
<h1>Change Disk Quota</h1>
<form action="change-quota.php" method="post">
<table>
<tr><td>Shell ID</td><td><input name="appid" /></td></tr>
<tr><td>Diskspace Softlimit</td><td><input name="diskspace_softlimit" /> (default 5G)</td></tr>
<tr><td>Diskspace Hardlimit</td><td><input name="diskspace_hardlimit" /> (default 7G)</td></tr>
<tr><td></td><td><button type="submit">Submit</button></td></tr>
</table>
</form>
