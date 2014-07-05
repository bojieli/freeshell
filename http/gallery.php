<?php
include_once "db.php";
$gallery = array();
$rs = checked_mysql_query("SELECT id, distribution, public_name, public_description FROM shellinfo WHERE is_public=true");
while ($row = mysql_fetch_array($rs)) {
    $gallery[$row['id']] = array(
        'name' => htmlspecialchars($row['public_name']),
        'distribution' => htmlspecialchars($row['distribution']),
        'description' => nl2br(htmlspecialchars($row['public_description'])),
    );
}
echo json_encode($gallery);
?>
