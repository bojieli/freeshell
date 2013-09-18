<?php
include_once "nodes.inc.php";
include_once "proxy.inc.php";
include_once "admin.inc.php";
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
        control_vz($a['nodeno'], $_POST['action'], $id);
        send_manage_notify_email($email, $id, $_POST['action']);
        break;
    case 'reset-root':
        reset_passwd($email, $a['nodeno'], $id);
        break;
    case 'update-proxy':
        update_proxy($_POST['domain']);
        break;
    default:
        die('Unsupported action');
}

function reset_passwd($email, $nodeno, $id) {
    $new_passwd = random_string(12);
    control_vz($nodeno, "reset-root", "$id $new_passwd");
    send_reset_root_email($email, $id, $new_passwd);
    echo 'New root password has been sent to your email. If not found, please check the Spam box.';
}

function update_proxy($domain) {
    global $id;

    $flag = subdomain_check($domain);
    switch ($flag) {
    case 1:
        die('ERROR: subdomain length should be at least 3, at most 20');
    case 2:
        die('ERROR: subdomain should only contain lower-case letters and numbers');
    case 3:
        die('Sorry, this domain name is reserved.');
    case 4:
        die('Sorry, someone else has taken this subdomain.');
    }

    mysql_query("UPDATE shellinfo SET `http_subdomain`='$domain' WHERE `id`='$id'");
    update_proxy_conf();
}
