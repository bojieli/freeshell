<?php
session_start();
include_once "../db.php";
include_once "../nodes.inc.php";

if (empty($_SESSION['isadmin']))
    exit();

$attrs = array(
    'ctid',
    'hostname',
    'status',
    'numproc',
    'physpages',
    'numtcpsock',
    'numfile',
    'diskspace',
    'diskspace.s',
    'diskspace.h',
    'diskinodes',
);
$detail_attrs = array(
    'ctid',
    'hostname',
    'status',
    'kmemsize',
    'lockedpages',
    'privvmpages',
    'shmpages',
    'numproc',
    'physpages',
    'numtcpsock',
    'numflock',
    'numpty',
    'numsiginfo',
    'tcpsndbuf',
    'tcprcvbuf',
    'othersockbuf',
    'dgramrcvbuf',
    'dcachesize',
    'numfile',
    'swappages',
    'diskspace',
    'diskspace.s',
    'diskspace.h',
    'diskinodes',
    'cpulimit',
    'cpuunits',
    'ioprio',
    'onboot',
    'bootorder',
);

$cmd = "vzlist -o ";
if (isset($_GET['detail']))
    $cmd .= implode(',', $detail_attrs);
else
    $cmd .= implode(',', $attrs);
?>
<h1>Freeshell System Status</h1>
<?php
foreach ($nodes2ip as $nodeno => $ip) {
    echo "<h2>Node #$nodeno: $ip</h2>\n";
    echo "<pre>";
    echo run_in_node($nodeno, $cmd);
    echo "</pre>\n";
}
?>
