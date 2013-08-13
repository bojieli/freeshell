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

function nodes_num() {
    global $nodes2ip;
    return count($nodes2ip);
}

function get_node_ip($nodeno) {
    global $nodes2ip;
    return $nodes2ip[$nodeno];
}

function get_node_ipv6($nodeno) {
    $prefix = "2001:da8:d800:701:8000::";
    if ($nodeno < 10000)
        return $prefix.($nodeno % 10000);
    else
        return $prefix.($nodeno / 10000).':'.($nodeno % 10000);
}

function run_in_node($nodeno, $cmd) {
    $cmd = str_replace("'", "\\'", $cmd);
    $cmd = str_replace("\"", "\\\"", $cmd);
    // force fork terminal
    $local_cmd = "/bin/sh -c 'echo \"$cmd\" | sudo -u scgyshell-monitor ssh -t -t scgyshell-client@scgyshell-$nodeno'";
    $output = array();
    exec($local_cmd, $output);
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

function create_vz($nodeno, $id, $hostname, $password) {
    return call_monitor($nodeno, "create-vz", "$id $hostname $password");
}

function activate_vz($nodeno, $id) {
    global $master_node;
    call_monitor($nodeno, "activate-vz", "$id ".get_node_ip($nodeno));
	if ($nodeno != $master_node)
		call_monitor($master_node, "nat-entry-node", "$id ".get_node_ip($master_node)." ".get_node_ip($nodeno));
}

function control_vz($nodeno, $action, $id) {
    return call_monitor($nodeno, $action, $id);
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

