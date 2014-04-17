<?php
include_once "verify.inc.php";

function update_proxy_conf() {
    $localfile = "/etc/nginx/autogen-conf/freeshell.conf";
    $fp = fopen($localfile, "w");
    fwrite($fp, nginx_conf_gen_for_proxy());
    fclose($fp);
    chmod($localfile, 0644);
    local_update_proxy_conf();
    ssh_update_proxy_conf($localfile);
}

function nginx_conf_gen_for_proxy() {
    $conf = '## This file is automatically generated by freeshell. DO NOT MODIFY!';
    $rs = checked_mysql_query("SELECT http_subdomain, http_cname, 40x_page, 50x_page, nodeno, id FROM shellinfo");
    while ($row = mysql_fetch_array($rs)) {
        $domain = $row['http_subdomain'];
        if (0 != subdomain_check_norepeat($domain))
            continue; // security
        $cname = $row['http_cname'];
        if (0 != cname_check_norepeat($cname))
            continue; // security
        if ($domain == "" && $cname == "")
            continue; // do not generate entry if empty
        $nodeno = $row['nodeno'];
        $httpport = appid2httpport($row['id']);
        $conf .= conf_for_one_subdomain($cname, $domain, $nodeno, $httpport,
            sanitize_url($row['40x_page']),
            sanitize_url($row['50x_page']));
    }
    return $conf;
}

function conf_for_one_subdomain($cname_domain, $domain, $nodeno, $httpport, $page40x = null, $page50x = null) {
    return "
server {
        listen 80;
        listen 443;
        listen [::]:80;
        listen [::]:443;
        server_name ".
    ($cname_domain ? "*.$cname_domain $cname_domain" : "").
    " ".
    ($domain ? "$domain.freeshell.ustc.edu.cn *.$domain.freeshell.ustc.edu.cn" : "").
    ";\n".
        ($page40x ? "error_page 400 403 404 = $page40x;\n" : "").
        ($page50x ? "error_page 500 502 503 504 = $page50x;\n" : "")."
        access_log /var/log/nginx/freeshell-proxy/access.log logverbose;
        error_log  /var/log/nginx/freeshell-proxy/error.log;
        location / {
                proxy_pass       http://s$nodeno.4.freeshell.ustc.edu.cn:$httpport;
                proxy_set_header X-Real-IP  \$remote_addr;
                proxy_set_header Host       \$http_host;
                proxy_set_header X-Scheme   \$scheme;
        }
}
";
}

function runas_monitor($cmd) {
    return exec("sudo -u scgyshell-monitor $cmd");
}

function local_update_proxy_conf() {
    exec("sudo /usr/local/bin/reload-nginx");
}

function ssh_update_proxy_conf($tmpfile) {
    $host = '202.38.70.159';
    $userhost = "scgyshell-client@$host";
    runas_monitor("/usr/bin/scp $tmpfile $userhost:~/freeshell-proxy");
    runas_monitor("/usr/bin/ssh $userhost chmod 644 freeshell-proxy");
    runas_monitor("/usr/bin/ssh $userhost sudo /etc/init.d/nginx reload");
}

function subdomain_check_norepeat($domain) {
    if ($domain == "")
        return 0; // empty domain is allowed
    if (strlen($domain) < 3 || strlen($domain) > 20)
        return 1;
    if (!preg_match('/^[a-z0-9][a-z0-9-]+[a-z0-9]$/', $domain))
        return 2;
    if (in_array($domain, array('master', 'proxy', 'lug', 'freeshell', 'test', 'example')))
        return 3;
    return 0;
}

function subdomain_check($id, $domain) {
    if (!is_numeric($id))
        return -1;
    if ($domain == "")
        return 0; // empty domain is allowed
    $flag = subdomain_check_norepeat($domain);
    if ($flag)
        return $flag;
    $rs = mysql_fetch_array(checked_mysql_query("SELECT * FROM shellinfo WHERE `http_subdomain`='$domain' AND id != $id"));
    if ($rs)
        return 4;
    return 0;
}

function cname_check_norepeat($domain) {
    if ($domain == "")
        return 0; // empty domain is allowed
    if (!preg_match('/^([a-z0-9-]+\.)+[a-z]+$/', $domain))
        return 1;
    if (strstr($domain, 'freeshell.ustc.edu.cn') ||
        strstr($domain, 'blog.ustc.edu.cn') ||
        strstr($domain, 'lug.ustc.edu.cn') ||
        strstr($domain, 'mirrors.ustc.edu.cn') ||
        strstr($domain, 'pxe.ustc.edu.cn'))
        return 2;
    return 0;
}

function cname_check($id, $domain) {
    if (!is_numeric($id))
        return -1;
    if ($domain == "")
        return 0; // empty domain is allowed
    $flag = cname_check_norepeat($domain);
    if ($flag)
        return $flag;
    $rs = mysql_fetch_array(checked_mysql_query("SELECT * FROM shellinfo WHERE `http_cname`='$domain' AND id != $id"));
    if ($rs)
        return 3;
    return 0;
}

