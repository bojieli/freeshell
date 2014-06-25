<?php
include_once "header.php";
include_once "db.php";
include_once "nodes.inc.php";
include_once "admin.inc.php";
include_once "distributions.inc.php";

if (!isset($_SESSION['appid']) || empty($_SESSION['appid']) || !is_numeric($_SESSION['appid']))
    include "logout.php";
$appid = $_SESSION['appid'];
$rs = checked_mysql_query("SELECT * FROM shellinfo WHERE `id`='$appid'");
$info = mysql_fetch_array($rs);
if (empty($info))
    include "logout.php";
session_write_close();

$info['ip'] = get_node_ipv4(1);
$info['realip'] = get_node_ipv4($info['nodeno']);
$info['ipv6'] = get_shell_ipv6($appid);
$info['sshport'] = appid2sshport($appid);
$info['global_sshport'] = appid2gsshport($appid);
$info['httpport'] = appid2httpport($appid);
$info['domain'] = 's'.$info['nodeno'].'.freeshell.ustc.edu.cn';

$num_onthisnode = mysql_result(checked_mysql_query("SELECT COUNT(*) FROM shellinfo WHERE `nodeno`='".$info['nodeno']."'"),0);
$node = get_node_info($info['nodeno'], $appid);
if (isset($node['locked']) && $node['locked'])
    $node['mystatus'] = 'Locked';
