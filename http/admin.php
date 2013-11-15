<?php
include_once "header.php";
include_once "db.php";
include_once "nodes.inc.php";
include_once "admin.inc.php";

$appid = $_SESSION['appid'];
if (empty($appid))
    include "logout.php";
$rs = mysql_query("SELECT * FROM shellinfo WHERE `id`='$appid'");
$info = mysql_fetch_array($rs);
if (empty($info))
    include "logout.php";

$info['ip'] = get_node_ip(1);
$info['realip'] = get_node_ip($info['nodeno']);
$info['ipv6'] = get_node_ipv6($appid);
$info['sshport'] = appid2sshport($appid);
$info['httpport'] = appid2httpport($appid);
$info['domain'] = 's'.$info['nodeno'].'.freeshell.ustc.edu.cn';

$num_onthisnode = mysql_result(mysql_query("SELECT COUNT(*) FROM shellinfo WHERE `nodeno`='".$info['nodeno']."'"),0);
$node = get_node_info($info['nodeno'], $appid);
if (isset($node['mystatus']))
    $node['mystatus'] = human_readable_status($node['mystatus']);
else
    $node['mystatus'] = 'Internal error: cannot connect to worker node';
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
.smaller {
    font-size: 16px !important;
}
</style>

<p>Welcome, <?=$_SESSION['email']?> <a href="logout.php">Logout</a> <a href="change-web-pass.php">Change Web Password</a></p>
<div id="progbar"></div>
<?php
if ($_SESSION['isadmin']) { ?>
<p>Freeshell admin area:
<a href="admin/status.php" target="_blank">View status</a>
<a href="admin/change-quota.php" target="_blank">Change Disk Quota</a>
<a href="admin/find-user.php" target="_blank">Search User</a>
<a href="admin/reset-web-pass.php" target="_blank">Reset Web Password</a>
</p>
<div id="progbar"></div>
<?php
}
?>

<?php
$rs = mysql_query("SELECT COUNT(*) FROM shellinfo WHERE `email`='".addslashes($_SESSION['email'])."'");
$num_shells = mysql_result($rs,0);
if ($num_shells >= 2) {
?>
<p>You have <?=$num_shells?> freeshells. <a href="select-shell.php">Click here to switch</a>.</p>
<div id="progbar"></div>
<?php
}
?>

<ul class="table">
  <li><span class="h">Shell ID:</span><strong><?=$appid?></strong>
  <li><span class="h">Status:</span><strong><?=$node['mystatus']?></strong>
  <li><span class="h">IPv6 address:</span><strong><?=$info['ipv6']?></strong>
  <li><span class="h">Hostname:</span><strong><span id="shell-hostname"><?=$info['hostname']?></span></strong> <button id="hostname-change-btn" onclick="changeHostname()">Change</button>
  <?php
  $count = mysql_result(mysql_query("SELECT COUNT(*) FROM shellinfo WHERE `hostname`='".addslashes($info['hostname'])."'"), 0);
  if ($count > 1) {
    echo "<li><strong>Your Hostname is in conflict with another freeshell.</strong><br /><strong>DNS name may be unavailable. Please change another hostname, thanks.</strong>";
  }
  ?>
  <li><span class="h">DNS Name:</span><?=get_node_dns_name($info['hostname'])?> (IPv6 only)
  <li><span class="h">SSH command:</span><span class="c">ssh root@<?=$info['hostname']?>.6.freeshell.ustc.edu.cn</span> (IPv6 only)
  <li><span class="h">HTTP address:</span><span class="c">http://<?=$info['hostname']?>.6.freeshell.ustc.edu.cn</span> (IPv6 only)
</ul>

<p class="note">If IPv6 is not working (e.g. you are using VPN), try IPv4 (RESTRICTED TO USTC CAMPUS):</p>
<ul class="table">
  <li><span class="h">SSH port:</span><strong><?=$info['sshport']?></strong> (mapped to port 22 of your freeshell)
  <li><span class="h">SSH command:</span><span class="c">ssh -p <?=$info['sshport']?> root@<?=$info['domain']?></span>
  <li><span class="h">HTTP port:</span><strong><?=$info['httpport']?></strong> (mapped to port 80 of your freeshell)
  <li><span class="h">HTTP address:</span><span class="c">http://<?=$info['domain']?>:<?=$info['httpport']?>/</span>
</ul>

