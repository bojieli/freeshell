<?php
include_once "admin.inc.php";

$nodes2ip = array(
 1 => "114.214.197.8",
 2 => "114.214.197.143",
 3 => "202.38.70.100",
 4 => "114.214.197.140",
 5 => "114.214.197.173",
 6 => "114.214.197.124",
 7 => "114.214.197.235",
);
$master_node = 1;

$SSH_TIMEOUT = 3; // in seconds

$errno = 0;

function nodes_num() {
    global $nodes2ip;
    return count($nodes2ip);
}

function is_valid_nodeno($nodeno) {
    if (!is_numeric($nodeno))
        return false;
    $int = (int)$nodeno;
    if ($int != $nodeno)
        return false;
    return $int > 0 && $int <= nodes_num();
}

function node_default_mem_limit($nodeno) {
    return '4G';
}

function get_node_ipv4($nodeno) {
    global $nodes2ip;
    return $nodes2ip[$nodeno];
}

function get_node_ipv6($nodeno) {
    return "2001:da8:d800:71::".$nodeno;
}

function get_shell_ipv6($id) {
    $prefix = "2001:da8:d800:71::";
    if ($id < 10000)
        return $prefix.($id % 10000);
    else
        return $prefix.intval($id / 10000).':'.($id % 10000);
}

function get_shell_ipv4($id) {
    return "10.10.".intval($id / 256).".".($id % 256);
}

function get_shell_v6_dns_name($hostname) {
    return "$hostname.6.freeshell.ustc.edu.cn";
}

function get_shell_v4_dns_name($hostname) {
    return "$hostname.4.freeshell.ustc.edu.cn";
}

function local_exec($cmd) {
    $start_time = microtime(true);
    exec($cmd, $output, $errno);
    $elapsed_time = microtime(true) - $start_time;
    $output = implode("\n", $output);
    if ($errno != 0) {
        report_sys_admin("local sudo failed with status $errno\nSTART TIME: $start_time\nELAPSED TIME: $elapsed_time\nFULL COMMAND:\n$cmd\nOUTPUT:\n$output\n");
    }
    return array($errno, $output);
}

function local_sudo($cmd) {
    return local_exec("sudo $cmd");
}

function single_quote_escape($cmd) {
    // substitute ' with '\'': close the single quote, add an escaped quote, reopen the single quote
    return str_replace("'", "'\\''", $cmd);
}

function run_in_node($nodeno, $cmd) {
    global $errno, $elapsed_time;
    global $SSH_TIMEOUT;
    // force fork terminal in ssh
    $sudo_cmd = "echo '".single_quote_escape($cmd)."' | /usr/bin/sudo -u scgyshell-monitor /usr/bin/ssh -4 -o ConnectTimeout=$SSH_TIMEOUT -t -t scgyshell-client@s$nodeno.freeshell.ustc.edu.cn";
    $local_cmd = "/bin/sh -c -- '".single_quote_escape($sudo_cmd)."'";
    $output = array();
    $start_time = microtime(true);
    exec($local_cmd, $output, $errno);
    $elapsed_time = microtime(true) - $start_time;
    $output = implode("\n", $output);
    if ($errno != 0) {
        report_sys_admin("Command in freeshell node returned non-zero status $errno\nSTART TIME: $start_time\nELAPSED TIME: $elapsed_time\nFULL COMMAND:\n$local_cmd\nOUTPUT:\n$output\n");
    }
    return array($errno, $output);
}

function hide_password($str, $password) {
    return str_replace($password, "********", $str);
}

function ssh_log_before($nodeno, $action, $cmd, $time, $password_to_hide) {
    if ($password_to_hide) {
        $cmd = hide_password($cmd, $password_to_hide);
    }
    $sql = "INSERT INTO ssh_log SET `nodeno`='$nodeno', `action`='".addslashes($action)."', `cmd`='".addslashes($cmd)."', `log_time`='$time'";
    checked_mysql_query($sql);
    if (mysql_affected_rows() != 1)
        report_sys_admin("Failed to save ssh log:\n$sql");
    return mysql_insert_id();
}

function ssh_log_after($id, $errno, $output, $password_to_hide) {
    if ($password_to_hide) {
        $output = hide_password($output, $password_to_hide);
    }
    global $elapsed_time;
    $sql = "UPDATE ssh_log SET `output`='".addslashes($output)."', `return_status`='$errno', `elapsed_time`='$elapsed_time' WHERE `id`='$id'";
    checked_mysql_query($sql);
    if (mysql_affected_rows() != 1) {
        report_sys_admin("Failed to save ssh log:\n$cmd");
        return false;
    }
    return true;
}

