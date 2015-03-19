<?php
include_once "admin.inc.php";
include_once "config.inc.php";
include_once "utils.inc.php";

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

function local_exec($cmd) {
    $start_time = microtime(true);
    exec($cmd, $output, $errno);
    $elapsed_time = microtime(true) - $start_time;
    $output = implode("\n", $output);
    if ($errno != 0) {
        report_sys_admin("local sudo failed with status $errno\nSTART TIME: ".date("Y-m-d H:i:s", (int)$start_time)."\nELAPSED TIME: $elapsed_time\nFULL COMMAND:\n$cmd\nOUTPUT:\n$output\n");
    }
    return array($errno, $output);
}

function local_sudo($cmd) {
    return local_exec("sudo $cmd");
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

function ssh_log_before($nodeno, $action, $cmd, $password_to_hide) {
    if ($password_to_hide) {
        $cmd = hide_password($cmd, $password_to_hide);
    }
    $sql = "INSERT INTO ssh_log SET `nodeno`='$nodeno', `action`='".addslashes($action)."', `cmd`='".addslashes($cmd)."', `log_time`='".time()."'";
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
        return array(-1, "Invalid nodeno");
    $cmd = "$action $param";
    $log_id = ssh_log_before($nodeno, $action, $cmd, $password_to_hide);
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

function delete_dns($hostname, $shellid) {
    include_once "dns.inc.php";
    $ns = new nsupdate();
    $ns->delete(ipv6_to_nibble(get_shell_ipv6($shellid)), 'PTR');
    $ns->commit_default_view();

    $ns = new nsupdate();
    $ns->delete(get_shell_v6_dns_name($hostname), 'AAAA');
    $ns->delete('*.'.get_shell_v6_dns_name($hostname), 'AAAA');
    $ns->delete(get_shell_v4_dns_name($hostname), 'A');
    $ns->delete('*.'.get_shell_v4_dns_name($hostname), 'A');
    return $ns->commit();
}

function __update_dns($ns, $hostname, $appid) {
    $ns->replace(get_shell_v6_dns_name($hostname), 'AAAA', get_shell_ipv6($appid));
    // wildcard domains are also supported
    $ns->replace('*.'.get_shell_v6_dns_name($hostname), 'AAAA', get_shell_ipv6($appid));
    $ns->replace(get_shell_v4_dns_name($hostname), 'A', get_shell_ipv4($appid));
    $ns->replace('*.'.get_shell_v4_dns_name($hostname), 'A', get_shell_ipv4($appid));
}

function __update_ptr_v6($ns, $hostname, $appid) {
    $ns->replace(ipv6_to_nibble(get_shell_ipv6($appid)), 'PTR', get_shell_v6_dns_name($hostname), 3600);
}

function update_dns($hostname, $appid) {
    include_once "dns.inc.php";
    $ns = new nsupdate();
    __update_dns($ns, $hostname, $appid);
    $ns_ptr = new nsupdate();
    __update_ptr_v6($ns_ptr, $hostname, $appid);
    return $ns->commit() && $ns_ptr->commit_default_view();
}

function create_vz($nodeno, $id, $hostname, $password, $mem_limit, $diskspace_softlimit, $diskspace_hardlimit, $distribution, $storage) {
    if (!update_dns($hostname, $id))
        return false;
    return call_monitor($nodeno, "create-vz", "$id $hostname $password $mem_limit $diskspace_softlimit $diskspace_hardlimit $distribution $storage", $password);
}

function copy_vz_without_activate($old_node, $old_id, $new_node, $new_id, $hostname, $new_storage) {
    if (!call_monitor($old_node, "copy-vz", "$old_id $new_node $new_id $new_storage"))
        return false;
    if (!set_vz($new_node, $new_id, 'hostname', $hostname))
        return false;
    if (!update_dns($hostname, $new_id))
        return false;
    return true;
}

function copy_vz($old_node, $old_id, $new_node, $new_id, $hostname, $distribution, $new_storage) {
    if (!copy_vz_without_activate($old_node, $old_id, $new_node, $new_id, $hostname, $new_storage))
        return false;
    return activate_vz($new_node, $new_id, $distribution);
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

function update_port_forwarding() {
    include_once 'port-forwarding.inc.php';
    $fwd = new PortForwarding();
    $rs = checked_mysql_query("SELECT id, nodeno FROM shellinfo WHERE isactive=1");
    while ($row = mysql_fetch_array($rs)) {
        $fwd->add_ssh($row['id'], $row['nodeno']);
    }
    $rs = checked_mysql_query("SELECT shellinfo.id, nodeno, public_endpoint, private_endpoint, protocol FROM endpoint, shellinfo WHERE endpoint.id = shellinfo.id AND shellinfo.isactive=1");
    while ($row = mysql_fetch_array($rs)) {
        $fwd->add($row['public_endpoint'], get_shell_ipv4($row['id']), $row['private_endpoint'], $row['protocol'], get_node_ipv4($row['nodeno']));
    }
    return $fwd->commit();
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
    if (!update_port_forwarding())
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

function is_local_storage($a) {
    return (strpos($a, "/home") === 0);
}

function is_same_storage($a, $b) {
    return ($a == $b && is_local_storage($a));
}