<div id="progbar"></div>
<h2>Manage your <?=$node['mystatus'] ?> freeshell</h2>
<p class="buttons">
  <span><button id="btn-manage-start" onclick="manage('start')">Start</button></span>
  <span><button id="btn-manage-stop" onclick="manage('stop')">Shutdown</button></span>
  <span><button id="btn-manage-reboot" onclick="manage('reboot')">Reboot</button></span>
  <span><button id="btn-manage-reset-root" onclick="manage('reset-root')">Reset Root Password</button></span>
</p>
<p class="buttons">
  <span><button id="btn-manage-reinstall-keephome" onclick="manage('reinstall-keephome')">Reinstall System (keep /home)</button></span>
  <span><button id="btn-manage-reinstall" onclick="manage('reinstall')">Reinstall System (NOT keep /home)</button></span>
</p>
</p>
<div id="progbar"></div>
<h2>HTTP Proxy</h2>
<p>
http(s)://<input id="http-proxy-subdomain" value="<?=$info['http_subdomain'] ?>" />.freeshell.ustc.edu.cn
<span><button id="btn-update-proxy" onclick="updateProxy()">Update Subdomain</button></span>
</p>
<p class="smaller">See <a href="faq.html" target="_blank">FAQ</a> for HTTP Proxy Policy and Help.</p>
<div id="progbar"></div>
<h2>Server status</h2>
<ul class="table">
  <li><span class="h">Node</span><strong>#<?=$info['nodeno']?></strong>
  <li><span class="h">Domain Name</span><strong><?=$info['domain']?></strong>
  <li><span class="h">Total shells</span><?=$num_onthisnode?>
<?php
unset($node['mystatus']);
foreach ($node as $key => $value) {
    if ($key[0] != '#') {
?>
  <li><span class="h"><?=$key?></span><span class="c"><?=$value?></span></li>
<?php
    }
}
?>
</ul>
<div id="progbar"></div>
<h2>Resource Limits</h2>
<ul class="table">
  <li><span class="h">Memory</span><?=$info['nodeno']==3?"12G":"16G"?>, unlimited
  <li><span class="h">CPU</span>8 cores * Xeon X5450, unlimited
  <li><span class="h">Disk</span><span class="r"><?=$info['diskspace_softlimit']?>. You can use up to <?=$info['diskspace_hardlimit']?> in a grace period of 24 hours.<br>Please delete unused files as soon as possible :)<br>If you need more disk space, email support@freeshell.ustc.edu.cn</span>
<?php
foreach ($node as $key => $value) {
    if ($key[0] == '#') {
?>
  <li><span class="h"><?=substr($key,1)?></span><span class="c"><?=$value?></span></li>
<?php
    }
}
?>
</ul>
<div id="progbar"></div>
<h2><a href="faq.html" target="_blank">Frequency Asked Questions</a></h2>
<div id="progbar"></div>
</div>
</div>
</div>
<script src="js/jquery.js" type="text/javascript"></script>
<script>
function ajaxSuccessFunc(msg){
    if (msg.length > 0)
        alert(msg);
    window.location.reload();
}
function manage(action) {
    if (!confirm("Do you really want to " + action + " your freeshell?"))
        return;
    $('#btn-manage-'+action).attr('disabled', true);
    $('#btn-manage-'+action).html('Processing...');
    $.ajax({
        url: 'manage.php',
        type: 'post',
        async: true,
        data: {appid: <?=$info['id']?>, action: action},
        success: ajaxSuccessFunc,
    });
}
function updateProxy() {
    var old_domain = "<?=$info['http_subdomain'] ?>";
    var new_domain = $('#http-proxy-subdomain').val();
    if (old_domain == new_domain) {
        alert('Domain Name Unchanged');
        return;
    }
    $('#btn-update-proxy').attr('disabled', true);
    $('#btn-update-proxy').html('Processing...');
    $.ajax({
        url: 'manage.php',
        type: 'post',
        async: true,
        data: {
            appid: <?=$info['id']?>,
            action: 'update-proxy',
            domain: new_domain
        },
        success: ajaxSuccessFunc,
    });
}
function changeHostname() {
    var dom = $('#shell-hostname');
    if (dom.hasClass('input')) {
        if (dom.find('input') != null && dom.find('input').val().length > 0) {
            $('#hostname-change-btn').attr('disabled', true);
            $('#hostname-change-btn').html('Processing...');
            $.ajax({
                url: 'manage.php',
                type: 'post',
                async: true,
                data: {
                    appid: <?=$info['id']?>,
                    action: 'update-hostname',
                    hostname: dom.find('input').val(),
                },
                success: ajaxSuccessFunc,
            });
        }
        return;
    }
    dom.html('<input type="text" value="' + dom.html() + '" />');
    dom.addClass('input');
    $('#hostname-change-btn').html('Save');
}
</script>

