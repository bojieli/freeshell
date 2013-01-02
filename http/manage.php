<?php
include_once "nodes.inc.php";
include_once "db.php";

session_start();
if (empty($_SESSION['email']))
    die('Not login');
if (!is_numeric($_POST['appid']))
    die('Invalid appid');
$id = $_POST['appid'];
$a = mysql_result(mysql_query("SELECT * FROM shellinfo WHERE `id`='$id'"),0);

switch ($_POST['action']) {
    case 'start':
    case 'reboot':
    case 'stop':
        control_vz($a['nodeno'], $_POST['action'], $a['id']);
    default:
        die('Unsupported action');
}
