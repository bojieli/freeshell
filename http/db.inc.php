<?php
function checked_mysql_query($sql) {
    mysql_query($sql);
    if (mysql_error()) {
        report_sys_admin("Mysql query error: ".mysql_error()."\nSQL:\n$sql");
    }
}
