<?php
session_start();
session_write_close();
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
    $attrs = $detail_attrs;
$cmd .= implode(',', $attrs);
?>
<h1>Freeshell System Status</h1>
<?php
echo '<table id="sort"><thead>';
echo "<th>Node#</th>";
foreach ($attrs as $attr) {
    echo "<th>$attr</th>";
}
echo "<th>freespace</th>";
echo "</tr>\n";
echo "</thead><tbody>";

foreach ($nodes2ip as $nodeno => $ip) {
    list($errno, $output) = run_in_node($nodeno, $cmd);
    if ($errno != 0) {
        continue;
    }
    $lines = explode("\n", $output);
    foreach ($lines as $line) {
        $cols = preg_split('/\s+/', trim($line));
        if (count($cols) != count($attrs))
            continue;
        if (!is_numeric($cols[0]))
            continue;
        $freespace = get_col($cols, 'diskspace.s') - get_col($cols, 'diskspace');
        $cols[] = $freespace;

        echo "<tr><td>$nodeno</td>";
        foreach ($cols as $col) {
            echo "<td>$col</td>";
        }
        echo "</tr>\n";
    }
}

echo "</tbody></table>";

function get_col($arr, $name){
    global $attrs;
    foreach ($attrs as $idx => $attr){
        if ($attr == $name)
            return $arr[$idx];
    }
    return null;
}
?>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.13.3/jquery.tablesorter.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.13.3/css/theme.default.css" />
<script>
$(document).ready(function(){ 
    $("#sort").tablesorter(); 
}); 
</script>
