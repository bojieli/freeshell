<?php
include_once "nodes.inc.php";

mb_language("uni");
mb_internal_encoding('UTF-8');

function alert_noredirect($msg) {
    echo "<script>alert('$msg');</script>";
}
function alert($msg) {
    die("<script>alert('$msg');location.href='index.php';</script>");
}
function internal_error($msg) {
    alert("Internal Error, please retry. Error message: $msg");
}
function error($msg, $info = "") {
    if (!$info)
        $info = "Should you have any problem, please feel free to contact support@freeshell.ustc.edu.cn<p>If you have problem receiving our email or find our email in spam box,<br />please add noreply@freeshell.ustc.edu.cn to contact list.";
    ?>
<div id="wrapper">
<div id="regtitle">
        	<h1><?=$msg?></h1>
        	<div id="progbar">
            </div>
            <style>
            p { line-height: 22px }
            </style>
            <p><?=$info?></p>
</div>
<div id="regbutton" onclick="javascript:window.location.href='index.php'">
        	<p>Go Back</p>
</div>
</div>
</body>
</html>
<?php
    exit(); // exit after error message is output
} // end function error

$headers = 'From: "Freeshell Notification" <noreply@freeshell.ustc.edu.cn>' . "\r\n" .
    'MIME-Version: 1.0' . "\r\n" .
    'Content-Type: text/plain; charset=utf-8' . "\r\n" .
    'Content-Disposition: inline' . "\r\n" .
    'Content-Transfer-Encoding: 8bit';
$footer = "\n\nThis is an automated email, please do not reply. Any problem, please email us: support@freeshell.ustc.edu.cn\n\nSincerely,\nUSTC Freeshell Team";

function site_baseurl() {
    return 'https://'.$_SERVER['SERVER_NAME'];
}

function greetings($appid){
    return "Hello Freeshell #$appid,\n\n";
}

function send_activate_mail($email, $appid, $token) {
    global $headers, $footer;
    $title = "Account Activation for USTC freeshell";
    $body = "Hello,\n\nThanks for choosing USTC freeshell. Please click on the link below (or copy it to the address bar) to activate your freeshell account.\n\n".site_baseurl()."/activate.php?appid=$appid&token=$token\n\nThis link will expire in 48 hours.".$footer;
    mail($email, $title, $body, $headers);
}

function send_register_fail_mail($email) {
    global $headers, $footer;
    $title = "Register Failed for USTC freeshell";
    $body = "Hello,\n\nSorry that your freeshell failed to be created. It might because the hard disk is full or the network is temporarily down. Please try again later.".$footer;
    mail($email, $title, $body, $headers);
}

function send_register_success_mail($email, $appid, $hostname) {
    global $headers, $footer;
    $title = "Your USTC freeshell has been created";
    $body = greetings($appid)."Your freeshell '$hostname' is up and running. You can login to your freeshell via IPv6:\n\nssh root@".get_shell_v6_dns_name($hostname)."\n\nFor alternative login methods and more information, please login to Web Control Panel: https://freeshell.ustc.edu.cn/".$footer;
    mail($email, $title, $body, $headers);
}

function send_reset_root_email($email, $appid, $new_passwd) {
    global $headers, $footer;
    $title = "New Root Password for your Freeshell";
    $body = greetings($appid)."You have requested root password reset for shell ID $appid on ".site_baseurl()."\n\nNew root password: $new_passwd\n\nAs a special remainder, please change root password as soon as possible.\n\nIf you did not request this password reset, maybe your web account is stolen, please contact us.".$footer;
    mail($email, $title, $body, $headers);
}

function send_reinstall_success_email($email, $appid, $hostname, $password) {
    global $headers, $footer;
    $title = "Your Freeshell is Reinstalled";
    $body = greetings($appid)."Freeshell ID $appid is reinstalled.\n\nNew hostname: $hostname\nNew root password: $password\n\nLogin IP and port does not change, so you may need to edit ~/.ssh/known_hosts to avoid conflicting keys. If you forget how to SSH, please login to ".site_baseurl()." for info.\nAs a special remainder, please change root password as soon as possible.".$footer;
    mail($email, $title, $body, $headers);
}

function send_reinstall_failure_email($email, $appid) {
    global $headers, $footer;
    $title = "Your Freeshell failed to Reinstall";
    $body = greetings($appid)."Sorry that your freeshell failed to reinstall. It may because the hard disk is full, or the network is temporarily down. Please try again later.".$footer;
    mail($email, $title, $body, $headers);
}

function need_email_verification($name, $msg, $action, $param, $email, $appid) {
    global $headers, $footer;
    $token = random_string(40);
    checked_mysql_query("INSERT INTO tickets (shellid,create_time,action,token,param) VALUES ('$appid', NOW(), '".addslashes($action)."', '$token', '".addslashes($param)."')");
    $id = mysql_insert_id();
    if (!is_numeric($id) || $id == 0)
        return "Failed to generate ticket. Please contact support@freeshell.ustc.edu.cn";

    $title = "Freeshell Danger Action Confirmation: $name";
    $body = greetings($appid)."You have requested $name for shell ID $appid on ".site_baseurl()." . This is a DANGER action, so we need your confirmation to proceed.\n\n$msg\n\nFollow this link to perform $name immediately and irreversibly:\n".site_baseurl()."/$action?id=$id&token=$token\n\nThis link will expire in 48 hours.\nIf you did not request this action, maybe your account is stolen, please contact us.".$footer;
    mail($email, $title, $body, $headers);

    return "Since this is a danger action, please check your mailbox and follow the link in confirmation email.";
}

