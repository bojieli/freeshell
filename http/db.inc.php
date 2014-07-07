<?php
function checked_mysql_query($sql) {
    $rs = mysql_query($sql);
    if (mysql_error()) {
        report_sys_admin("Mysql query error: ".mysql_error()."\nSQL:\n$sql");
    }
    return $rs;
}

function report_sys_admin($msg) {
    $headers = 'From: "Freeshell Notification" <noreply@freeshell.ustc.edu.cn>' . "\r\n" .
        'MIME-Version: 1.0' . "\r\n" .
        'Content-Type: text/plain; charset=utf-8' . "\r\n" .
        'Content-Disposition: inline' . "\r\n" .
        'Content-Transfer-Encoding: 8bit';
    $title = "Freeshell System Alert";
    $body = $msg;
    $admin_email = "servmon@freeshell.ustc.edu.cn";
    mail($admin_email, $title, $body, $headers);
}
