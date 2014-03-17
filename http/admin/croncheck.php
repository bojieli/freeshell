<?php
// should be called in crontab

date_default_timezone_set('Asia/Chongqing');
include_once "../nodes.inc.php";

foreach ($nodes2ip as $nodeno => $ip) {
    global $errno;
    $response = run_in_node($nodeno, "vzlist");
    if ($errno != 0 || !stristr($response, "CTID")) {
        mail_warning('croncheck@freeshell.ustc.edu.cn', $nodeno, "vzlist",
            "Return Code: $errno\n$response");
    }
}
function mail_warning($email, $nodeno, $action, $detail = "") {
    $title = "Freeshell node $nodeno $action failed";
    $body = "This email is for freeshell system admin.\n\nCroncheck target $action failed for node $nodeno.\n\nDetail: $detail\n\nDate: ".date("Y-m-d H:i:s");
    mail($email, $title, $body);
}
?>
