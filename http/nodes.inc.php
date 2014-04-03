<?php
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

function get_node_ip($nodeno) {
    global $nodes2ip;
    return $nodes2ip[$nodeno];
}

function get_node_ipv6($nodeno) {
    $prefix = "2001:da8:d800:71::";
    if ($nodeno < 10000)
        return $prefix.($nodeno % 10000);
    else
        return $prefix.($nodeno / 10000).':'.($nodeno % 10000);
}

function get_node_dns_name($hostname) {
    return "$hostname.6.freeshell.ustc.edu.cn";
}

function run_in_node($nodeno, $cmd) {
    global $errno;
    $cmd = str_replace("'", "\\'", $cmd);
    $cmd = str_replace("\"", "\\\"", $cmd);
    // force fork terminal
    global $SSH_TIMEOUT;
    $local_cmd = "/bin/sh -c 'echo \"$cmd\" | /usr/bin/sudo -u scgyshell-monitor /usr/bin/ssh -4 -o ConnectTimeout=$SSH_TIMEOUT -t -t scgyshell-client@s$nodeno.freeshell.ustc.edu.cn'";
    $output = array();
    exec($local_cmd, $output, $errno);
    return implode("\n", $output);
}

function call_monitor($nodeno, $action, $param) {
    if (!is_numeric($nodeno))
        return;
    $cmd = "$action $param";
    $output = run_in_node($nodeno, $cmd);
    mysql_query("INSERT INTO ssh_log SET `nodeno`='$nodeno', `action`='".addslashes($action)."', `cmd`='".addslashes($cmd)."', `output`='".addslashes($output)."', `log_time`='".time()."'");
    return $output;
}

function destroy_vz($nodeno, $id, $keephome = false) {
    call_monitor($nodeno, "stop", "$id --fast");
    if ($keephome)
        return call_monitor($nodeno, "destroy", "$id keephome");
    else
        return call_monitor($nodeno, "destroy", $id);
}

function delete_dns($hostname) {
    include_once "dns.inc.php";
    nsupdate_delete(get_node_dns_name($hostname), 'AAAA');
    nsupdate_delete('*.'.get_node_dns_name($hostname), 'AAAA');
}

function update_dns($hostname, $appid) {
    include_once "dns.inc.php";
    nsupdate_replace(get_node_dns_name($hostname), 'AAAA', get_node_ipv6($appid));
    // wildcard domains are also supported
    nsupdate_replace('*.'.get_node_dns_name($hostname), 'AAAA', get_node_ipv6($appid));
}

function create_vz($nodeno, $id, $hostname, $password, $diskspace_softlimit, $diskspace_hardlimit, $distribution) {
    update_dns($hostname, $id);
    return call_monitor($nodeno, "create-vz", "$id $hostname $password $diskspace_softlimit $diskspace_hardlimit $distribution");
}

function copy_vz($old_node, $old_id, $new_node, $new_id, $hostname, $distribution) {
    update_dns($hostname, $new_id);
    $ret = call_monitor($old_node, "copy-vz", "$old_id $new_node $new_id");
    set_vz($new_node, $new_id, 'hostname', $hostname);
    activate_vz($new_node, $new_id, $distribution);
    return $ret;
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
    call_monitor($nodeno, "activate-vz", "$id ".get_node_ip($nodeno)." $distribution renew");
	if ($nodeno != $master_node)
		call_monitor($master_node, "nat-entry-node", "$id ".get_node_ip($master_node)." ".get_node_ip($nodeno)." renew");
}

function add_node_port_forwarding($nodeno, $public_port, $shellid, $private_port) {
    call_monitor($nodeno, "port-forward", "add $public_port $shellid $private_port");
}

function remove_node_port_forwarding($nodeno, $public_port, $shellid, $private_port) {
    call_monitor($nodeno, "port-forward", "remove $public_port $shellid $private_port");
}

function add_local_port_forwarding($local_port, $remote_ip, $remote_port) {
    if ($local_port < 1024)
        die('Request tainted');
    exec("sudo /usr/local/bin/port-forward add $local_port $remote_ip $remote_port");
}

function remove_local_port_forwarding($local_port, $remote_ip, $remote_port) {
    exec("sudo /usr/local/bin/port-forward remove $local_port $remote_ip $remote_port");
}

function add_ssh_port_forwarding($id, $nodeno) {
    add_local_port_forwarding(appid2gsshport($id), get_node_ip($nodeno), appid2sshport($id));
}

function is_valid_public_endpoint($port) {
    return (is_numeric($port) && $port >= 40000 && $port < 50000);
}

function is_valid_private_endpoint($port) {
    return (is_numeric($port) && $port != 22 && $port != 80 && $port > 0 && $port < 65536);
}

function add_endpoint($id, $nodeno, $public_port, $private_port) {
    if (!is_valid_public_endpoint($public_port) || !is_valid_private_endpoint($private_port))
        return false;
    add_node_port_forwarding($nodeno, $public_port, $id, $private_port);
    add_local_port_forwarding($public_port, get_node_ip($nodeno), $public_port);
    return true;
}

function remove_endpoint($id, $nodeno, $public_port, $private_port) {
    if (!is_valid_public_endpoint($public_port) || !is_valid_private_endpoint($private_port))
        return false;
    remove_node_port_forwarding($nodeno, $public_port, $id, $private_port);
    remove_local_port_forwarding($public_port, get_node_ip($nodeno), $public_port);
    return true;
}

function remove_all_endpoints($nodeno, $id) {
    $rs = mysql_query("SELECT * FROM endpoint WHERE `id`='$id'");
    while ($row = mysql_fetch_array($rs)) {
        remove_endpoint($id, $nodeno, $row['public_port'], $row['private_port']);
    }
}

function activate_vz($nodeno, $id, $distribution) {
    global $master_node;
    mysql_query("UPDATE shellinfo SET isactive=1 WHERE id=$id");
    call_monitor($nodeno, "activate-vz", "$id ".get_node_ip($nodeno)." $distribution");
	if ($nodeno != $master_node)
		call_monitor($master_node, "nat-entry-node", "$id ".get_node_ip($master_node)." ".get_node_ip($nodeno));
    add_ssh_port_forwarding($id, $nodeno);
}

function control_vz($nodeno, $action, $id) {
    return call_monitor($nodeno, $action, $id);
}

function set_vz($nodeno, $id, $option, $value) {
    return call_monitor($nodeno, "setvz", "$id $option $value");
}

function get_node_info($nodeno, $id) {
    $str = call_monitor($nodeno, 'node-info', $id);
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
