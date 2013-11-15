<?php
include "../http/db.php";
include "../http/dns.inc.php";
include "../http/nodes.inc.php";
$rs = mysql_query("SELECT id, hostname FROM shellinfo");
while ($row = mysql_fetch_array($rs)) {
    nsupdate_replace(get_node_dns_name($row['hostname']), 'AAAA', get_node_ipv6($row['id']));
}
