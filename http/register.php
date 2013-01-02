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
include "verify.inc.php";
$username = $_POST['regname'];
$password = $_POST['regpassword'];
if ($password != $_POST['regconfpass'])
    alert('Passwords mismatch.');
$email = $_POST['regemail'];
if (strtoupper($_POST['invitation']) != 'USTCLUG')
    alert('Sorry, the blog service is currently in Beta. You need an invitation code.');
$title = $_POST['regbtitle'];
$appname = $_POST['folderaddr'];

if (checkname($username) || checkemail($email) || checkfolder($appname))
    internal_error('check failed');

if (!($appid = create_app($appname, $username, $email, $password)))
    internal_error('failed create_app');

if (!install_blog_filesystem($appname))
    internal_error('failed install_blog_filesystem');

$siteurl = "http://$appname.blog.ustc.edu.cn";
if (!init_database($username, $password, $email, $title, $siteurl))
    internal_error('failed init_database');

if (!($info = get_appinfo($appid)))
    internal_error('failed get_appinfo');

if (!send_activate_mail($email, $info['token'], $appid, $appname, $username))
    internal_error('failed sendmail');

?>
<div id="wrapper">
<div id="regtitle">
        	<h1>It's almost there!</h1>
        	<div id="progbar">
            </div>
<p>Your have successfully registered your blog.</p>
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
    $title = "Account Activation for $appname.blog.ustc.edu.cn";
    $body = "Hello $username:\n\nThanks for registering USTC blog. Please click on the link below (or copy it to the address bar) to activate your blog account.\n\nhttp://".$_SERVER['HTTP_HOST']."/activate.php?appid=$appid&token=$token\n\nThis link will expire in 48 hours. Any problems, please email us: lug@ustc.edu.cn\n\nSincerely,\nUSTC Blog Team";
    return sendmail($email, $title, $body);
}

function init_database($username, $password, $email, $title, $siteurl) {
    $queries = explode_queries(file_get_contents("wp_installed.sql"));
    foreach ($queries as $query) {
        mysql_query($query);
    }

    update_option('blogname', $title);
    update_option('admin_email', $email);
    update_option('siteurl', $siteurl);
    update_option('home', $siteurl);
    
    $password = wp_hash_password($password);
    $username = addslashes($username);
    $email = addslashes($email);
    $title = addslashes($title);
    mysql_query("UPDATE wp_users SET user_login='$username', user_pass='$password', user_nicename='$username', user_email='$email', user_registered=NOW()");

    mysql_query("UPDATE wp_comments SET comment_date=NOW(), comment_date_gmt=NOW()");
    mysql_query("UPDATE wp_posts SET post_date=NOW(), post_date_gmt=NOW()");

    return true;
}
function update_option($field, $value) {
    $field = addslashes($field);
    $value = addslashes($value);
    mysql_query("UPDATE wp_options SET `option_value`='$value' WHERE `option_name`='$field'");
}
function wp_hash_password($password) {
    include "class-phpass.php";
    $hasher = new PasswordHash(8, true);
    return $hasher->HashPassword($password);
}
function explode_queries($querystr) {
    $queries = explode(";\n", $querystr);
    foreach ($queries as $i => $query) {
        $query = trim($query);
        if (in_array(substr($query,0,2), array('/*', '--')))
            unset($queries[$i]);
    }
    return $queries;
}
?>
