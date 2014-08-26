<?php
function __nsupdate($commands) {
    $tmpfile = tempnam('/tmp', 'freeshell_ns_');
    $fp = fopen($tmpfile, "w");
    if (!$fp)
        return false;
    fwrite($fp, "server dns.lug.ustc.edu.cn\n");
    foreach ($commands as $cmd) {
        fwrite($fp, "update $cmd\n");
    }
    fwrite($fp, "send\n");
    fclose($fp);
    chmod($tmpfile, 0644);
    exec("nsupdate -k /etc/freeshell/default-update-key.key $tmpfile", $output1, $errno1);
    exec("nsupdate -k /etc/freeshell/chinanet-update-key.key $tmpfile", $output2, $errno2);
    exec("nsupdate -k /etc/freeshell/cmcc-update-key.key $tmpfile", $output3, $errno3);
    unlink($tmpfile);
    return ($errno1 == 0 && $errno2 == 0 && $errno3 == 0);
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
        if (count($this->commands) == 0)
            return true;
        return __nsupdate($this->commands);
    }
}
