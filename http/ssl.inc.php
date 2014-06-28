<?php
include_once "nodes.inc.php";

function install_ssl_key($domain) {
    list($errno, $output) = local_exec("/usr/local/bin/install-ssl-key $domain");
    return $errno;
}

function remove_ssl_key($domain) {
    list($errno, $output) = local_exec("/usr/local/bin/remove-ssl-key $domain");
    return $errno;
}
