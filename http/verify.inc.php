<?php
function checkemail($email) {
    if (strlen($email) > 50)
        return 4;
    if (!preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z._-]+$/', $email))
        return 2;
    if (!preg_match('/^[a-zA-Z0-9._-]+@(lug.ustc.edu.cn|mail.ustc.edu.cn|ustc.edu.cn|ustc.edu)$/', $email))
        return 3;
    if (!check_email_count($email))
        return 1;
    return 0;
}

function checkhost($folder) {
    if (strlen($folder) < 3)
        return 5;
    if (strlen($folder) > 30)
        return 3;
    if (!preg_match('/^[a-z0-9][a-z0-9-]+[a-z0-9]$/', $folder))
        return 1;
    include_once "db.php";
    $rs = mysql_query("SELECT COUNT(*) FROM shellinfo WHERE `hostname`='$folder'");
    if (mysql_result($rs,0) != 0)
        return 2;
    return 0;
}

function check_email_count($email) {
    include_once "db.php";
    $rs = mysql_query("SELECT COUNT(*) FROM shellinfo WHERE `email`='$email'");
    return mysql_result($rs,0) < 14;
}

function sanitize_url($url) {
    if (!preg_match('/^[a-zA-Z0-9:_&+%=?\/.-]+$/', $url))
        return "";
    return filter_var($url, FILTER_VALIDATE_URL);
}
