<?php
define('DEFAULT_STORAGE_BASE', '/mnt/nfs1');

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
