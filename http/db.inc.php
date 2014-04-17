<?php
function checked_mysql_query($sql) {
    $rs = mysql_query($sql);
    if (mysql_error()) {
        report_sys_admin("Mysql query error: ".mysql_error()."\nSQL:\n$sql");
    }
    return $rs;
}

function report_sys_admin($msg) {
    $headers = 'From: "Freeshell Notification" <noreply@freeshell.ustc.edu.cn>';
    $title = "Freeshell System Alert";
    $body = $msg;
    $admin_email = "servmon@freeshell.ustc.edu.cn";
    mail($admin_email, $title, $body, $headers);
}
