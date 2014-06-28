<?php
// should be called in crontab

date_default_timezone_set('Asia/Chongqing');
include_once "../nodes.inc.php";
include_once "../db.php";

foreach ($nodes2ip as $nodeno => $ip) {
    list($errno, $response) = run_in_node($nodeno, "vzlist");
    if ($errno != 0 || !stristr($response, "CTID")) {
        mail_warning('croncheck@freeshell.ustc.edu.cn', $nodeno, "vzlist",
            "Return Code: $errno\n$response");
    }
}

// check freeshells that have been locked for more than 1 hour
$time_threshold = intval(time() - 3600);
$locked_shell_count = mysql_result(checked_mysql_query("SELECT COUNT(*) FROM shellinfo WHERE locked=1 AND lock_time<$time_threshold"), 0);
if ($locked_shell_count != 0) {
    $rs = mysql_query("SELECT id, email, lock_time FROM shellinfo WHERE locked=1 AND lock_time<$time_threshold");
    $msg = "Locked shell count: $locked_shell_count\n";
    while ($row = mysql_fetch_array($rs)) {
        $msg .= "id=".$row['id']." email=".$row['email']." locked since ".date("Y-m-d H:i:s", $row['lock_time'])."\n";
    }
    report_sys_admin($msg);
}

function mail_warning($email, $nodeno, $action, $detail = "") {
    $title = "Freeshell node $nodeno $action failed";
    $body = "This email is for freeshell system admin.\n\nCroncheck target $action failed for node $nodeno.\n\nDetail: $detail\n\nDate: ".date("Y-m-d H:i:s");
    mail($email, $title, $body);
}
?>
