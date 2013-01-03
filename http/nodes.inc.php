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

function nodes_num() {
    global $nodes2ip;
    return count($nodes2ip);
}

function get_node_ip($nodeno) {
    global $nodes2ip;
    return $nodes2ip[$nodeno];
}

function call_monitor($nodeno, $action, $param) {
    $cmd = "sudo -u scgyshell-monitor ssh -t scgyshell-client@scgyshell-$nodeno sudo /home/boj/scripts/scgyshell.sh $action $param";
    $output = array();
    exec($cmd, $output);
    return implode("\n", $output);
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

function get_node_info($nodeno, $id) {
    $str = call_monitor($nodeno, 'node-info', $id);
    $items = explode("-----FREESHELL-----", $str);
    $num = count($items);
    $info = array();
    for ($i=0;$i<$num;$i+=2) {
        $k = htmlspecialchars(trim($items[$i]));
        // do not htmlspecialchars because value is in <pre>
        $v = trim($items[$i+1]);
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

