<?php
session_start();
include_once "db.php";
include_once "nodes.inc.php";

$email = addslashes($_POST['email']);
$pass = $_POST['pass'];

$rs = mysql_query("SELECT * FROM shellinfo WHERE `email`='$email'");
if (!$rs)
    error('Email account does not exist.');
$info = mysql_fetch_array($rs);
$passes = explode('/', $info['password']);
if (sha1(sha1($pass).$passes[1]) !== $passes[0])
    error('Wrong password!');
if (!$rs['isactive'])
    error('Your account is not activated! Please check your email.');

$info['ip'] = get_node_ip($info['nodeno']);
$info['sshport'] = 10000 + $info['id'];
$info['httpport'] = 20000 + $info['id'];

$_SESSION['email'] = $email;

function error($msg) {
    echo "<script>";
    echo "alert('$msg');";
    echo "window.location.href='index.php';";
    echo "</script>";
    exit();
}
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
#regtitle p{
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
<p>This is a simple and naive control panel for freeshell.</p>
<p>Notice: Freeshell can only accessed within USTC campus!
<ul>
  <li>Node: #<?=$info['nodeno']?>
  <li>IP address: <?=get_node_ip($info['nodeno'])?>
  <li>SSH Port: <?=$info['sshport']?>
  <li>SSH command: <pre>ssh -p <?=$info['sshport']?> root@<?=$info['ip']?></pre>
  <li>HTTP Port: <?=$info['httpport']?>
  <li>HTTP Address: <pre>http://<?=$info['ip']?>:<?=$info['httpport']?>/</pre>
</ul>
<p>Operations:
<ul>
  <li><button onclick="manage('start')">Start</button>
  <li><button onclick="manage('stop')">Shutdown</button>
  <li><button onclick="manage('reboot')">Reboot</button>
<ul>
<p>More functions are under development...
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
            alert("Your request have been sent.");
        }
    });
}
</script>
