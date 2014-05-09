<?php
session_start();
session_write_close();
if (empty($_SESSION['isadmin']))
    exit();
include_once '../db.php';
include_once '../nodes.inc.php';

$rs = checked_mysql_query("SELECT id, nodeno FROM shellinfo WHERE isactive=1");
while ($row = mysql_fetch_array($rs)) {
    add_ssh_port_forwarding($row['id'], $row['nodeno']);
}
$rs = checked_mysql_query("SELECT shellinfo.id, nodeno, public_endpoint, private_endpoint, protocol FROM endpoint, shellinfo WHERE endpoint.id = shellinfo.id");
while ($row = mysql_fetch_array($rs)) {
    add_endpoint($row['id'], $row['nodeno'], $row['public_endpoint'], $row['private_endpoint'], $row['protocol']);
}
echo "Done.";
