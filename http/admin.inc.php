<?php
include_once "nodes.inc.php";

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

$headers = 'From: "Freeshell Notification" <noreply@freeshell.ustc.edu.cn>';
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
    $body = greetings($appid)."Thanks for being the ".($appid-100)."-th user of USTC freeshell. Please click on the link below (or copy it to the address bar) to activate your freeshell account.\n\n".site_baseurl()."/activate.php?appid=$appid&token=$token\n\nThis link will expire in 48 hours.".$footer;
    mail($email, $title, $body, $headers);
}

function send_reset_root_email($email, $appid, $new_passwd) {
    global $headers, $footer;
    $title = "New Root Password for your Freeshell";
    $body = greetings($appid)."You have requested root password reset for shell ID $appid on ".site_baseurl().".\n\nNew root password: $new_passwd\n\nAs a special remainder, please change root password as soon as possible.\n\nIf you did not request this password reset, maybe your web account is stolen, please contact us.".$footer;
    mail($email, $title, $body, $headers);
}

function send_reinstall_success_email($email, $appid, $hostname, $password) {
    global $headers, $footer;
    $title = "Your Freeshell is Reinstalled";
    $body = greetings($appid)."Freeshell ID $appid is reinstalled.\n\nNew hostname: $hostname\nNew root password: $password\n\nLogin IP and port does not change, so you may need to edit ~/.ssh/known_hosts to avoid conflicting keys. If you forget how to SSH, please login to ".site_baseurl()." for info.\nAs a special remainder, please change root password as soon as possible.".$footer;
    mail($email, $title, $body, $headers);
}

function need_email_verification($name, $msg, $action, $param, $email, $appid) {
    global $headers, $footer;
    $token = random_string(40);
    mysql_query("INSERT INTO tickets (shellid,create_time,action,token,param) VALUES ('$appid', NOW(), '".addslashes($action)."', '$token', '".addslashes($param)."')");
    $id = mysql_insert_id();
    if (!is_numeric($id) || $id == 0)
        return "Failed to generate ticket. Please contact support@freeshell.ustc.edu.cn";

    $title = "Freeshell Danger Action Confirmation: $name";
    $body = greetings($appid)."You have requested $name for shell ID $appid on ".site_baseurl().". This is a DANGER action, so we need your confirmation to proceed.\n\n$msg\n\nFollow this link to perform $name immediately and irreversibly:\n".site_baseurl()."/$action?id=$id&token=$token\n\nThis link will expire in 48 hours.\nIf you did not request this action, maybe your account is stolen, please contact us.".$footer;
    mail($email, $title, $body, $headers);

    return "Since this is a danger action, please check your mailbox and follow the link in confirmation email.";
}

function send_manage_notify_email($email, $appid, $action, $additional_info = "") {
    global $headers, $footer;
    $title = "Your freeshell has $action";
    $body = greetings($appid)."This email is to notify you that shell ID $appid has been $action via Web control panel ".site_baseurl().".\n\n$additional_info\nIf you did not request this action, maybe your web account is stolen, please contact us.".$footer;
    mail($email, $title, $body, $headers);
}

function send_change_password_email($email, $appid) {
    global $headers, $footer;
    $title = "Freeshell $appid Control Panel Password Changed";
    $body = greetings($appid)."This email is to notify you that Control Panel Password for shell ID $appid has been changed via Web control panel ".site_baseurl().".\n\nIf you did not request this action, maybe your web account is stolen, please contact us.".$footer;
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
    mysql_query("UPDATE shellinfo SET `password`='".addslashes($salted_password)."' WHERE id='$id'");
}

function check_password($plain, $salted) {
    $passes = explode('/', $salted);
    return (sha1(sha1($plain).$passes[1]) === $passes[0]);
}

function send_admin_email($email, $appid, $title, $body) {
    global $headers, $footer;
    $body = "Hello freeshell $appid,\n\n".$body.$footer;
    mail($email, $title, $body, $headers);
}

function get_next_appid($nodeno) {
    $nodeno = (int)$nodeno % nodes_num();
    if ($nodeno < 0)
        return null;
    $max = mysql_result(mysql_query("SELECT MAX(id) FROM shellinfo"),0);
    $appid = $max ? (int)$max + 1 : 1;
    while ($appid % nodes_num() != $nodeno)
        ++$appid;
    return $appid;
}

function create_freeshell_in_db($hostname, $salted_pass, $email, $nodeno, $distribution) {
    $query = "INSERT INTO shellinfo SET `hostname`='$hostname', `password`='$salted_pass', `email`='$email', `distribution`='$distribution'";
    
    if ($nodeno && is_numeric($nodeno)) {
        $query .= ",id=".get_next_appid($nodeno);
    }
    
    mysql_query($query);
    $appid = mysql_insert_id();

    $real_nodeno = $appid % nodes_num();
    if ($real_nodeno == 0)
        $real_nodenonodeno = nodes_num();
    mysql_query("UPDATE shellinfo SET nodeno=$real_nodeno WHERE id=$appid");

    return array($appid, $real_nodeno);
}

function move_freeshell_in_db($old_id, $nodeno) {
    $appid = get_next_appid($nodeno);
    mysql_query("UPDATE shellinfo SET id=$appid, nodeno=$nodeno WHERE id=$old_id");
    return $appid;
}

function copy_freeshell_config($old_id, $new_id)
{
    $fields = array("diskspace_softlimit", "diskspace_hardlimit", "distribution");
    $info = mysql_fetch_array(mysql_query("SELECT ".implode(',',$fields)." FROM shellinfo WHERE id=$old_id"));
    if (empty($info))
        return false;
    $values = array();
    foreach ($fields as $field) {
        $values[] = "`$field`='".addslashes($info[$field])."'";
    }
    mysql_query("UPDATE shellinfo SET ".implode(',', $values)." WHERE id=$new_id");
    return (mysql_affected_rows() == 1);
}

/* return 0 for success
 * return 1 for too many endpoints
 * return 2 for endpoint already taken
 * return 3 for database error
 */
function db_add_endpoint($id, $public_endpoint, $private_endpoint) {
    $count = mysql_result(mysql_query("SELECT COUNT(*) FROM endpoint WHERE `id`='$id'"), 0);
    if ($count >= 10)
        return 1;
    $count = mysql_result(mysql_query("SELECT COUNT(*) FROM endpoint WHERE `public_endpoint`='$public_endpoint'"), 0);
    if ($count != 0)
        return 2;
    mysql_query("INSERT INTO endpoint SET `id`='$id', `public_endpoint`='$public_endpoint', `private_endpoint`='$private_endpoint'");
    if (mysql_affected_rows() == 1)
        return 0;
    return 3;
}

function db_remove_endpoint($id, $public_endpoint, $private_endpoint) {
    mysql_query("DELETE FROM endpoint WHERE `id`='$id' AND `public_endpoint`='$public_endpoint' AND `private_endpoint`='$private_endpoint'");
    return (mysql_affected_rows() == 1);
}

function db_remove_all_endpoints($id) {
    mysql_query("DELETE FROM endpoint WHERE `id`='$id'");
}
