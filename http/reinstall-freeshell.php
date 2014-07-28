<?php
include_once "header.php";
session_write_close();
include_once "nodes.inc.php";
include_once "admin.inc.php";
include_once "db.php";
include_once "verify.inc.php";

if (isset($_SESSION['isadmin']) && $_SESSION['isadmin'] && isset($_GET['shellid'])) {
    $shell_id = intval($_GET['shellid']);
    $info = mysql_fetch_array(checked_mysql_query("SELECT *, id AS shellid FROM shellinfo WHERE id='$shell_id'"));
    if (empty($info))
        die('no such freeshell');
    $ticket_id = -1;
    $distribution = $info['distribution'];
    $keep_dirs = '/root,/home';
    report_sys_admin("Administrator ".$_SESSION['email']." reinstalled freeshell #".$shell_id." to distribution $distribution");
    goto do_reinstall;
}

if (!is_numeric($_GET['id']))
    die('Invalid request');
$ticket_id = $_GET['id'];

$info = mysql_fetch_array(checked_mysql_query("SELECT tickets.shellid, shellinfo.email, shellinfo.nodeno, shellinfo.hostname, shellinfo.diskspace_softlimit, shellinfo.diskspace_hardlimit, shellinfo.distribution, tickets.create_time, tickets.used_time, tickets.token, tickets.action, tickets.param FROM tickets, shellinfo WHERE tickets.id='$ticket_id' AND tickets.shellid=shellinfo.id"));

if (empty($info) || $info['shellid'] == 0)
    die('Ticket does not exist.');
if (strtotime($info['used_time']) > 0)
    die('This ticket has been used.');
if (time() - strtotime($info['create_time']) > 48*3600)
    die('Sorry, this link has been expired. Please re-perform the action.');
if (!isset($_GET['token']) || !isset($info['token']) || sha1($info['token']) !== sha1($_GET['token']))
    die('Invalid link. Please copy the link to the address bar of your browser and retry.');
if ($info['action'] !== 'reinstall-freeshell.php')
    die('This token is not intended for reinstalling freeshell. Please login to Control Panel and try again.');
if (empty($info['param']))
    die('Invalid param!');
list($distribution, $keep_dirs) = explode("\n", $info['param']);

do_reinstall:
log_operation($info['shellid'], 'reinstall-confirm', array('distribution'=>$distribution, 'keep_dirs'=>$keep_dirs));

$gallery_id = null;
if (preg_match('/^gallery-([0-9]+)$/', $distribution, $matches)) {
    $gallery_id = $matches[1];
    if (!is_in_gallery($gallery_id)) {
        alert('No such item in gallery.');
    }
    $distribution = mysql_result(checked_mysql_query("SELECT distribution FROM shellinfo WHERE id='$gallery_id'"), 0);
}
if (check_distribution($distribution))
    die('Invalid distribution!');
if (check_keep_dirs($keep_dirs))
    die('Invalid keep directory list!');

lock_shell_or_die($info['shellid']);

checked_mysql_query("UPDATE tickets SET used_time=NOW() WHERE id='$ticket_id'");
if ($info['distribution'] != $distribution) {
    checked_mysql_query("UPDATE shellinfo SET distribution='$distribution' WHERE id='".$info['shellid']."'");
    if (mysql_affected_rows() != 1) {
        unlock_shell($info['shellid'], false);
        die('Failed to set distribution in database');
    }
    $info['distribution'] = $distribution;
}
?>
<div id="wrapper">
<div id="regtitle">
        	<h1>Reinstalling</h1>
        	<div id="progbar">
            </div>
<p>Your freeshell #<?=$info['shellid']?> is reinstalling.</p>
<p>We will send you an email containing the new root password.</p>
</div>
<div id="regbutton" onclick="javascript:document.location.href='http://email.ustc.edu.cn'">
        	<p>Check Email</p>
</div>
</div>
<?php
fastcgi_finish_request();

function do_copy_from_gallery($info, $password, $gallery_id, $keep_dirs) {
    $gallery_node = mysql_result(checked_mysql_query("SELECT nodeno FROM shellinfo WHERE id='$gallery_id'"), 0);
    return destroy_vz($info['nodeno'], $info['shellid'], $keep_dirs)
        && copy_vz_without_activate($gallery_node, $gallery_id, $info['nodeno'], $info['shellid'], $info['hostname'])
        && copy_freeshell_config($gallery_id, $info['shellid'])
        && control_vz($info['nodeno'], 'reset-root', $info['shellid']." ".$password, $password)
        && reactivate_vz($info['nodeno'], $info['shellid'], $info['distribution']);
}

function do_reinstall($info, $password, $keep_dirs) {
    if (!destroy_vz($info['nodeno'], $info['shellid'], $keep_dirs))
        return false;
    if (!create_vz($info['nodeno'], $info['shellid'], $info['hostname'], $password, node_default_mem_limit($info['nodeno']), $info['diskspace_softlimit'], $info['diskspace_hardlimit'], $info['distribution']))
        return false;
    if (!reactivate_vz($info['nodeno'], $info['shellid'], $info['distribution']))
        return false;
    return true;
}

$password = random_string(12);
if ($gallery_id)
    $status = do_copy_from_gallery($info, $password, $gallery_id, $keep_dirs);
else
    $status = do_reinstall($info, $password, $keep_dirs);
unlock_shell($info['shellid'], $status);
if ($status) {
    send_reinstall_success_email($info['email'], $info['shellid'], $info['hostname'], $password);
} else {
    send_reinstall_failure_email($info['email'], $info['shellid']);
}
