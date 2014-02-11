<?php
include_once "nodes.inc.php";
include_once "verify.inc.php";
include_once "proxy.inc.php";
include_once "admin.inc.php";
include_once "dns.inc.php";
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
        send_manage_notify_email($email, $id,
            ($_POST['action'] == 'stop' ? 'stopped' : $_POST['action'].'ed'));
        break;
    case 'reset-root':
        reset_passwd($email, $a['nodeno'], $id);
        break;
    case 'reinstall-keephome':
        echo need_email_verification("Reinstalling System",
            "WARNING: THIS OPERATION WILL ERASE ALL DATA ON YOUR FREESHELL, EXCEPT HOME DIRECTORY.",
            "reinstall-freeshell.php",
            "keephome",
            $email, $id);
        break;
    case 'reinstall':
        echo need_email_verification("Reinstalling System",
            "WARNING: THIS OPERATION WILL ERASE ALL DATA ON YOUR FREESHELL.",
            "reinstall-freeshell.php",
            "",
            $email, $id);
        break;
    case 'update-proxy':
        update_proxy(trim($_POST['domain']), trim($_POST['cname']));
        send_manage_notify_email($email, $id, "Updated HTTP Proxy");
        break;
    case 'update-hostname':
        update_hostname(trim($_POST['hostname']));
        send_manage_notify_email($email, $id, "Updated Hostname",
            "Due to DNS caches, the new hostname may take up to 10 minutes to be usable.");
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

function update_proxy($domain, $cname) {
    global $id;

    $flag = subdomain_check($id, $domain);
    switch ($flag) {
    case 0:
        break;
    case 1:
        die('ERROR: subdomain length should be at least 3, at most 20');
    case 2:
        die('ERROR: subdomain should only contain lower-case letters, hyphen and numbers');
    case 3:
        die('Sorry, this domain name is reserved.');
    case 4:
        die('Sorry, another freeshell has taken this subdomain.');
    default:
        die('Unknown Error '.$flag);
    }

    $flag = cname_check($id, $cname);
    switch ($flag) {
    case 0:
        break;
    case 1:
        die('ERROR: CNAME is not a valid domain name. Please use lower-case letters.');
    case 2:
        die('ERROR: CNAME is not allowed to contain ustc.edu.cn');
    case 3:
        die('Sorry, this CNAME has been taken by another freeshell. Please contact us if you are the owner of the domain.');
    default:
        die('Unknown Error '.$flag);
    }

    mysql_query("UPDATE shellinfo SET `http_subdomain`='$domain', `http_cname`='$cname' WHERE `id`='$id'");
    update_proxy_conf();
}

function update_hostname($hostname) {
    global $id;
    global $a;

    if (!is_numeric($id) || !is_numeric($a['nodeno']))
        die('Sanity check failed');

    $flag = checkhost($hostname);
    switch ($flag) {
    case 0:
        break;
    case 1:
        die('ERROR: hostname should only contain lower-case letters, hyphen and numbers');
    case 2:
        die('Sorry, this hostname is already taken.');
    case 3:
    case 5:
        die('ERROR: subdomain length should be at least 3, at most 30');
    default:
        die('Unknown Error '.$flag);
    }

    mysql_query("UPDATE shellinfo SET `hostname`='$hostname' WHERE `id`='$id'");
    set_vz($a['nodeno'], $id, 'hostname', $hostname);

    nsupdate_replace(get_node_dns_name($hostname), 'AAAA', get_node_ipv6($id));
    if (strlen($a['hostname']) > 0)
        nsupdate_delete(get_node_dns_name($a['hostname']), 'AAAA');
}
