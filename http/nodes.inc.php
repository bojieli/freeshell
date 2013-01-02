<?php
$nodes2ip = array(
 1 => "114.214.197.8",
 2 => "114.214.197.143";
 3 => "202.38.70.100";
 4 => "114.214.197.140";
 5 => "114.214.197.173";
 6 => "114.214.197.124";
 7 => "114.214.197.235";
);

function nodes_num() {
    return count($nodes2ip);
}

function get_node_ip($nodeno) {
    return $nodes2ip[$i];
}

function call_monitor($nodeno, $action, $param, $sync = false) {
    $cmd = "sudo -u scgyshell-monitor ssh -t scgyshell-client@scgyshell-$nodeno sudo /home/boj/scripts/scgyshell.sh $action $param";
    if ($sync)
        return system($cmd);
    else
        return exec($cmd);
}

function create_vz($nodeno, $id, $hostname, $password) {
    return call_monitor($nodeno, "create-vz", "$id $hostname $password");
}

function activate_vz($nodeno, $id) {
    return call_monitor($nodeno, "activate-vz", "$id ". get_node_ip($nodeno));
}

function control_vz($nodeno, $action, $id) {
    return call_monitor($nodeno, $action, $id);
}

function control_vz_sync($nodeno, $action, $id) {
    return call_monitor($nodeno, $action, $id, true);
}

function random_string($length) {
    $str = '';
    for ($i=0;$i<$length;$i++) {
        $r = rand() % 62;
        if ($r < 26)
            $char = chr(ord('a')+$r);
        else if ($r < 52)
            $char = chr(ord('A')+$r);
        else
            $char = chr(ord('0')+$r);
        $str = $str.$char;
    }
    return $str;
}
