<?php
include_once "header.php";
include_once "db.php";
include_once "nodes.inc.php";

$appid = $_SESSION['appid'];
if (empty($appid))
    exit();
$rs = mysql_query("SELECT * FROM shellinfo WHERE `id`='$appid'");
$info = mysql_fetch_array($rs);
$info['ip'] = get_node_ip(1);
$info['realip'] = get_node_ip($info['nodeno']);
$info['ipv6'] = get_node_ipv6($appid);
$info['sshport'] = 10000 + $appid;
$info['httpport'] = 20000 + $appid;
$info['domain'] = 's'.$info['nodeno'].'.freeshell.ustc.edu.cn';

$num_onthisnode = mysql_result(mysql_query("SELECT COUNT(*) FROM shellinfo WHERE `nodeno`='".$info['nodeno']."'"),0);
$node = get_node_info($info['nodeno'], $appid);
?>
<div id="wrapper">
<div id="regtitle">
        	<h1>Freeshell Control Panel</h1>
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
<p class="note">Note: IPv4 address of your freeshell is shared and can only be accessed within USTC campus.
<ul class="table">
  <li><span class="h">Shell ID:</span><strong><?=$appid?></strong>
  <li><span class="h">Status:</span><?=$node['mystatus']?> <?php unset($node['mystatus']); ?>
  <li><span class="h">SSH port:</span><strong><?=$info['sshport']?></strong> (mapped to port 22 of your shell)
  <li><span class="h">SSH command:</span><span class="c">ssh -p <?=$info['sshport']?> root@<?=$info['domain']?></span>
  <li><span class="h">HTTP port:</span><strong><?=$info['httpport']?></strong> (mapped to port 80 of your shell)
  <li><span class="h">HTTP address:</span><span class="c">http://<?=$info['domain']?>:<?=$info['httpport']?>/</span>
</ul>
<p class="note">Note: The following IPv6 access is experimental.
<ul class="table">
  <li><span class="h">IPv6 address:</span><strong><?=$info['ipv6']?></strong> (dedicate)
  <li><span class="h">SSH command:</span><span class="c">ssh root@<?=$info['ipv6']?></span>
</ul>

<div id="progbar"></div>
<h2>Manage your freeshell</h2>
<p class="buttons">
  <span><button onclick="manage('start')">Start</button></span>
  <span><button onclick="manage('stop')">Shutdown</button></span>
  <span><button onclick="manage('reboot')">Reboot</button></span>
</p>
<div id="progbar"></div>
<h2>Server status</h2>
<ul class="table">
  <li><span class="h">Node</span><strong>#<?=$info['nodeno']?></strong>
  <li><span class="h">Domain Name</span><strong><?=$info['domain']?></strong>
  <li><span class="h">Total shells</span><?=$num_onthisnode?>
<?php
foreach ($node as $key => $value) {
?>
  <li><span class="h"><?=$key?></span><span class="c"><?=$value?></span></li>
<?php
}
?>
</ul>
<div id="progbar"></div>
<h2>Resource Limits</h2>
<ul class="table">
  <li><span class="h">Memory</span><?=$info['nodeno']==3?"12G":"16G"?>, unlimited
  <li><span class="h">CPU</span>8 cores * Xeon X5450, unlimited
  <li><span class="h">Disk</span><span class="r">5GB. You can use up to 7GB in a grace period of 24 hours.<br>Please delete unused files as soon as possible :)</span>
  <li><span class="h">Process</span>Up to 200 processes, including kernel threads.
  <li><span class="h">TCP sockets</span>100
  <li><span class="h">UDP sockets</span>100
</ul>
<div id="progbar"></div>
<h2>For Linux newbies</h2>
<ul class="help">
  <li>If you are using Windows, please download PuTTY <a href="http://lug.ustc.edu.cn/~boj/web_dev/ref/putty.zip">Here</a>. The usage of PuTTY can be found on Google.
  <li>If you are still using ROOT account to login, please create your own user and add it to sudo group. It is also recommended to login with SSH key instead of username and password. If you don't know what this is all about, just ignore this recommendation.
  <li>This Linux box ships with few pre-installed software packages. It is a minimal debian 6.0 system with USTC mirror configured and "fail2ban", "sudo" installed. Many utils on normal Linux distributions have to be installed by yourself. Remember not to install too many packages, since our disk space is limited :)
  <li>DO NOT INSTALL X, GNOME OR KDE! This is a command-line based shell, not a graphical desktop environment. You are expected to learn some shell commands. Break shell, touch Linux!
  <li>Why my HTTP address cannot be accessed? You have not installed a Web server. If you are not in need of Web service, please save our shared resource by leaving it uninstalled.
  <li>Why my program terminated when I exit the shell? You should install "screen" and run long-live programs in screen.
</ul>
</div>
</div>
</div>
<script src="js/jquery.js" type="text/javascript"></script>
<script>
function manage(action) {
    if (!confirm("Do you really want to " + action + " your freeshell?"))
        return;
    $.ajax({
        url: 'manage.php',
        type: 'post',
        async: true,
        data: {appid: <?=$info['id']?>, action: action},
        success: function(msg){
            if (msg.length > 0)
                alert(msg);
            else
                window.location.reload();
        }
    });
}
</script>

