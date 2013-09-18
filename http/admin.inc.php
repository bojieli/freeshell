<?php
function alert($msg) {
    die("<script>alert('$msg');location.href='index.php';</script>");
}
function internal_error($msg) {
    alert("Internal Error, please retry. Error message: $msg");
}
function error($msg) {
    echo "<script>";
    echo "alert('$msg');";
    echo "window.location.href='index.php';";
    echo "</script>";
    exit();
}

function send_activate_mail($email, $appid, $token) {
    $title = "Account Activation for USTC freeshell";
    $body = "Hello,\n\nThanks for being the ".($appid-100)."-th user of USTC freeshell. Please click on the link below (or copy it to the address bar) to activate your freeshell account.\n\nhttp://freeshell.ustc.edu.cn/activate.php?appid=$appid&token=$token\n\nThis link will expire in 48 hours. Any problems, please email us: lug@ustc.edu.cn\n\nSincerely,\nUSTC Freeshell Team";
    $headers = 'From: noreply@blog.ustc.edu.cn';
    mail($email, $title, $body, $headers);
}

function send_reset_root_email($email, $appid, $new_passwd) {
    $title = "New Root Password for your Freeshell";
    $body = "Hello,\n\nYou have requested root password reset for shell ID $appid on http://freeshell.ustc.edu.cn.\n\nNew root password: $new_passwd\n\nPlease login and change it as soon as possible.\n\nIf you did not request this password reset, maybe your web account is stolen, please contact us.\nAny problems, please email us: lug@ustc.edu.cn\n\nSincerely,\nUSTC Freeshell Team";
    $headers = 'From: noreply@blog.ustc.edu.cn';
    mail($email, $title, $body, $headers);
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
    $body = "Hello,\n\nThis email is to notify you that shell ID $appid has been $actioned via Web control panel http://freeshell.ustc.edu.cn.\n\nIf you did not request this action, maybe your web account is stolen, please contact us.\nAny problems, please email us: lug@ustc.edu.cn\n\nSincerely,\nUSTC Freeshell Team";
    $headers = 'From: noreply@blog.ustc.edu.cn';
    mail($email, $title, $body, $headers);
}
