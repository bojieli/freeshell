<?php
include_once "header.php";
session_write_close();
include_once "db.php";
include_once "verify.inc.php";
include_once "nodes.inc.php";
include_once "admin.inc.php";

$password = $_POST['regpassword'];
if ($password !== $_POST['regconfpass'])
    alert('Passwords mismatch.');
$email = $_POST['regemail'];
$hostname = addslashes($_POST['hostname']);
$distribution = $_POST['distribution'];

if (checkhost($hostname) || strlen($password)<6 || checkemail($email)) {
    alert('Sorry, sanity check failed.');
}
if ($distribution == 'gallery') {
    if (!is_in_gallery($_POST['gallery-id'])) {
        alert('No such item in gallery.');
    }
    $gallery_id = intval($_POST['gallery-id']);
    $distribution = mysql_result(checked_mysql_query("SELECT distribution FROM shellinfo WHERE id='$gallery_id'"), 0);
}
if (check_distribution($distribution)) {
    alert('Sorry, this distribution is no longer supported.');
}

list($appid, $nodeno) = create_freeshell_in_db($hostname, generate_password($password), mysql_real_escape_string($email), $_POST['nodeno'], mysql_real_escape_string($distribution));
if (!$appid)
    alert('Database error, please retry. If the problem persists, please contact support@freeshell.ustc.edu.cn');

$info = mysql_fetch_array(checked_mysql_query("SELECT * FROM shellinfo WHERE `id`='$appid'"));
if (empty($info))
    die('Failed to create freeshell, please contact us at support@freeshell.ustc.edu.cn');

lock_shell_or_die($appid);
?>
<div id="wrapper">
<div id="regtitle">
        	<h1>It's almost there!</h1>
        	<div id="progbar">
            </div>
<p>Your freeshell is installing.</p>
<p>We will send you: <?=$email?> an confirmation mail once installation finished.</p>
<p>Please activate your account by clicking the link inside the mail.</p>
</div>
<div id="regbutton" onclick="javascript:document.location.href='http://email.ustc.edu.cn'">
        	<p>Check now</p>
</div>
</div>
<?php
fastcgi_finish_request();
log_operation($appid, 'register', $_POST);
if ($_POST['distribution'] != 'gallery') {
    if (!create_vz($nodeno, $appid, $hostname, $password, node_default_mem_limit($nodeno), $info['diskspace_softlimit'], $info['diskspace_hardlimit'], $info['distribution'])) {
        delete_freeshell_in_db($appid);
        send_register_fail_mail($email);
        exit();
    }
} else {
    $gallery_node = mysql_result(checked_mysql_query("SELECT nodeno FROM shellinfo WHERE id='$gallery_id'"), 0);
    if (!copy_vz_without_activate($gallery_node, $gallery_id, $nodeno, $appid, $hostname)
        || !copy_freeshell_config($gallery_id, $appid)
        || !control_vz($nodeno, 'reset-root', "$appid $password", $password))
    {
        destroy_vz($nodeno, $appid);
        unlock_shell($appid);
        delete_freeshell_in_db($appid);
        send_register_fail_mail($email);
        exit();
    }
}

$token = random_string(40);
checked_mysql_query("UPDATE shellinfo SET `token`='$token' WHERE `id`='$appid'");
unlock_shell($appid);
send_activate_mail($email, $appid, $token);
