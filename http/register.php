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
<?php
include_once "db.php";
include_once "verify.inc.php";
include_once "nodes.inc.php";

$password = $_POST['regpassword'];
if ($password != $_POST['regconfpass'])
    alert('Passwords mismatch.');
$email = $_POST['regemail'];
if (strtoupper($_POST['invitation']) != 'SCGYSHELL')
    alert('Sorry, the freeshell service is currently in testing. You need an invitation code.');
$hostname = addslashes($_POST['hostname']);

if (checkhost($hostname) || strlen($password)<6 || checkemail($email)) {
    alert('Sorry, sanity check failed.');
}

$salt = random_string(20);
$salted_pass = sha1(sha1($password).$salt) . '/'. $salt;

mysql_query("INSERT INTO shellinfo SET `hostname`='$hostname', `password`='$saltedpass', `email`='$email'");
$appid = mysql_insert_id();
if (empty($appid))
    alert('Database error!');
$nodeno = $appid % nodes_num() + 1;
mysql_query("UPDATE shellinfo SET `nodeno`='$nodeno' WHERE `id`='$appid'");

create_vz($nodeno, $appid, $hostname, $password);
?>
<div id="wrapper">
<div id="regtitle">
        	<h1>It's almost there!</h1>
        	<div id="progbar">
            </div>
<p>Your freeshell have been created.</p>
<p>We have sent you: <?=$email?> an confirmation mail.</p>
<p>Please activate your account by clicking the link inside the mail.</p>
</div>
<div id="regbutton" onclick="javascript:document.location.href='http://email.ustc.edu.cn'">
        	<p>Check now</p>
</div>
</div>
<?php
function alert($msg) {
    die("<script>alert('$msg');location.href='/';</script>");
}
function internal_error($msg) {
    alert("Internal Error, please retry. Error message: $msg");
}

function send_activate_mail($email, $token, $appid, $appname, $username) {
    $title = "Account Activation for USTC freeshell";
    $body = "Hello $username:\n\nThanks for registering USTC blog. Please click on the link below (or copy it to the address bar) to activate your blog account.\n\nhttp://".$_SERVER['HTTP_HOST']."/activate.php?appid=$appid&token=$token\n\nThis link will expire in 48 hours. Any problems, please email us: lug@ustc.edu.cn\n\nSincerely,\nUSTC Blog Team";
    return sendmail($email, $title, $body);
}

