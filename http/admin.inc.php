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
    $body = "Hello,\n\nThanks for being the ".($appid-100)."-th user of USTC freeshell. Please click on the link below (or copy it to the address bar) to activate your blog account.\n\nhttp://blog.ustc.edu.cn/freeshell/activate.php?appid=$appid&token=$token\n\nThis link will expire in 48 hours. Any problems, please email us: lug@ustc.edu.cn\n\nSincerely,\nUSTC Freeshell Team";
    $headers = 'From: noreply@blog.ustc.edu.cn';
    mail($email, $title, $body, $headers);
}

