<?php
// should be called in crontab

date_default_timezone_set('Asia/Chongqing');
include_once "../nodes.inc.php";

foreach ($nodes2ip as $nodeno => $ip) {
    $response = run_in_node($nodeno, "vzlist");
    if (!stristr($response, "CTID")) {
        mail_warning('croncheck@freeshell.ustc.edu.cn', $nodeno, "vzlist", $response);
    }
}
function mail_warning($email, $nodeno, $action, $detail = "") {
    $title = "Freeshell node $nodeno $action failed";
    $body = "This email is for freeshell system admin.\n\nCroncheck target $action failed for node $nodeno.\n\nDetail: $detail\n\nDate: ".date("Y-m-d H:i:s");
    mail($email, $title, $body);
}
?>