<?php
session_start();
session_write_close();
include_once "../db.php";
include_once "../nodes.inc.php";
include_once "../admin.inc.php";

if (empty($_SESSION['isadmin']))
    exit();

if (is_numeric($_POST['appid']) && !empty($_POST['diskspace_softlimit']) && !empty($_POST['diskspace_hardlimit']))
{
    $info = mysql_fetch_array(checked_mysql_query("SELECT * FROM shellinfo WHERE id=".$_POST['appid']));
    if (empty($info))
        alert("Shell Not Exist");
    lock_shell_or_die($info['id']);
    checked_mysql_query("UPDATE shellinfo SET diskspace_softlimit='".addslashes($_POST['diskspace_softlimit'])."', diskspace_hardlimit='".addslashes($_POST['diskspace_hardlimit'])."' WHERE id=".$_POST['appid']);
    $status = set_vz($info['nodeno'], $info['id'], "diskspace", $_POST['diskspace_softlimit'].":".$_POST['diskspace_hardlimit']);
    unlock_shell($info['id'], $status);
    if ($status) {
        send_change_quota_email($info['email'], $info['id'], $info['diskspace_softlimit'], $_POST['diskspace_softlimit']);
        alert_noredirect("OK");
    } else {
        alert_noredirect("FAILED !!!");
    }
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
