<?php
include_once "header.php";
include_once "db.php";
include_once "nodes.inc.php";

$appid = $_SESSION['appid'];
if (empty($appid))
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

$cmd = "sudo vzlist -o ";
if (isset($_GET['detail']))
    $cmd .= implode(',', $detail_attrs);
else
    $cmd .= implode(',', $attrs);
?>
<div id="wrapper">
<div id="regtitle">
        	<h1>Freeshell System Status</h1>
        	<div id="progbar">
            </div>
<style>
ul.table, ul.help, p.note {
	font-size: 16px !important;
	font-family: "Segoe UI","Helvetica Neue", Helvetica, Ubuntu;
	margin-left: 100px !important;
	margin-top: 20px;
	line-height: 20px;
}
h2 {
	font-size: 20px !important;
	font-family: "Segoe UI","Helvetica Neue", Helvetica, Ubuntu;
	font-weight: 100;
	margin-left: 100px !important;
}
ul.table span.h {
    display: inline-block;
    width: 200px;
    margin-right: 30px;
}
ul.table span.r {
    display: inline-block;
}
ul.table span.c {
    display: inline-block;
    font-family: "Courier New", "Monospace";
    font-size: 14px;
}
ul.table li {
    margin: 5px 0 5px 0;
}
ul.help li {
    list-style: square;
    margin: 10px 0 10px 0;
    width: 700px;
}
.buttons span {
    margin-right: 30px;
}
p.note {
    width: 700px;
}
</style>
<?php
foreach ($nodes2ip as $nodeno => $ip) {
    echo "<h2>Node #$nodeno: $ip</h2>\n";
    echo "<pre>";
    echo run_in_node($nodeno, $cmd);
    echo "</pre>\n";
}
?>
</div>
</div>
<script src="js/jquery.js" type="text/javascript"></script>
