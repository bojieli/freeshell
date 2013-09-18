<?php
function update_proxy_conf() {
    $tmpfile = tempnam('/tmp', 'ub_');
    $fp = fopen($tmpfile, "w");
    fwrite($fp, nginx_conf_gen_for_proxy());
    fclose($fp);
    chmod($tmpfile, 644);
    ssh_update_proxy_conf($tmpfile);
    unlink($tmpfile);
}

function nginx_conf_gen_for_proxy() {
    $conf = '';
    $rs = mysql_query("SELECT http_subdomain, nodeno, id FROM shellinfo");
    while ($row = mysql_fetch_array($rs)) {
        $domain = $row['http_subdomain'];
        if (empty($domain) || 0 != subdomain_check_norepeat($domain))
            continue;
        $nodeno = $row['nodeno'];
        $httpport = appid2httpport($row['id']);
        $conf .= conf_for_one_subdomain($domain, $nodeno, $httpport);
    }
    return $conf;
}

function conf_for_one_subdomain($domain, $nodeno, $httpport) {
    return "
server {
        listen 80;
        listen [::]:80;
        server_name $domain.freeshell.ustc.edu.cn;
        location / {
                proxy_pass http://s$nodeno.freeshell.ustc.edu.cn:$httpport;
        }
}";
}

function runas_monitor($cmd) {
    return exec("sudo -u scgyshell-monitor $cmd");
}

function ssh_update_proxy_conf($tmpfile) {
    $host = '202.38.70.159';
    $userhost = "scgyshell-client@$host";
    runas_monitor("scp $tmpfile $userhost:~/freeshell-proxy");
    runas_monitor("ssh $userhost chmod 644 freeshell-proxy");
    runas_monitor("ssh $userhost sudo /etc/init.d/nginx reload");
}

function subdomain_check_norepeat($domain) {
    if (strlen($domain) < 3 || strlen($domain) > 20)
        return 1;
    if (!preg_match('/[a-z0-9]+/', $domain))
        return 2;
    if (in_array($domain, array('master', 'proxy', 'lug', 'freeshell')))
        return 3;
    return 0;
}

function subdomain_check($domain) {
    $flag = subdomain_check_norepeat($domain);
    if ($flag)
        return $flag;
    $rs = mysql_fetch_array(mysql_query("SELECT * FROM shellinfo WHERE `http_subdomain`='$domain'"));
    if ($rs)
        return 4;
    return 0;
}