else if (isset($node['mystatus']))
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
table, ul.table, ul.help, p.note {
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

<p>Welcome, <?=$_SESSION['email']?> &nbsp;&nbsp;
<a href="logout.php">Logout</a>&nbsp;&nbsp;
<a href="change-web-pass.php">Change Web Password</a>&nbsp;&nbsp;
<a href="faq.html">Help</a>
</p>

<div id="progbar"></div>
<?php
if (isset($_SESSION['isadmin']) && $_SESSION['isadmin']) { ?>
<p>Freeshell admin area:<br />
<a href="admin/status.php" target="_blank">View status</a>
<a href="admin/change-quota.php" target="_blank">Change Disk Quota</a>
<a href="admin/find-user.php" target="_blank">Search User</a>
<a href="admin/reset-web-pass.php" target="_blank">Reset Web Password</a>
<a href="admin/sendmail.php" target="_blank">Send Email to Users</a>
</p>
<div id="progbar"></div>
<?php
}
?>

<?php
$rs = checked_mysql_query("SELECT COUNT(*) FROM shellinfo WHERE `email`='".addslashes($_SESSION['email'])."'");
$num_shells = mysql_result($rs,0);
if ($num_shells >= 2) {
?>
<p>You have <?=$num_shells?> freeshells. <a href="select-shell.php">Click here to switch</a>.</p>
<div id="progbar"></div>
<?php
}
?>

<?php
if ($info['locked']) {
    ?>
    <p><strong>Your freeshell has a pending action started <?=time() - $info['lock_time']?> seconds ago, please wait.<br />Refresh at will.</strong>
    <?php if (time() - $info['lock_time'] > 3600) { ?>
        <p>The action takes significantly longer than usual. The technical team will be awared of this issue. Feel free to contact us if you have any additional details to provide.</p>
    <?php } ?>
    <div id="progbar"></div>
    <?php
}
$free_diskspace = $node['#Disk Space Limit (KB)'] - $node['Used Disk Space (KB)'];
if ($free_diskspace < 0) {
    ?>
    <p><strong>Your freeshell has exceeded disk space limit by <?=-$free_diskspace?> KB.</strong></p>
    <p><strong>Please remove some files, otherwise the freeshell might be blocked after grace period.</strong></p>
    <div id="progbar"></div>
    <?php
}
else if ($free_diskspace < 500 * 1024) {
    ?>
    <p>Notice: Your freeshell has only <?=$free_diskspace?> KB free space, please save disk space.</p>
    <div id="progbar"></div>
    <?php
}
?>
<ul class="table">
  <li><span class="h">Shell ID:</span><strong><?=$appid?></strong>
  <li><span class="h">Status:</span><strong><?=$node['mystatus']?></strong>
  <?php if ($node['mystatus'] == "Not exist") echo 'Oops! There seems to be some problem. <button onclick="manage(\'reinstall\')">Reinstall Freeshell</button>'; ?>
  <?php if ($node['mystatus'] == "Locked") echo 'Oops! There seems to be some problem. Please register a new freeshell, sorry for the inconvenience. If you need to migrate the data, please contact us.'; ?>
  <li><span class="h">IPv6 address:</span><strong><?=$info['ipv6']?></strong>
  <li><span class="h">Hostname:</span><strong><span id="shell-hostname"><?=$info['hostname']?></span></strong> <button id="hostname-change-btn" onclick="changeHostname()">Change</button>
  <?php
  $count = mysql_result(checked_mysql_query("SELECT COUNT(*) FROM shellinfo WHERE `hostname`='".addslashes($info['hostname'])."'"), 0);
  if ($count > 1) {
    echo "<li><strong>Your Hostname is in conflict with another freeshell.</strong><br /><strong>DNS name may be unavailable. Please change another hostname, thanks.</strong>";
  }
  ?>
  <li><span class="h">DNS Name:</span><?=get_shell_v6_dns_name($info['hostname'])?> (IPv6 only)
  <li><span class="h">SSH command:</span><span class="c">ssh root@<?=$info['hostname']?>.6.freeshell.ustc.edu.cn</span> (IPv6 only)
  <li><span class="h">HTTP address:</span><span class="c">http://<?=$info['hostname']?>.6.freeshell.ustc.edu.cn</span> (IPv6 only)
</ul>

<p class="note">If you do not have IPv6 access, try IPv4:</p>
<ul class="table">
  <li><span class="h">SSH port:</span><strong><?=$info['global_sshport']?></strong> (mapped to port 22 of your freeshell, global access)
  <li><span class="h">SSH command:</span><span class="c">ssh -p <?=$info['global_sshport']?> root@ssh.freeshell.ustc.edu.cn</span>
  <li><span class="h">HTTP port:</span><strong><?=$info['httpport']?></strong> (mapped to port 80 of your freeshell, USTC campus only)
  <li><span class="h">HTTP address:</span><span class="c">http://<?=$info['domain']?>:<?=$info['httpport']?>/</span>
</ul>

<div id="progbar"></div>
<h2>Manage your <?=$node['mystatus'] ?> freeshell</h2>
<p class="buttons">
  <span><button id="btn-manage-start" onclick="manage('start')">Start</button></span>
  <span><button id="btn-manage-stop" onclick="manage('stop')">Shutdown</button></span>
  <span><button id="btn-manage-force-stop" onclick="manage('force-stop')">Force Shutdown</button></span>
  <span><button id="btn-manage-reboot" onclick="manage('reboot')">Reboot</button></span>
  <span><button id="btn-manage-force-reboot" onclick="manage('force-reboot')">Force Reboot</button></span>
  <span><button id="btn-manage-destroy" onclick="manage('destroy')">Destroy</button></span>
</p>

<div id="progbar"></div>
<h2>Recovery</h2>
<p class="buttons">
  <span><button id="btn-manage-reset-root" onclick="manage('reset-root')">Reset Root Password</button></span>
  <span><button id="btn-manage-rescue" onclick="manage('rescue')">Rescue</button></span>
  <span class="smaller">(If your freeshell does not respond to <strong>Force Reboot</strong>, try Rescue. <a href="faq.html#29" target="_blank">Details</a>)</span>
</p>

<div id="progbar"></div>
<h2>Reinstall System</h2>
<ul class="table">
<li>
  <span class="h">Distribution</span>
  <span class="c">
    <?php if (!is_supported_distribution($info['distribution'])) {
        echo 'Your current distribution is no longer supported! <a href="faq.html#nonsupported-dist" target="_blank">Details</a><br />';
    } ?>
    <select id="reinstall-distribution">
    <?php echo distribution_option_html($info['distribution']); ?>
    </select>
  </span>
</li>
<li>
  <span class="h">Keep Directories</span>
  <span class="c"><input type="text" id="reinstall-keep-directories" value="/home,/root" /> (separate by ',')</span>
</li>
<li>
  <span class="h"><button id="btn-manage-reinstall" onclick="manage('reinstall')">Reinstall System</button></span>
</li>
</ul>

<div id="progbar"></div>
<h2>Copy / Move</h2>
<p class="note">Use Copy to duplicate freeshells, use Move to switch hardware node. Both operations require complete data copy and take a long time. Your current node is #<?=$info['nodeno'] ?>.</p>
<p>
<span>
<select id="copy-nodeno">
<option>-- Select Target Node --</option>
<option value="1">1</option>
<option value="2">2</option>
<option value="3">3</option>
<option value="4">4</option>
<option value="5">5</option>
<option value="6">6</option>
<option value="7">7</option>
</select>
</span>
<span><input type="text" id="copy-hostname" value="Input New HostName" /></span>
<span><button id="btn-manage-copy" onclick="manage('copy')">Copy</button></span>
</p>
<p>
<span>
<select id="move-nodeno">
<option>-- Select Target Node --</option>
<option value="1">1</option>
<option value="2">2</option>
<option value="3">3</option>
<option value="4">4</option>
<option value="5">5</option>
<option value="6">6</option>
<option value="7">7</option>
</select>
</span>
<span><button id="btn-manage-move" onclick="manage('move')">Move</button></span>
</p>

<div id="progbar"></div>
<h2>HTTP Proxy</h2>
<p class="note">
http(s)://<input id="http-proxy-subdomain" value="<?=$info['http_subdomain'] ?>" />.freeshell.ustc.edu.cn
<br />
40x error page URL: <input id="40x-page" value="<?=$info['40x_page'] ?>" />
<br />
50x error page URL: <input id="50x-page" value="<?=$info['50x_page'] ?>" />
<br />
<button id="btn-update-proxy" onclick="updateProxy()">Update</button>
</p>
<p class="note">Your own domain: (please CNAME to proxy.freeshell.ustc.edu.cn, subdomains supported)</p>
<table>
<?php
$rs = checked_mysql_query("SELECT domain FROM cname WHERE id='$appid'");
while ($row = mysql_fetch_array($rs)){
    ?>
    <tr>
    <td><?=$row['domain']?></td>
    <td><button class="btn-remove-cname" onclick="removeCname('<?=$row['domain']?>')">Remove</button></td>
    </tr>
    <?php
}
?>
<tr>
<td><input id="http-cname" /></td>
<td><button id="btn-add-cname" onclick="addCname()">Add</button></td>
</tr>
</table>

<div id="progbar"></div>
<h2>Public Endpoint (Port Forwarding)</h2>
<table>
<tr><th>Protocol</th><th>Public Port</th><th>Private Port</th><th></th></tr>
<tr><td>TCP</td><td><?=$info['global_sshport']?></td><td>22</td><td>For SSH, cannot remove</td></tr>
<?php
$rs = checked_mysql_query("SELECT * FROM endpoint WHERE id='$appid'");
while ($row = mysql_fetch_array($rs)) {
    echo "<tr>";
    echo "<td>".strtoupper($row['protocol'])."</td>";
    echo "<td>".$row['public_endpoint']."</td>";
    echo "<td>".$row['private_endpoint']."</td>";
    echo '<td><button class="btn-remove-endpoint" onclick="removeEndpoint('.$row['public_endpoint'].','.$row['private_endpoint'].',\''.$row['protocol'].'\')">Remove</button></td>';
    echo "</tr>\n";
}
?>
<tr>
<td><select id="endpoint-protocol"><option value="tcp" selected="selected">TCP</option><option value="udp">UDP</option></select></td>
<td><input type="text" id="public-endpoint" /></td>
<td><input type="text" id="private-endpoint" /></td>
<td><button id="btn-add-endpoint" onclick="addEndpoint()">Add</button></td>
</tr>
</table>
<p class="note">Please use ssh.freeshell.ustc.edu.cn to access, the IP address is subject to change.<br />Public port must be in range 40000-59999.</p>

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
<h2>Resource Limits & Configurations</h2>
<ul class="table">
  <li><span class="h">Distribution</span><strong><?=$info['distribution']?></strong>
  <li><span class="h">Private IP</span><strong><?=get_shell_ipv4($appid)?></strong> (Freeshell Internal)
  <li><span class="h">Private Hostname</span><strong><?=get_shell_v4_dns_name($info['hostname'])?></strong> (Freeshell Internal)
  <li><span class="h">Memory</span><strong><?=node_default_mem_limit($info['nodeno'])?></strong>
  <li><span class="h">CPU</span>8 cores * Xeon X5450, unlimited
  <li><span class="h">Disk</span><span class="r"><strong><?=$info['diskspace_softlimit']?></strong>. You can use up to <?=$info['diskspace_hardlimit']?> in a grace period of 24 hours.<br>Please delete unused files as soon as possible :)<br>If you need more disk space, email support@freeshell.ustc.edu.cn</span>
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
function ajaxErrorFunc() {
    alert('Failed to connect to server!');
}
function ajaxManage(data) {
    $.ajax({
        url: 'manage.php',
        type: 'post',
        async: true,
        data: data,
        success: ajaxSuccessFunc,
        error: ajaxErrorFunc,
    });
}
function manage(action) {
    if (!confirm("Do you really want to " + action + " your freeshell?"))
        return;
    $('#btn-manage-'+action).attr('disabled', true);
    $('#btn-manage-'+action).html('Processing...');
    var data = {appid: <?=$info['id']?>, action: action};
    if (action == "copy") {
        data.nodeno = $('#copy-nodeno').val();
        data.hostname = $('#copy-hostname').val();
    }
    else if (action == "move") {
        data.nodeno = $('#move-nodeno').val();
    }
    else if (action == "reinstall") {
        data.distribution = $('#reinstall-distribution').val();
        data.keep_directories = $('#reinstall-keep-directories').val();
    }
    ajaxManage(data);
}
function removeEndpoint(public_endpoint, private_endpoint, protocol) {
    $('.btn-remove-endpoint').attr('disabled', true);
    ajaxManage({
        appid: <?=$info['id']?>,
        action: 'remove-endpoint',
        public_endpoint: public_endpoint,
        private_endpoint: private_endpoint,
        protocol: protocol,
    });
}
function addEndpoint() {
    $('#btn-add-endpoint').attr('disabled', true);
    $('#btn-add-endpoint').html('Processing...');
    ajaxManage({
        appid: <?=$info['id']?>,
        action: 'add-endpoint',
        public_endpoint: $('#public-endpoint').val(),
        private_endpoint: $('#private-endpoint').val(),
        protocol: $('#endpoint-protocol').val(),
    });
}
function removeCname(domain) {
    $('.btn-remove-cname').attr('disabled', true);
    ajaxManage({
        appid: <?=$info['id']?>,
        action: 'remove-cname',
        domain: domain,
    });
}
function addCname() {
    if ($('#http-cname').val() == "") {
        alert('Please specify your own domain!');
        return;
    }
    $('#btn-add-cname').attr('disabled', true);
    $('#btn-add-cname').html('Processing...');
    ajaxManage({
        appid: <?=$info['id']?>,
        action: 'add-cname',
        domain: $('#http-cname').val(),
    });
}
function updateProxy() {
    var old_domain = "<?=$info['http_subdomain'] ?>";
    var new_domain = $('#http-proxy-subdomain').val();
    var old_cname = "<?=$info['http_cname'] ?>";
    var new_cname = $('#http-cname').val();
    var new_40x_page = $('#40x-page').val();
    var new_50x_page = $('#50x-page').val();
    $('#btn-update-proxy').attr('disabled', true);
    $('#btn-update-proxy').html('Processing...');
    $.ajax({
        url: 'manage.php',
        type: 'post',
        async: true,
        data: {
            appid: <?=$info['id']?>,
            action: 'update-proxy',
            domain: new_domain,
            cname: new_cname,
            '40x_page': new_40x_page,
            '50x_page': new_50x_page,
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

