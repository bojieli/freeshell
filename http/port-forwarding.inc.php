<?php
include_once 'nodes.inc.php';
include_once 'db.inc.php';

class PortForwarding {
    var $dnat_rules = array();
    var $snat_rules = array();

    function add_ssh($id, $nodeno) {
        return $this->add(appid2gsshport($id), get_shell_ipv4($id), 22, 'tcp', get_node_ipv4($nodeno));
    }

    function add($local_port, $remote_ip, $remote_port, $protocol, $snat_src_ip) {
        $local_ips = array('202.141.160.99', '202.141.176.99');
        foreach ($local_ips as $local_ip) {
            $this->dnat_rules[] = "-d $local_ip/32 -p $protocol --dport $local_port -j DNAT --to-destination $remote_ip:$remote_port";
        }
        $this->snat_rules[] = "-s $snat_src_ip/32 -d $remote_ip/32 -p $protocol --dport $remote_port -j MASQUERADE";
        return true;
    }

    private function gen_new_rules() {
        $rules = '';
        foreach ($this->dnat_rules as $rule)
            $rules .= "-A PREROUTING $rule\n";
        foreach ($this->snat_rules as $rule)
            $rules .= "-A POSTROUTING $rule\n";
        return $rules;
    }

    private function gen_all_rules($new_rules) {
        $static = file_get_contents('/etc/iptables/rules.v4');
        if (!$static)
            return false;
        $nat_start = strpos($static, "\n*nat\n");
        if (!$nat_start)
            return false;
        $static_after_nat = substr($static, $nat_start);
        $pos_commit = strpos($static_after_nat, "\nCOMMIT\n");
        if (!$pos_commit)
            return false;
        return substr($static, 0, $nat_start + $pos_commit) . "\n" . $new_rules . "\n" . substr($static_after_nat, $pos_commit);
    }

    function commit() {
        $all_rules = $this->gen_all_rules($this->gen_new_rules());
        if (!$all_rules)
            return false;
        $write = popen('sudo /sbin/iptables-restore', 'w');
        if (!$write)
            return false;
        fwrite($write, $all_rules);
        $status = pclose($write);
        if ($status != 0) {
            report_sys_admin("update port forwarding: iptables-restore returned $status\n\nIPTABLES RULES TO INSTALL:\n$all_rules");
        }
        return ($status == 0);
    }
}