function __call_monitor($nodeno, $action, $param, $password_to_hide = "") {
    if (!is_numeric($nodeno))
        return;
    $cmd = "$action $param";
    $time = time();
    $log_id = ssh_log_before($nodeno, $action, $cmd, $time, $password_to_hide);
    list($errno, $output) = run_in_node($nodeno, $cmd);
    ssh_log_after($log_id, $errno, $output, $password_to_hide);
    return array($errno, $output);
}

function call_monitor($nodeno, $action, $param, $password_to_hide = "") {
    list($errno, $output) = __call_monitor($nodeno, $action, $param, $password_to_hide);
    return ($errno == 0);
}


function destroy_vz($nodeno, $id, $keep_dirs = "") {
    call_monitor($nodeno, "force-stop", "$id");
    return call_monitor($nodeno, "destroy", "$id $keep_dirs");
}

function delete_dns($hostname) {
    include_once "dns.inc.php";
    $ns = new nsupdate();
    $ns->delete(get_shell_v6_dns_name($hostname), 'AAAA');
    $ns->delete('*.'.get_shell_v6_dns_name($hostname), 'AAAA');
    $ns->delete(get_shell_v4_dns_name($hostname), 'A');
    $ns->delete('*.'.get_shell_v4_dns_name($hostname), 'A');
    return $ns->commit();
}

function update_dns($hostname, $appid) {
    include_once "dns.inc.php";
    $ns = new nsupdate();
    $ns->replace(get_shell_v6_dns_name($hostname), 'AAAA', get_shell_ipv6($appid));
    // wildcard domains are also supported
    $ns->replace('*.'.get_shell_v6_dns_name($hostname), 'AAAA', get_shell_ipv6($appid));
    $ns->replace(get_shell_v4_dns_name($hostname), 'A', get_shell_ipv4($appid));
    $ns->replace('*.'.get_shell_v4_dns_name($hostname), 'A', get_shell_ipv4($appid));
    return $ns->commit();
}

function create_vz($nodeno, $id, $hostname, $password, $mem_limit, $diskspace_softlimit, $diskspace_hardlimit, $distribution) {
    if (!update_dns($hostname, $id))
        return false;
    return call_monitor($nodeno, "create-vz", "$id $hostname $password $mem_limit $diskspace_softlimit $diskspace_hardlimit $distribution", $password);
}

function copy_vz_without_activate($old_node, $old_id, $new_node, $new_id, $hostname) {
    if (!update_dns($hostname, $new_id))
        return false;
    if (!call_monitor($old_node, "copy-vz", "$old_id $new_node $new_id"))
        return false;
    if (!set_vz($new_node, $new_id, 'hostname', $hostname))
        return false;
    return true;
}

function copy_vz($old_node, $old_id, $new_node, $new_id, $hostname, $distribution) {
    if (!copy_vz_without_activate($old_node, $old_id, $new_node, $new_id, $hostname))
        return false;
    return activate_vz($new_node, $new_id, $distribution);
}

function move_vz($old_node, $old_id, $new_node, $new_id, $hostname, $distribution) {
    /* do not use fast move because vzquota may fail when old VZ cannot be stopped
     * copying files is slow, but safer
     *
    if ($new_node == $old_node) {
        update_dns($hostname, $new_id);
        $ret = call_monitor($old_node, "move-vz", "$old_id $new_id");
        activate_vz($new_node, $new_id, $distribution);
    } else {
    */
        $ret = copy_vz($old_node, $old_id, $new_node, $new_id, $hostname, $distribution);
        destroy_vz($old_node, $old_id);
    /*
    }
    */
    return $ret;
}

function reactivate_vz($nodeno, $id, $distribution) {
    global $master_node;
    if (!call_monitor($nodeno, "activate-vz", "$id ".get_node_ipv4($nodeno)." $distribution renew"))
        return false;
	if ($nodeno != $master_node)
		if (!call_monitor($master_node, "nat-entry-node", "$id ".get_node_ipv4($master_node)." ".get_node_ipv4($nodeno)." renew"))
            return false;
    return true;
}

function add_node_port_forwarding($nodeno, $public_port, $shellid, $private_port, $protocol = 'tcp') {
    return call_monitor($nodeno, "port-forward", "add $public_port $shellid $private_port $protocol");
}

function remove_node_port_forwarding($nodeno, $public_port, $shellid, $private_port, $protocol = 'tcp') {
    return call_monitor($nodeno, "port-forward", "remove $public_port $shellid $private_port $protocol");
}

function add_local_port_forwarding($local_port, $remote_ip, $remote_port, $protocol = 'tcp') {
    if ($local_port < 1024)
        die('Request tainted');
    list($errno, $output) = local_sudo("/usr/local/bin/port-forward add $local_port $remote_ip $remote_port $protocol");
    return ($errno == 0);
}

