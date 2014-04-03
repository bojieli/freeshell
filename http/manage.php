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
    die("Freeshell does not exist");

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
    case 'destroy':
        echo need_email_verification("Destroy Freeshell",
            "WARNING: THIS OPERATION WILL ERASE ALL DATA ON YOUR FREESHELL.",
            "destroy-freeshell.php",
            "",
            $email, $id);
        break;
    case 'update-proxy':
        update_proxy(trim($_POST['domain']), trim($_POST['cname']), trim($_POST['40x_page']), trim($_POST['50x_page']));
        send_manage_notify_email($email, $id, "Updated HTTP Proxy");
        break;
    case 'update-hostname':
        update_hostname(trim($_POST['hostname']));
        send_manage_notify_email($email, $id, "Updated Hostname",
            "Due to DNS cache, the new hostname may take several minutes to be usable.");
        break;
    case 'copy':
        check_nodeno_and_fail($_POST['nodeno']);
        check_hostname_and_fail($_POST['hostname']);
        list($appid, $nodeno) = create_freeshell_in_db($_POST['hostname'], $a['password'], $email, $_POST['nodeno'], $a['distribution']);
        if (!$appid)
            die("Failed to create new entry in database.");
        copy_freeshell_config($id, $appid);
        goto_background();
        copy_vz($a['nodeno'], $id, $nodeno, $appid, $_POST['hostname'], $a['distribution']);
        send_manage_notify_email($email, $id, "been Copied to node ".$nodeno,
            "The new freeshell ID is $appid, new hostname is ".get_node_dns_name($_POST['hostname']));
        break;
    case 'rescue':
        $_POST['nodeno'] = $a['nodeno'];
        // fall through
    case 'move':
        check_nodeno_and_fail($_POST['nodeno']);
        $appid = move_freeshell_in_db($id, $_POST['nodeno']);
        if (!$appid)
            die("Failed to move freeshell in database.");
        goto_background();
        update_proxy_conf();
        move_endpoints($a['nodeno'], $id, $_POST['nodeno'], $appid);
        move_vz($a['nodeno'], $id, $_POST['nodeno'], $appid, $a['hostname'], $a['distribution']);
        send_manage_notify_email($email, $id, "been Moved to node ".$_POST['nodeno'],
            "The new freeshell ID is $appid and the original ID $id is deprecated. You can still access your freeshell via ".get_node_dns_name($a['hostname']).", but due to DNS cache, you may have to wait several minutes for DNS to refresh. Please note that the IPv6 address and IPv4 SSH/HTTP port have changed.");
        break;
    case 'add-endpoint':
        if (!is_valid_public_endpoint($_POST['public_endpoint']))
            die('Invalid public endpoint');
        if (!is_valid_private_endpoint($_POST['private_endpoint']))
            die('Invalid private endpoint'); 
        $status = db_add_endpoint($id, $_POST['public_endpoint'], $_POST['private_endpoint']);
        switch ($status) {
            case 0:
                break;
            case 1:
                die('You have created too many endpoints.');
            case 2:
                die('The public endpoint has been taken, please use another one');
            default:
                die('Unknown error');
        }
        add_endpoint($id, $a['nodeno'], $_POST['public_endpoint'], $_POST['private_endpoint']);
        send_manage_notify_email($email, $id, "Added Public Endpoint ".$_POST['public_endpoint']." => Private Port ".$_POST['private_endpoint']);
        break;
    case 'remove-endpoint':
        if (!is_valid_public_endpoint($_POST['public_endpoint']))
            die('Invalid public endpoint');
        if (!is_valid_private_endpoint($_POST['private_endpoint']))
            die('Invalid private endpoint'); 
        if (!db_remove_endpoint($id, $_POST['public_endpoint'], $_POST['private_endpoint']))
            die('The endpoints does not exist');
        remove_endpoint($id, $a['nodeno'], $_POST['public_endpoint'], $_POST['private_endpoint']);
        send_manage_notify_email($email, $id, "Removed Public Endpoint ".$_POST['public_endpoint']." => Private Port ".$_POST['private_endpoint']);
        break;
    default:
        die('Unsupported action');
}

function goto_background() {
    echo "Your request is being processed in background and you will receive an email upon completion.";
    fastcgi_finish_request();
}

function check_nodeno_and_fail($nodeno) {
    if (!is_valid_nodeno($nodeno))
        die('Please select target node!');
}

function reset_passwd($email, $nodeno, $id) {
    $new_passwd = random_string(12);
    control_vz($nodeno, "reset-root", "$id $new_passwd");
    send_reset_root_email($email, $id, $new_passwd);
    echo 'New root password has been sent to your email. If not found, please check the Spam box.';
}

function update_proxy($domain, $cname, $page_40x, $page_50x) {
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

    $page_40x = addslashes(sanitize_url($page_40x));
    $page_50x = addslashes(sanitize_url($page_50x));

    mysql_query("UPDATE shellinfo SET `http_subdomain`='$domain', `http_cname`='$cname', `40x_page`='$page_40x', `50x_page`='$page_50x' WHERE `id`='$id'");
    update_proxy_conf();
}

function check_hostname_and_fail($hostname) {
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
}

function update_hostname($hostname) {
    global $id;
    global $a;

    if (!is_numeric($id) || !is_numeric($a['nodeno']))
        die('Sanity check failed');

    check_hostname_and_fail($hostname);

    mysql_query("UPDATE shellinfo SET `hostname`='$hostname' WHERE `id`='$id'");
    set_vz($a['nodeno'], $id, 'hostname', $hostname);

    if (strlen($a['hostname']) > 0)
        delete_dns($a['hostname']);
    update_dns($hostname, $id);
}

function move_endpoints($old_node, $old_id, $new_node, $new_id) {
    $rs = mysql_query("SELECT * FROM endpoint WHERE `id`='$old_id'");
    while ($row = mysql_fetch_array($rs)) {
        remove_endpoint($old_id, $old_node, $row['public_endpoint'], $row['private_endpoint']);
        add_endpoint($new_id, $new_node, $row['public_endpoint'], $row['private_endpoint']);
    }
    mysql_query("UPDATE endpoint SET `id`='$new_id' WHERE `id`='$old_id'");
}