function send_manage_notify_email($status, $email, $appid, $action, $additional_info = "") {
    global $headers, $footer;
    $title = "Your freeshell ".($status ? "succeeded in " : "FAILED TO")." $action";
    if (!$status)
        $additional_info = "This failure may be caused by a temporary network failure. Please try again later. The freeshell developers will be awared of this issue.\n".$additional_info;
    $body = greetings($appid)."This email is to notify you that shell ID $appid ".($status ? "has been" : "FAILED TO")." $action via Web control panel ".site_baseurl()."\n\n$additional_info\nIf you did not request this action, maybe this action is done by a system administrator (you should receive another email on the details), or your web account has been compromised, please contact us.".$footer;
    mail($email, $title, $body, $headers);
}

function send_admin_manage_email($email, $copy_email, $appid, $action, $additional_info = "") {
    global $headers, $footer;
    $title = "Your freeshell has been $action"."ed";
    $body = greetings($appid)."This email is to notify you that shell ID $appid has been $action"."ed by the administrator.\n\n$additional_info\n".$footer;
    mail($email, $title, $body, $headers);
    if ($email != $copy_email)
        mail($copy_email, $title, $body, $headers);
}

function send_change_password_email($email, $appid) {
    global $headers, $footer;
    $title = "Freeshell $appid Control Panel Password Changed";
    $body = greetings($appid)."This email is to notify you that Control Panel Password for shell ID $appid has been changed via Web control panel ".site_baseurl()."\n\nIf you did not request this action, maybe your web account is stolen, please contact us.".$footer;
    mail($email, $title, $body, $headers);
}

function send_reset_password_email($email, $appid, $password) {
    global $headers, $footer;
    $title = "Your Freeshell Control Panel Password is Reset";
    $body = greetings($appid)."Control panel password for freeshell $appid is reset.\n\nNew password: $password\n\nPlease login to ".site_baseurl()." using your email and new password, and change it as soon as possible.\n\nIf you did not request this action, maybe your web account is stolen, please contact us.".$footer;
    mail($email, $title, $body, $headers);
}

function send_change_quota_email($email, $appid, $old, $new) {
    global $headers, $footer;
    $title = "Your Freeshell Disk Quota Increased to $new";
    $body = greetings($appid)."We have found your disk space is nearly exhausted, so we have increased your disk quota from $old to $new. Thanks for choosing freeshell.".$footer;
    mail($email, $title, $body, $headers);
}

function generate_password($password) {
    $salt = random_string(20);
    return sha1(sha1($password).$salt) . '/'. $salt;
}

function change_password($id, $salted_password) {
    checked_mysql_query("UPDATE shellinfo SET `password`='".addslashes($salted_password)."' WHERE id='$id'");
}

function check_password($plain, $salted) {
    $passes = explode('/', $salted);
    return (sha1(sha1($plain).$passes[1]) === $passes[0]);
}

function send_admin_email($email, $appid, $title, $body) {
    global $headers, $footer;
    if (!mb_check_encoding($title, "ASCII"))
	    $title=mb_encode_mimeheader($title);
    $body = "Hello freeshell $appid,\n\n".$body.$footer;
    mail($email, $title, $body, $headers);
}

function get_next_appid($nodeno) {
    $nodeno = (int)$nodeno % nodes_num();
    if ($nodeno < 0)
        return null;
    $max = mysql_result(checked_mysql_query("SELECT MAX(id) FROM shellinfo"),0);
    $appid = $max ? (int)$max + 1 : 1;
    while ($appid % nodes_num() != $nodeno)
        ++$appid;
    return $appid;
}

function create_freeshell_in_db($hostname, $salted_pass, $email, $nodeno, $distribution, $storage_base) {
    $query = "INSERT INTO shellinfo SET `hostname`='".mysql_real_escape_string($hostname)."', `password`='".mysql_real_escape_string($salted_pass)."', `email`='".mysql_real_escape_string($email)."', `distribution`='".mysql_real_escape_string($distribution)."', `storage_base`='".mysql_real_escape_string($storage_base)."'";
    
    if ($nodeno && is_numeric($nodeno)) {
        $query .= ",id=".get_next_appid($nodeno);
    }
    
    checked_mysql_query($query);
    $appid = mysql_insert_id();

    $real_nodeno = $appid % nodes_num();
    if ($real_nodeno == 0)
        $real_nodeno = nodes_num();
    checked_mysql_query("UPDATE shellinfo SET nodeno=$real_nodeno WHERE id=$appid");

    return array($appid, $real_nodeno);
}

