<?php
include_once "db.inc.php";

class nsupdate {
    var $commands = array();

    private function do_commit($views) {
        if (count($this->commands) == 0)
            return true;
        $tmpfile = tempnam('/tmp', 'freeshell_ns_');
        $fp = fopen($tmpfile, "w");
        if (!$fp)
            return false;
        fwrite($fp, "server dns.lug.ustc.edu.cn\n");
        foreach ($this->commands as $cmd) {
            fwrite($fp, "update $cmd\n");
        }
        fwrite($fp, "send\n");
        fclose($fp);
        chmod($tmpfile, 0644);
        foreach ($views as $view) {
            exec("nsupdate -k /etc/freeshell/$view-update-key.key $tmpfile", $output, $errno);
            if ($errno != 0) {
                report_sys_admin("DNS update failed:\n===== $view ERRNO[$errno] =====\n".implode("\n", $output));
                return false;
            }
        }
        unlink($tmpfile);
        return true;
    }
    function replace($fqdn, $record, $content) {
        $ttl = 600;
        $this->commands[] = "delete $fqdn $record";
        $this->commands[] = "add $fqdn $ttl $record $content";
    }
    function delete($fqdn, $record) {
        $this->commands[] = "delete $fqdn $record";
    }
    function commit() {
        return $this->do_commit(array('default', 'chinanet', 'cmcc'));
    }
    function commit_default_view() {
        return $this->do_commit(array('default'));
    }
}
