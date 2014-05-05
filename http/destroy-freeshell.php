<?php
include_once "header.php";
session_write_close();
include_once "nodes.inc.php";
include_once "admin.inc.php";
include_once "proxy.inc.php";
include_once "db.php";

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
delete_dns($info['hostname']);
update_proxy_conf();
destroy_vz($info['nodeno'], $info['shellid']);
remove_all_endpoints($info['nodeno'], $info['shellid']);
db_remove_all_endpoints($info['shellid']);
unlock_shell($info['shellid']);
checked_mysql_query("DELETE FROM shellinfo WHERE id='".$info['shellid']."'");

