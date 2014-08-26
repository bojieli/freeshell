<?php
session_start();
session_write_close();
if (empty($_SESSION['isadmin']))
    exit();
include_once '../db.php';
include_once '../nodes.inc.php';
include_once "../dns.inc.php";

$counter = 0;
$ok = true;
$ns = new nsupdate();

$rs = checked_mysql_query("SELECT id, hostname FROM shellinfo WHERE isactive=1");
while ($row = mysql_fetch_array($rs)) {
    __update_dns($ns, $row['hostname'], $row['id']);
    // DNS messages are up to 65536 bytes, do not add too many records in one nsupdate
    if (++$counter >= 100) {
        if (!$ns->commit())
            $ok = false;
        $counter = 0;
        $ns = new nsupdate();
    }
}

echo $ns->commit() && $ok ? 'Done' : 'Failed';
