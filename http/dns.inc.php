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
    exec("nsupdate -k /etc/freeshell/chinanet-update-key.key $tmpfile");
    unlink($tmpfile);
}

class nsupdate {
    var $commands = array();

    function replace($fqdn, $record, $content) {
        $ttl = 600;
        $this->commands[] = "delete $fqdn $record";
        $this->commands[] = "add $fqdn $ttl $record $content";
    }
    function delete($fqdn, $record) {
        $this->commands[] = "delete $fqdn $record";
    }
    function commit() {
        __nsupdate($this->commands);
    }
}
