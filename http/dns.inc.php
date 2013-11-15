<?php
function __nsupdate($commands) {
    $tmpfile = tempnam('/tmp', 'freeshell_ns_');
    $fp = fopen($tmpfile, "w");
    fwrite($fp, "server dns.lug.ustc.edu.cn\n");
    foreach ($commands as $cmd) {
        fwrite($fp, "update $cmd\n");
    }
    fwrite($fp, "send\n");
    fclose($fp);
    chmod($tmpfile, 0644);
    exec("nsupdate -k /etc/freeshell/default-update-key.key $tmpfile");
    unlink($tmpfile);
}
function nsupdate_add($fqdn, $record, $content) {
    __nsupdate(array("add $fqdn $record $content"));
}
function nsupdate_replace($fqdn, $record, $content) {
    $ttl = 600;
    __nsupdate(array("delete $fqdn $record", "add $fqdn $ttl $record $content"));
}
function nsupdate_delete($fqdn, $record) {
    __nsupdate(array("delete $fqdn $record"));
}