function remove_local_port_forwarding($local_port, $remote_ip, $remote_port, $protocol = 'tcp') {
    list($errno, $output) = local_sudo("/usr/local/bin/port-forward remove $local_port $remote_ip $remote_port $protocol");
    return ($errno == 0);
}

function add_ssh_port_forwarding($id, $nodeno) {
    return add_local_port_forwarding(appid2gsshport($id), get_shell_ipv4($id), 22);
}

function add_tunnel_ip_route($id, $nodeno) {
    list($errno, $output) = local_sudo("/usr/local/bin/tunnel-ip-route $id $nodeno ".get_shell_ipv4($id));
    return ($errno == 0);
}

function is_valid_public_endpoint($port) {
    return (is_numeric($port) && intval($port) == $port && $port >= 40000 && $port < 60000);
}

function is_valid_private_endpoint($port) {
    return (is_numeric($port) && intval($port) == $port && $port > 0 && $port < 65536);
}

function is_valid_transport_protocol($protocol) {
    return ($protocol == 'tcp' || $protocol == 'udp');
}

function add_endpoint($id, $nodeno, $public_port, $private_port, $protocol) {
    if (!is_valid_public_endpoint($public_port) || !is_valid_private_endpoint($private_port))
        return false;
    if (!is_valid_transport_protocol($protocol))
        return false;
    return add_local_port_forwarding($public_port, get_shell_ipv4($id), $private_port, $protocol);
}

function remove_endpoint($id, $nodeno, $public_port, $private_port, $protocol) {
    if (!is_valid_public_endpoint($public_port) || !is_valid_private_endpoint($private_port))
        return false;
    if (!is_valid_transport_protocol($protocol))
        return false;
    return remove_local_port_forwarding($public_port, get_shell_ipv4($id), $private_port, $protocol);
}

function remove_all_endpoints($nodeno, $id) {
    $status = true;
    $rs = checked_mysql_query("SELECT * FROM endpoint WHERE `id`='$id'");
    while ($row = mysql_fetch_array($rs)) {
        if (!remove_endpoint($id, $nodeno, $row['public_port'], $row['private_port'], $row['protocol']))
            $status = false;
    }
    return $status;
}

function activate_vz($nodeno, $id, $distribution) {
    global $master_node;
    checked_mysql_query("UPDATE shellinfo SET isactive=1 WHERE id=$id");
    if (mysql_affected_rows() != 1)
        return false; // may be not exist or already activated
    if (!call_monitor($nodeno, "activate-vz", "$id ".get_node_ipv4($nodeno)." $distribution"))
        return false;
	if ($nodeno != $master_node)
		if (!call_monitor($master_node, "nat-entry-node", "$id ".get_node_ipv4($master_node)." ".get_node_ipv4($nodeno)))
            return false;
    if (!add_tunnel_ip_route($id, $nodeno))
        return false;
    if (!add_ssh_port_forwarding($id, $nodeno))
        return false;
    return true;
}

function control_vz($nodeno, $action, $id, $password_to_hide = "") {
    return call_monitor($nodeno, $action, $id, $password_to_hide);
}

function set_vz($nodeno, $id, $option, $value) {
    return call_monitor($nodeno, "setvz", "$id $option $value");
}

function get_node_info($nodeno, $id) {
    list($errno, $str) = __call_monitor($nodeno, 'node-info', $id);
    if ($errno)
        return null;
    $FS = "-----FREESHELL-FIELD-----";
    $LS = "-----FREESHELL-LINE-----";
    $lines = explode($LS, $str);
    $info = array();
    foreach ($lines as $line) {
        $fields = explode($FS, $line);
        if (count($fields) != 2)
            continue;
        $k = htmlspecialchars(trim($fields[0]));
        $v = nl2br(htmlspecialchars(trim($fields[1])));
        if ($k && $v)
            $info[$k] = $v;
    }
    return $info;
}

function random_string($length) {
    $str = '';
    for ($i=0;$i<$length;$i++) {
        $r = rand() % 62;
        if ($r < 26)
            $char = chr(ord('a')+$r);
        else if ($r < 52)
            $char = chr(ord('A')+$r-26);
        else
            $char = chr(ord('0')+$r-52);
        $str = $str.$char;
    }
    return $str;
}

function appid2sshport($appid) {
    return 10000 + $appid;
}
function appid2gsshport($appid) {
    return 30000 + $appid;
}
function appid2httpport($appid) {
    return 20000 + $appid;
}

function human_readable_status($str) {
    if (strstr($str, 'running'))
        return 'Running';
    if (strstr($str, 'exist'))
        return 'Down';
    return 'Not exist';
}
