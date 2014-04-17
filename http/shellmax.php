<?php
include_once "db.php";
$shellmax = mysql_result(checked_mysql_query("SELECT MAX(id) FROM shellinfo"), 0);
echo $shellmax;
?>
