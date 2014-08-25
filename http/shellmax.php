<?php
if ($_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR'] && $_SERVER['REMOTE_ADDR'] != '127.0.0.1')
    exit();

include_once "db.php";
$shellmax = mysql_result(checked_mysql_query("SELECT MAX(id) FROM shellinfo"), 0);
echo $shellmax;
?>
