<?php
session_start();
if (empty($_SESSION['isadmin']))
    exit();
include_once '../db.php';
include_once '../nodes.inc.php';

$rs = mysql_query("SELECT id, hostname FROM shellinfo WHERE isactive=1");
while ($row = mysql_fetch_array($rs)) {
    update_dns($row['hostname'], $row['id']);
}
echo "Done.";
