<?php
function single_quote_escape($cmd) {
    // substitute ' with '\'': close the single quote, add an escaped quote, reopen the single quote
    return str_replace("'", "'\\''", $cmd);
}

function ipv6_to_nibble($ip) {
    $addr = inet_pton($ip);
    $hex = unpack('H*', $addr);
    return implode('.', array_reverse(str_split($hex[1]))) . '.ip6.arpa';
}
