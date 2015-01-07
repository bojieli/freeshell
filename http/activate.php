<?php
include_once "header.php";
include_once "config.inc.php";
include_once "nodes.inc.php";
include_once "db.php";
include_once "verify.inc.php";
include_once "admin.inc.php";

$regid = intval($_GET['appid']);
$row = mysql_fetch_array(checked_mysql_query("SELECT reginfo FROM pending_register WHERE id='$regid'"));
if (empty($row)) {
    alert('Token does not exist, please retry register');
}
extract(unserialize($row['reginfo']));
if (sha1($token) !== sha1($_GET['token'])) {
    alert('Invalid token, please retry register');
}
if (time() - $time > 48 * 3600) {
    alert('Your registration has expired, please retry register');
}

if ($distribution == 'gallery') {
    if (!is_in_gallery($gallery_id)) {
        alert('No such item in gallery.');
    }
    $gallery_id = intval($gallery_id);
    $distribution = mysql_result(checked_mysql_query("SELECT distribution FROM shellinfo WHERE id='$gallery_id'"), 0);
}
if (check_distribution($distribution)) {
    alert('Sorry, this distribution is no longer supported.');
}

list($appid, $nodeno) = create_freeshell_in_db($hostname, generate_password($password), $email, $nodeno, $distribution, DEFAULT_STORAGE_BASE);
if (!$appid)
    alert('Database error, please retry. If the problem persists, please contact support@freeshell.ustc.edu.cn');

$info = mysql_fetch_array(checked_mysql_query("SELECT * FROM shellinfo WHERE `id`='$appid'"));
if (empty($info))
    die('Failed to create freeshell, please contact us at support@freeshell.ustc.edu.cn');

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
<p>Your freeshell is being installed. We will send you: <?=$info['email']?> an email after installation finishes. 
<p>You may go to the control panel for more info:</p>
</div>
<div id="regbutton" onclick="javascript:document.location.href='admin.php'">
        	<p>Let's Rock!</p>
</div>
</div>
</body>
</html>
<?php
session_write_close();
fastcgi_finish_request();

log_operation($appid, 'activate');
checked_mysql_query("DELETE FROM pending_register WHERE id='$regid'");
if (!try_lock_shell($appid)) {
    send_register_fail_mail($email);
    exit();
}

if ($distribution != 'gallery') {
    if (!create_vz($nodeno, $appid, $hostname, $password, node_default_mem_limit($nodeno), $info['diskspace_softlimit'], $info['diskspace_hardlimit'], $info['distribution'], $info['storage_base'])) {
        unlock_shell($appid, $status);
        delete_freeshell_in_db($appid);
        send_register_fail_mail($email);
        exit();
    }
} else {
    $gallery_node = mysql_result(checked_mysql_query("SELECT nodeno FROM shellinfo WHERE id='$gallery_id'"), 0);
    if (!copy_vz_without_activate($gallery_node, $gallery_id, $nodeno, $appid, $hostname, $info['storage_base'])
        || !copy_freeshell_config($gallery_id, $appid)
        || !control_vz($nodeno, 'reset-root', "$appid $password", $password))
    {
        destroy_vz($nodeno, $appid);
        unlock_shell($appid, $status);
        delete_freeshell_in_db($appid);
        send_register_fail_mail($email);
        exit();
    }
}

$status = activate_vz($info['nodeno'], $appid, $info['distribution']);
unlock_shell($info['id'], $status);
if (!$status) {
    send_register_fail_mail($email);
} else {
    send_register_success_mail($email, $appid, $info['hostname']);
}
?>
