<?php
include_once "config.inc.php";
include_once "nodes.inc.php";
include_once "verify.inc.php";
include_once "proxy.inc.php";
include_once "ssl.inc.php";
include_once "admin.inc.php";
include_once "dns.inc.php";
include_once "db.php";

session_start();
session_write_close();
if (empty($_SESSION['email']))
    die('Not login');
if (!is_numeric($_POST['appid']) || $_POST['appid'] == 0 || empty($_POST['action']))
    die('Invalid appid');
$id = intval($_POST['appid']);
$email = $_SESSION['email'];

if ($_SESSION['isadmin']) {
    $a = mysql_fetch_array(checked_mysql_query("SELECT * FROM shellinfo WHERE `id`='$id'"));
} else {
    $a = mysql_fetch_array(checked_mysql_query("SELECT * FROM shellinfo WHERE `email`='$email' AND `id`='$id'"));
}
if (empty($a))
    die("Freeshell does not exist");
if (!$_SESSION['isadmin']) {
    if (!$a['isactive'])
        die('Not Activated');
    if ($a['blocked'])
        die('Freeshell blocked');
}

log_operation($id, $_POST['action'], $_POST);
switch ($_POST['action']) {
    case 'start':
    case 'reboot':
    case 'force-reboot':
    case 'stop':
    case 'force-stop':
        lock_shell_or_die($id);
        $status = control_vz($a['nodeno'], $_POST['action'], $id);
        unlock_shell($id, $status);
        if ($email == $a['email'])
            send_manage_notify_email($status, $email, $id, strtoupper($_POST['action']));
        else if ($status)
            send_admin_manage_email($email, $a['email'], $id, $_POST['action']);
        break;
    case 'reset-root':
        reset_passwd($email, $a['nodeno'], $id);
        break;
    case 'reinstall':
        $distribution = $_POST['distribution'];
        if ($distribution == 'gallery') {
            if (!is_in_gallery($_POST['gallery-id'])) {
                alert('No such item in gallery.');
            }
            $gallery_id = intval($_POST['gallery-id']);
            $distribution = mysql_result(checked_mysql_query("SELECT distribution FROM shellinfo WHERE id='$gallery_id'"), 0);
        }
        if (check_distribution($distribution))
            die("Invalid Distribution!");

        $keep_dir_str = trim($_POST['keep_directories']);
        $keep_dirs = explode(',', trim($_POST['keep_directories']));
        foreach ($keep_dirs as $i => $d) {
            $keep_dirs[$i] = trim($d);
        }
        $keep_dir_str = implode(',', $keep_dirs);
        switch (check_keep_dirs($keep_dir_str)) {
            case 0:
                break;
            case 1:
                die("No empty directory could be keeped");
            case 2:
                die("Root directory cannot be keeped");
            case 3:
                die("Directory must start with /");
            case 4:
                die("Directory must not contain ..");
            case 5:
                die("Special chars not supported in directory name. Directory name could only contain letters, digits, '_', '-' and '/'");
            case 6:
                die("Directory path too long");
            case 7:
                die("Too many directories to keep");
        }

        $distribution_param = ($_POST['distribution'] == 'gallery') ? "gallery-$gallery_id" : $distribution;
        echo need_email_verification("Reinstalling System",
            "Distribution: ".$_POST['distribution']."\n".
            "WARNING: THIS OPERATION WILL ERASE ALL DATA ON YOUR FREESHELL".
                ($keep_dir_str ? " EXCEPT THE FOLLOWING DIRECTORIES:\n".implode("\n", $keep_dirs) : "."),
            "reinstall-freeshell.php",
            "$distribution_param\n$keep_dir_str",
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
        $status = update_proxy(trim($_POST['domain']), trim($_POST['40x_page']), trim($_POST['50x_page']),
            ($_POST['force_ssl'] ? 1 : 0),
            ($_POST['websocket_en'] ? 1 : 0));
        send_manage_notify_email($status, $email, $id, "Updated HTTP Proxy");
        break;
    case 'add-cname':
        $status = add_cname(trim($_POST['domain']));
        send_manage_notify_email($status, $email, $id, "Added HTTP Proxy Domain ".$_POST['domain']);
        break;
    case 'remove-cname':
        $status = remove_cname(trim($_POST['domain']));
        send_manage_notify_email($status, $email, $id, "Removed HTTP Proxy Domain ".$_POST['domain']);
        break;
    case 'update-hostname':
        $status = update_hostname(trim($_POST['hostname']));
        send_manage_notify_email($status, $email, $id, "Updated Hostname",
            "Due to DNS cache, the new hostname may take several minutes to be usable.");
        break;
    case 'copy':
        check_nodeno_and_fail($_POST['nodeno']);
        check_hostname_and_fail($_POST['hostname']);

        lock_shell_or_die($id);
        list($appid, $nodeno) = create_freeshell_in_db($_POST['hostname'], $a['password'], $email, $_POST['nodeno'], $a['distribution'], DEFAULT_STORAGE_BASE);
        if (!$appid || !try_lock_shell($appid)) {
            unlock_shell($id, false);
            die("Failed to create new entry in database.");
        }
        if (!copy_freeshell_config($id, $appid)) {
            unlock_shell($id, false);
            die("Failed to copy freeshell config in database.");
        }
        goto_background();
        $status = copy_vz($a['nodeno'], $id, $nodeno, $appid, $_POST['hostname'], $a['distribution'], DEFAULT_STORAGE_BASE);
        unlock_shell($id, $status);
        unlock_shell($appid, $status);

        send_manage_notify_email($status, $email, $id, "been Copied to node ".$nodeno,
            "The new freeshell ID is $appid, new hostname is ".get_shell_v6_dns_name($_POST['hostname']));
        break;
    case 'rescue':
        $_POST['nodeno'] = $a['nodeno'];
        // fall through
    case 'move':
        check_nodeno_and_fail($_POST['nodeno']);

        lock_shell_or_die($id);
        $new_storage = DEFAULT_STORAGE_BASE;
        $appid = move_freeshell_in_db($id, $_POST['nodeno'], $new_storage);
        if (!$appid) {
            unlock_shell($id, false);
            die("Failed to move freeshell in database.");
        }
        goto_background();
        if (!($status = update_proxy_conf())) {
            report_sys_admin("failed to update proxy conf");
            goto move_finish;
        }
        if (!($status = copy_vz($a['nodeno'], $id, $_POST['nodeno'], $appid, $a['hostname'], $a['distribution'], $new_storage))) {
            report_sys_admin("failed to copy vz");
            goto move_finish;
        }
        destroy_vz($a['nodeno'], $id);
move_finish:
        unlock_shell($id, $status);
        unlock_shell($appid, $status);
        
        send_manage_notify_email($status, $email, $id, "been Moved to node ".$_POST['nodeno'],
            "The new freeshell ID is $appid and the original ID $id is deprecated. You can still access your freeshell via ".get_shell_v6_dns_name($a['hostname']).", but due to DNS cache, you may have to wait several minutes for DNS to refresh. Please note that the IPv6 address and IPv4 SSH/HTTP port have changed.");
        break;
    case 'add-endpoint':
        if (!is_valid_public_endpoint($_POST['public_endpoint']))
            die('Invalid public endpoint');
        if (!is_valid_private_endpoint($_POST['private_endpoint']))
            die('Invalid private endpoint');
        if (!is_valid_transport_protocol($_POST['protocol']))
            die('Invalid protocol');

        lock_shell_or_die($id);
        $status = db_add_endpoint($id, $_POST['public_endpoint'], $_POST['private_endpoint'], $_POST['protocol']);
        switch ($status) {
            case 0:
                break;
            case 1:
                unlock_shell($id, false);
                die('You have created too many endpoints. If you have special needs, please contact us.');
            case 2:
                unlock_shell($id, false);
                die('The public endpoint has been taken, please use another one');
            default:
                unlock_shell($id, false);
                die('Unknown error');
        }
        $status = update_port_forwarding();
        unlock_shell($id, $status);
        send_manage_notify_email($status, $email, $id, "Added ".strtoupper($_POST['protocol'])." Public Endpoint ".$_POST['public_endpoint']." => Private Port ".$_POST['private_endpoint']);
        break;
    case 'remove-endpoint':
        if (!is_valid_public_endpoint($_POST['public_endpoint']))
            die('Invalid public endpoint');
        if (!is_valid_private_endpoint($_POST['private_endpoint']))
            die('Invalid private endpoint');
        if (!is_valid_transport_protocol($_POST['protocol']))
            die('Invalid protocol');

        lock_shell_or_die($id);
        if (!db_remove_endpoint($id, $_POST['public_endpoint'], $_POST['private_endpoint'], $_POST['protocol'])) {
            unlock_shell($id, false);
            die('The endpoints does not exist');
        }
        $status = update_port_forwarding();
        unlock_shell($id, $status);
        send_manage_notify_email($status, $email, $id, "Removed ".strtoupper($_POST['protocol'])." Public Endpoint ".$_POST['public_endpoint']." => Private Port ".$_POST['private_endpoint']);
        break;
    case 'update-public':
        $is_public = ($_POST['is_public'] == 1);
        if ($a['is_public'] == $is_public && $_POST['public_name'] == $a['public_name'] && $_POST['public_description'] == $a['public_description'])
            die('Nothing changed');
        $status = db_update_public($id, $is_public, $_POST['public_name'], $_POST['public_description']);
        if (!$status)
            die('Operation failed. Please do not use name and description that are too long.');
        if ($a['is_public'] != $is_public)
            send_manage_notify_email($status, $email, $id, ($_POST['is_public'] == 1 ? 'Added to' : 'Removed from')." public gallery");
        break;
    case 'block':
        if (!$_SESSION['isadmin'])
            die('Unsupported action');
        checked_mysql_query("UPDATE shellinfo SET blocked=1 WHERE id='$id'");
        if (mysql_affected_rows() != 1) {
            die('Query failed, maybe the shell is already blocked');
        } else {
            send_admin_manage_email($email, $a['email'], $id, $_POST['action'], "Please let us know if you want to retrieve your data or have any complaint.");
        }
        break;
    case 'unblock':
        if (!$_SESSION['isadmin'])
            die('Unsupported action');
        checked_mysql_query("UPDATE shellinfo SET blocked=0 WHERE id='$id'");
        if (mysql_affected_rows() != 1) {
            die('Query failed, maybe the shell is not blocked');
        } else {
            send_admin_manage_email($email, $a['email'], $id, $_POST['action'], "Please start your freeshell via Web control panel. If you have any problem, please contact us.");
        }
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
    lock_shell_or_die($id);
    $status = control_vz($nodeno, "reset-root", "$id $new_passwd");
    unlock_shell($id, $status);
    if ($status) {
        send_reset_root_email($email, $id, $new_passwd);
        echo 'New root password has been sent to your email. If not found, please check the Spam box.';
    }
    else {
        echo 'Failed to reset root password. Please try again later.';
    }
}

function update_proxy($domain, $page_40x, $page_50x, $force_ssl, $websocket_en) {
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

    $page_40x = addslashes(sanitize_url($page_40x));
    $page_50x = addslashes(sanitize_url($page_50x));
    $force_ssl = $force_ssl ? 1 : 0;
    $websocket_en = $websocket_en ? 1 : 0;

    checked_mysql_query("UPDATE shellinfo SET `http_subdomain`='$domain', `40x_page`='$page_40x', `50x_page`='$page_50x', `force_ssl`='$force_ssl', `websocket_en`='$websocket_en' WHERE `id`='$id'");
    return update_proxy_conf();
}

function add_cname($cname) {
    global $id;

    $flag = cname_check($id, $cname);
    switch ($flag) {
    case 0:
        break;
    case 1:
        die('ERROR: your own domain is not a valid domain name. Please use lower-case letters.');
    case 2:
        die('ERROR: your own domain is not allowed to contain freeshell.ustc.edu.cn or any other domains owned by USTC LUG');
    case 3:
        die('Sorry, your own domain has been taken by one freeshell. Please contact us if you are the owner of the domain.');
    default:
        die('Unknown Error '.$flag);
    }

    $cname = addslashes($cname);
    checked_mysql_query("INSERT INTO cname (id,domain) VALUES ('$id','$cname')");
    if (mysql_affected_rows() != 1)
        die('Database Error');
    return update_proxy_conf();
}

function remove_cname($cname) {
    global $id;

    $cname = addslashes($cname);
    $is_ssl = mysql_result(checked_mysql_query("SELECT is_ssl FROM cname WHERE id=$id AND domain='$cname'"), 0);
    if ($is_ssl) {
        remove_ssl_key($cname);
    }
    checked_mysql_query("DELETE FROM cname WHERE id=$id AND domain='$cname'");
    if (mysql_affected_rows() != 1)
        die('The domain you are removing does not exist');
    return update_proxy_conf();
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

    lock_shell_or_die($id);
    checked_mysql_query("UPDATE shellinfo SET `hostname`='$hostname' WHERE `id`='$id'");
    $status = set_vz($a['nodeno'], $id, 'hostname', $hostname);
    unlock_shell($id, $status);
    if (!$status)
        return false;

    if (strlen($a['hostname']) > 0)
        delete_dns($a['hostname'], $id);
    return update_dns($hostname, $id);
}
