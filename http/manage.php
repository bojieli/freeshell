<?php
include_once "nodes.inc.php";
include_once "db.php";

session_start();
if (empty($_SESSION['email']))
    die('Not login');
if (!is_numeric($_POST['appid']) || $_POST['appid'] == 0 || empty($_POST['action']))
    die('Invalid appid');
$id = $_POST['appid'];
$email = $_SESSION['email'];
$a = mysql_fetch_array(mysql_query("SELECT * FROM shellinfo WHERE `email`='$email' AND `id`='$id'"));
if (empty($a))
    die("shell do not exist");

switch ($_POST['action']) {
    case 'start':
    case 'reboot':
    case 'stop':
        fastcgi_finish_request();
        control_vz($a['nodeno'], $_POST['action'], $id);
    default:
        die('Unsupported action');
}
