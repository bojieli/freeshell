<?php
session_start();
include_once "db.php";
include_once "nodes.inc.php";

$appid = $_SESSION['appid'];
if (empty($appid))
    exit();
$rs = mysql_query("SELECT * FROM shellinfo WHERE `id`='$appid'");
$info = mysql_fetch_array($rs);
$info['ip'] = get_node_ip($info['nodeno']);
$info['sshport'] = 10000 + $appid;
$info['httpport'] = 20000 + $appid;

$num_onthisnode = mysql_result(mysql_query("SELECT COUNT(*) FROM shellinfo WHERE `nodeno`='".$info['nodeno']."'"),0);
$node = get_node_info($info['nodeno'], $appid);
?>
<!-- This file is the HTML Template for a register -->
<style type="text/css">
@font-face { font-family: "Segoe UI"; src: url('./fonts/segoeui.otf');  }
@font-face { font-family: "Helvetica Neue"; src: url('./fonts/HelveticaNeueLTStd-Lt.otf');  }
/*initialization*/
html, body, div, span,    
h1, h2, h3, h4, h5, h6, p, blockquote, pre,   
a, abbr, acronym, address, big, cite, code,   
img, ins, kbd, q, s, samp,   
small, strike, strong,    
dl, dt, dd, ol, ul, li,   
fieldset, form, label, legend,   
table, caption, tbody, tfoot, thead, tr, th, td {   
    margin: 0;   
    padding: 0;   
    border: 0;   
    outline: 0;   
    /*font-size: 100%; */  
    vertical-align: baseline;   
    background: transparent;   
}   
body {
	line-height: 1;
	text-align:left;
}   
ol, ul {   
    list-style: none;   
}   
blockquote, q {   
    quotes: none;   
}   
blockquote:before, blockquote:after,   
q:before, q:after {   
    content: '';   
    content: none;   
}   
   
table {   
    border-collapse: collapse;   
    border-spacing: 0;   
}  

body{
	background-color:#DDDDDD;
}
#wrapper{
	margin:20px auto;
	width:1000px;
	background-color:white;
	box-shadow:0 0 25px #444;
	padding:20px 0;
}
#regtitle h1{
	font-size:36px;
	font-weight:100;
	color:#333;
	font-family:"Segoe UI","Helvetica Neue", Helvetica, Ubuntu;
	margin-left:100px;
	margin-top:20px;
}
#regtitle p, #regtitle ul {
	font-size:18px;
	font-weight:100;
	color:#333;
	font-family:"Segoe UI","Helvetica Neue", Helvetica, Ubuntu;
	margin-left:100px;
	margin-top:20px;
}
#progbar{
	width:800px;
	height:2px;
	margin:20px auto;
	background-color:#C44D58;
}
#regbutton{
	width:250px;
	height:60px;
	background-color:#C44D58;
	margin:20px auto 0;
	text-align:center;
	font-size:22px;
	font-family:"Segoe UI","Helvetica Neue", Helvetica, Ubuntu;
	color:white;
	cursor:pointer;
	-moz-transition: background-color 0.5s;
	-webkit-transition: background-color 0.5s;
	-o-transition: background-color 0.5s;
	transition: background-color 0.5s;
}
#regbutton:hover{
	background-color:#FF6B6B;
}
#regbutton p{
	margin:0;
	padding-top:16px;
	vertical-align:central;
}
</style>

<div id="wrapper">
<div id="regtitle">
        	<h1>Control Panel</h1>
        	<div id="progbar">
            </div>
<p>Note: Freeshell can only be accessed within USTC campus!
<style>
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
</style>
<ul class="table">
  <li><span class="h">Shell ID:</span><?=$appid?>
  <li><span class="h">Status:</span><?=$node['mystatus']?> <?php unset($node['mystatus']); ?>
  <li><span class="h">IP address:</span><?=get_node_ip($info['nodeno'])?>
  <li><span class="h">SSH port:</span><?=$info['sshport']?>
  <li><span class="h">SSH command:</span><span class="c">ssh -p <?=$info['sshport']?> root@<?=$info['ip']?></span>
  <li><span class="h">HTTP port:</span><?=$info['httpport']?>
  <li><span class="h">HTTP address:</span><span class="c">http://<?=$info['ip']?>:<?=$info['httpport']?>/</span>
</ul>
<div id="progbar"></div>
<p>Manage your freeshell:
<p class="buttons">
  <span><button onclick="manage('start')">Start</button></span>
  <span><button onclick="manage('stop')">Shutdown</button></span>
  <span><button onclick="manage('reboot')">Reboot</button></span>
</p>
<div id="progbar"></div>
<p>Server status:
<ul class="table">
  <li><span class="h">Node</span>#<?=$info['nodeno']?>
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
<p>Resource Limits:
<ul class="table">
  <li><span class="h">Memory</span><?=$info['nodeno']==3?"12G":"16G"?>, unlimited
  <li><span class="h">CPU</span>8 cores * Xeon X5450, unlimited
  <li><span class="h">Disk</span><span class="r">5GB. You can use up to 7GB in a grace period of 24 hours.<br>Please delete unused files as soon as possible :)</span>
  <li><span class="h">Process</span>Up to 200 processes, including kernel threads.
  <li><span class="h">TCP sockets</span>100
  <li><span class="h">UDP sockets</span>100
</ul>
<div id="progbar"></div>
<p>For Linux newbies:
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

