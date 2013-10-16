<?php
include_once "header.php";
include_once "nodes.inc.php";
include_once "admin.inc.php";
include_once "db.php";

if (!is_numeric($_GET['id']))
    die('Invalid request');
$ticket_id = $_GET['id'];

$info = mysql_fetch_array(mysql_query("SELECT tickets.shellid, shellinfo.email, shellinfo.nodeno, shellinfo.hostname, tickets.create_time, tickets.used_time, tickets.token, tickets.action FROM tickets, shellinfo WHERE tickets.id='$ticket_id' AND tickets.shellid=shellinfo.id"));

if (empty($info) || $info['shellid'] == 0)
    die('Ticket does not exist.');
//if (strtotime($info['used_time']) > 0)
//    die('This ticket has been used.');
if (time() - strtotime($info['create_time']) > 48*3600)
    die('Sorry, this link has been expired. Please re-perform the action.');
if ($info['token'] !== $_GET['token'])
    die('Invalid link. Please copy the link to the address bar of your browser and retry.');
if ($info['action'] !== 'reinstall-freeshell.php')
    die('This token is not intended for reinstalling freeshell. Please login to Control Panel and try again.');

mysql_query("UPDATE tickets SET used_time=NOW() WHERE id='$ticket_id'");
?>
<div id="wrapper">
<div id="regtitle">
        	<h1>Reinstalling</h1>
        	<div id="progbar">
            </div>
<p>Your freeshell is reinstalling.</p>
<p>We will send you an email containing the new root password.</p>
</div>
<div id="regbutton" onclick="javascript:document.location.href='http://email.ustc.edu.cn'">
        	<p>Check Email</p>
</div>
</div>
<?php
fastcgi_finish_request();
destroy_vz($info['nodeno'], $info['shellid']);
$password = random_string(12);
create_vz($info['nodeno'], $info['shellid'], $info['hostname'], $password);
reactivate_vz($info['nodeno'], $info['shellid']);
send_reinstall_success_email($info['email'], $info['shellid'], $info['hostname'], $password);