// return newly assigned ID if success, return false if error
function move_freeshell_in_db($old_id, $nodeno, $new_storage_base) {
    $appid = get_next_appid($nodeno);
    // the new freeshell should be activated later
    checked_mysql_query("UPDATE shellinfo SET id=$appid, nodeno=$nodeno, isactive=0, storage_base='".mysql_real_escape_string($new_storage_base)."' WHERE id=$old_id");
    if (mysql_affected_rows() == 1) {
        checked_mysql_query("UPDATE tickets SET shellid=$appid WHERE shellid=$old_id");
        checked_mysql_query("UPDATE cname SET id=$appid WHERE id=$old_id");
        checked_mysql_query("UPDATE endpoint SET id=$appid WHERE id=$old_id");
        return $appid;
    }
    else
        return false;
}

function delete_freeshell_in_db($id) {
    // tickets should be saved
    checked_mysql_query("DELETE FROM cname WHERE id=$id");
    checked_mysql_query("DELETE FROM endpoint WHERE id=$id");
    // delete shellinfo should be the last step
    checked_mysql_query("DELETE FROM shellinfo WHERE id=$id");
    return (mysql_affected_rows() == 1);
}

function copy_freeshell_config($old_id, $new_id)
{
    $fields = array("diskspace_softlimit", "diskspace_hardlimit", "distribution");
    $info = mysql_fetch_array(checked_mysql_query("SELECT ".implode(',',$fields)." FROM shellinfo WHERE id=$old_id"));
    if (empty($info))
        return false;
    $values = array();
    foreach ($fields as $field) {
        $values[] = "`$field`='".addslashes($info[$field])."'";
    }
    checked_mysql_query("UPDATE shellinfo SET ".implode(',', $values)." WHERE id=$new_id");
    return (mysql_errno() == 0);
}

/* return 0 for success
 * return 1 for too many endpoints
 * return 2 for endpoint already taken
 * return 3 for database error
 */
function db_add_endpoint($id, $public_endpoint, $private_endpoint, $protocol) {
    $count = mysql_result(checked_mysql_query("SELECT COUNT(*) FROM endpoint WHERE `id`='$id'"), 0);
    if ($count >= 10)
        return 1;
    $count = mysql_result(checked_mysql_query("SELECT COUNT(*) FROM endpoint WHERE `public_endpoint`='$public_endpoint' AND `protocol`='$protocol'"), 0);
    if ($count != 0)
        return 2;
    checked_mysql_query("INSERT INTO endpoint SET `id`='$id', `public_endpoint`='$public_endpoint', `private_endpoint`='$private_endpoint', `protocol`='$protocol'");
    if (mysql_affected_rows() == 1)
        return 0;
    return 3;
}

function db_remove_endpoint($id, $public_endpoint, $private_endpoint, $protocol) {
    checked_mysql_query("DELETE FROM endpoint WHERE `id`='$id' AND `public_endpoint`='$public_endpoint' AND `private_endpoint`='$private_endpoint' AND `protocol`='$protocol'");
    return (mysql_affected_rows() == 1);
}

function db_remove_all_endpoints($id) {
    checked_mysql_query("DELETE FROM endpoint WHERE `id`='$id'");
}

function db_update_public($shellid, $is_public, $name, $description) {
    $name = trim($name);
    $description = trim($description);
    if (strlen($name) >= 200 || ($is_public && strlen($name) == 0))
        return false;
    if (strlen($description) >= 3000)
        return false;
    checked_mysql_query("UPDATE shellinfo SET is_public=".($is_public ? 'true' : 'false').", public_name='".mysql_real_escape_string($name)."', public_description='".mysql_real_escape_string($description)."' WHERE id='".$shellid."'");
    return (mysql_affected_rows() == 1);
}

function try_lock_shell($id) {
    checked_mysql_query("UPDATE shellinfo SET locked=1 WHERE id='$id'");
    if (mysql_affected_rows() == 1) {
        checked_mysql_query("UPDATE shellinfo SET lock_time='".time()."' WHERE id='$id'");
        checked_mysql_query("START TRANSACTION;");
        return true;
    }
    else return false;
}

function unlock_shell($id, $should_commit = true) {
    if ($should_commit)
        checked_mysql_query("COMMIT;");
    else
        checked_mysql_query("ROLLBACK;");
    checked_mysql_query("UPDATE shellinfo SET locked=0 WHERE id='$id'");
    return (mysql_affected_rows() == 1);
}

function lock_shell_or_die($id) {
    if (try_lock_shell($id))
        return true;
    else
        die("Another action is pending for your freeshell, please try again later.");
}

function log_operation($id, $action, $data = null) {
    if (!is_numeric($id) || $id <= 0 || $action == "") {
        report_sys_admin("log_operation failed:\nid = $id\naction = $action\ndata = $data");
        return;
    }
    $action = mysql_real_escape_string($action);
    if (!is_string($data))
        $data = json_encode($data);
    $data = mysql_real_escape_string($data);
    $time = time();
    checked_mysql_query("INSERT INTO operation_log (id, action, data, log_time) VALUES ($id, '$action', '$data', $time)");
}
