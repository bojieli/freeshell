<?php
include_once "header.php";
session_write_close();
include_once "nodes.inc.php";
include_once "admin.inc.php";
include_once "proxy.inc.php";
include_once "db.php";

if (isset($_SESSION['isadmin']) && $_SESSION['isadmin'] && isset($_GET['shellid'])) {
    $shell_id = intval($_GET['shellid']);
    $info = mysql_fetch_array(checked_mysql_query("SELECT *, id AS shellid FROM shellinfo WHERE id='$shell_id'"));
    if (empty($info))
        die('no such freeshell');
    $ticket_id = -1;
    report_sys_admin("Administrator ".$_SESSION['email']." destroyed freeshell #".$shell_id);
    goto do_destroy;
}

if (!is_numeric($_GET['id']))
    die('Invalid request');
$ticket_id = $_GET['id'];

$info = mysql_fetch_array(checked_mysql_query("SELECT tickets.shellid, shellinfo.email, shellinfo.nodeno, shellinfo.hostname, shellinfo.diskspace_softlimit, shellinfo.diskspace_hardlimit, tickets.create_time, tickets.used_time, tickets.token, tickets.action FROM tickets, shellinfo WHERE tickets.id='$ticket_id' AND tickets.shellid=shellinfo.id"));

if (empty($info) || $info['shellid'] == 0)
    die('Ticket does not exist.');
if (strtotime($info['used_time']) > 0)
    die('This ticket has been used.');
if (time() - strtotime($info['create_time']) > 48*3600)
    die('Sorry, this link has been expired. Please re-perform the action.');
if (!isset($_GET['token']) || !isset($info['token']) || sha1($info['token']) !== sha1($_GET['token']))
    die('Invalid link. Please copy the link to the address bar of your browser and retry.');
if ($info['action'] !== 'destroy-freeshell.php')
    die('This token is not intended for destroying freeshell. Please login to Control Panel and try again.');

do_destroy:
lock_shell_or_die($info['shellid']);
checked_mysql_query("UPDATE tickets SET used_time=NOW() WHERE id='$ticket_id'");
?>
<div id="wrapper">
<div id="regtitle">
        	<h1>Destroy Confirmed</h1>
        	<div id="progbar">
            </div>
<p>Your freeshell #<?=$info['shellid'] ?> is being deleted.</p>
</div>
</div>
<?php
fastcgi_finish_request();
function do_destroy($info) {
    if (!delete_dns($info['hostname']))
        return false;
    if (!update_proxy_conf())
        return false;
    if (!destroy_vz($info['nodeno'], $info['shellid']))
        return false;
    if (!remove_all_endpoints($info['nodeno'], $info['shellid']))
        return false;
    return true;
}
$status = do_destroy($info);
unlock_shell($info['shellid']);
if ($status) {
    delete_freeshell_in_db($info['shellid']);
} else {
    send_manage_notify_email(false, $info['email'], $info['shellid'], 'DESTROY');
}
