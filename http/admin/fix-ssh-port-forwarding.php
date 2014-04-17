<?php
session_start();
if (empty($_SESSION['isadmin']))
    exit();
include_once '../db.php';
include_once '../nodes.inc.php';

$rs = checked_mysql_query("SELECT id, nodeno FROM shellinfo WHERE isactive=1");
while ($row = mysql_fetch_array($rs)) {
    add_ssh_port_forwarding($row['id'], $row['nodeno']);
}
echo "Done.";
