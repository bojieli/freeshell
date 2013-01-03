<?php
include_once "nodes.inc.php";
include_once "db.php";

if (!is_numeric($_GET['appid']))
    die('Invalid request');
$appid = $_GET['appid'];

$info = mysql_fetch_array(mysql_query("SELECT nodeno,token,isactive FROM shellinfo WHERE `id`='$appid'"));

if (empty($info))
    die('App does not exist. The link may have been expired.');
if ($info['isactive'])
    die('Your shell is activated. Please login to the control panel.');
if ($info['token'] !== $_GET['token'])
    die('Incorrect token. Please copy the link to the address bar of your browser and retry.');

mysql_query("UPDATE shellinfo SET `isactive`=1 WHERE `id`='$appid'");
activate_vz($info['nodeno'], $appid);
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
        	<h1>Congratulations!</h1>
        	<div id="progbar">
            </div>
<p>All things done.</p>
<p>You can login to your shell with SSH now:</p>
<p style="font:Courier New">ssh -p <?php echo $appid + 10000 ?> root@<?php echo get_node_ip($info['nodeno']); ?></p>
<p>The root password is the same as the login password of control panel. It is recommended that you create your own account and login with it instead of root.</p>
<p>Also you can login to the control panel for more info:</p>
</div>
<div id="regbutton" onclick="javascript:document.location.href='/'">
        	<p>Let's Rock!</p>
</div>
</div>
