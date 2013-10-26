<?php
include_once "nodes.inc.php";

function alert($msg) {
    die("<script>alert('$msg');location.href='index.php';</script>");
}
function internal_error($msg) {
    alert("Internal Error, please retry. Error message: $msg");
}
function error($msg, $info) {
    if (!$info)
        $info = "Should you have any problem, please feel free to contact support@freeshell.ustc.edu.cn<p>If you have problem receiving our email or find our email in spam box,<br />please add support@freeshell.ustc.edu.cn to contact list.";
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
<?php
    exit();
}

function send_activate_mail($email, $appid, $token) {
    $title = "Account Activation for USTC freeshell";
    $body = "Hello,\n\nThanks for being the ".($appid-100)."-th user of USTC freeshell. Please click on the link below (or copy it to the address bar) to activate your freeshell account.\n\nhttp://freeshell.ustc.edu.cn/activate.php?appid=$appid&token=$token\n\nThis link will expire in 48 hours. Any problems, please email us: support@freeshell.ustc.edu.cn\n\nSincerely,\nUSTC Freeshell Team";
    $headers = 'From: "Freeshell Support" <support@freeshell.ustc.edu.cn>';
    mail($email, $title, $body, $headers);
}

function send_reset_root_email($email, $appid, $new_passwd) {
    $title = "New Root Password for your Freeshell";
    $body = "Hello,\n\nYou have requested root password reset for shell ID $appid on http://freeshell.ustc.edu.cn.\n\nNew root password: $new_passwd\n\nAs a special remainder, please change root password as soon as possible.\n\nIf you did not request this password reset, maybe your web account is stolen, please contact us.\nAny problems, please email us: support@freeshell.ustc.edu.cn\n\nSincerely,\nUSTC Freeshell Team";
    $headers = 'From: "Freeshell Support" <support@freeshell.ustc.edu.cn>';
    mail($email, $title, $body, $headers);
}

function send_reinstall_success_email($email, $appid, $hostname, $password) {
    $title = "Your Freeshell is Reinstalled";
    $body = "Hello,\n\nFreeshell ID $appid is reinstalled.\n\nNew hostname: $hostname\nNew root password: $password\n\nLogin IP and port does not change, so you may need to edit ~/.ssh/known_hosts to avoid conflicting keys. If you forget how to SSH, please login to http://freeshell.ustc.edu.cn for info.\nAs a special remainder, please change root password as soon as possible.\n\nAny problems, please email us: support@freeshell.ustc.edu.cn\n\nSincerely,\nUSTC Freeshell Team";
    $headers = 'From: "Freeshell Support" <support@freeshell.ustc.edu.cn>';
    mail($email, $title, $body, $headers);
}

function need_email_verification($name, $msg, $action, $email, $appid) {
    $token = random_string(40);
    mysql_query("INSERT INTO tickets (shellid,create_time,action,token) VALUES ('$appid', NOW(), '$action', '$token')");
    $id = mysql_insert_id();
    if (!is_numeric($id) || $id == 0)
        return "Failed to generate ticket. Please contact support@freeshell.ustc.edu.cn";

    $title = "Freeshell Danger Action Confirmation: $name";
    $body = "Hello,\n\nYou have requested $name for shell ID $appid on http://freeshell.ustc.edu.cn. This is a DANGER action, so we need your confirmation to proceed.\n\n$msg\n\nFollow this link to perform $name immediately and irreversibly:\nhttp://freeshell.ustc.edu.cn/$action?id=$id&token=$token\n\nThis link will expire in 48 hours.\nIf you did not request this action, maybe your account is stolen, please contact us.\nAny problems, please email us: support@freeshell.ustc.edu.cn\n\nSincerely,\nUSTC Freeshell Team";
    $headers = 'From: "Freeshell Support" <support@freeshell.ustc.edu.cn>';
    mail($email, $title, $body, $headers);

    return "Since this is a danger action, please check your mailbox and follow the link in confirmation email.";
}

function send_manage_notify_email($email, $appid, $action) {
    switch ($action) {
        case 'stop':
            $actioned = 'stopped';
            break;
        default:
            $actioned = $action . 'ed';
    }
    $title = "Your freeshell has $actioned";
    $body = "Hello,\n\nThis email is to notify you that shell ID $appid has been $actioned via Web control panel http://freeshell.ustc.edu.cn.\n\nIf you did not request this action, maybe your web account is stolen, please contact us.\nAny problems, please email us: support@freeshell.ustc.edu.cn\n\nSincerely,\nUSTC Freeshell Team";
    $headers = 'From: "Freeshell Support" <support@freeshell.ustc.edu.cn>';
    mail($email, $title, $body, $headers);
}

function send_change_password_email($email, $appid) {
    $title = "Freeshell $appid Control Panel Password Changed";
    $body = "Hello,\n\nThis email is to notify you that Control Panel Password for shell ID $appid has been changed via Web control panel http://freeshell.ustc.edu.cn.\n\nIf you did not request this action, maybe your web account is stolen, please contact us.\nAny problems, please email us: support@freeshell.ustc.edu.cn\n\nSincerely,\nUSTC Freeshell Team";
    $headers = 'From: "Freeshell Support" <support@freeshell.ustc.edu.cn>';
    mail($email, $title, $body, $headers);
}

function send_reset_password_email($email, $appid, $password) {
    $title = "Your Freeshell Control Panel Password is Reset";
    $body = "Hello,\n\nControl panel password for freeshell $appid is reset.\n\nNew password: $password\n\nPlease login to http://freeshell.ustc.edu.cn using your email and new password, and change it as soon as possible.\n\nIf you did not request this action, maybe your web account is stolen, please contact us.\nAny problems, please email us: support@freeshell.ustc.edu.cn\n\nSincerely,\nUSTC Freeshell Team";
    $headers = 'From: "Freeshell Support" <support@freeshell.ustc.edu.cn>';
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
