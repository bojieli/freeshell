<?php
include_once "header.php";
include_once "db.php";
include_once "verify.inc.php";
include_once "nodes.inc.php";
include_once "admin.inc.php";

$password = $_POST['regpassword'];
if ($password != $_POST['regconfpass'])
    alert('Passwords mismatch.');
$email = $_POST['regemail'];
$hostname = addslashes($_POST['hostname']);

if (checkhost($hostname) || strlen($password)<6 || checkemail($email)) {
    alert('Sorry, sanity check failed.');
}

$salt = random_string(20);
$salted_pass = sha1(sha1($password).$salt) . '/'. $salt;

mysql_query("INSERT INTO shellinfo SET `hostname`='$hostname', `password`='$salted_pass', `email`='$email'");
$appid = mysql_insert_id();
if (empty($appid))
    alert('Database error!');

$nodeno = $appid % nodes_num();
if ($nodeno == 0)
    $nodeno = nodes_num();

mysql_query("UPDATE shellinfo SET `nodeno`='$nodeno' WHERE `id`='$appid'");

?>
<div id="wrapper">
<div id="regtitle">
        	<h1>It's almost there!</h1>
        	<div id="progbar">
            </div>
<p>Your freeshell is installing.</p>
<p>We will send you: <?=$email?> an confirmation mail once installation finished.</p>
<p>Please activate your account by clicking the link inside the mail.</p>
</div>
<div id="regbutton" onclick="javascript:document.location.href='http://email.ustc.edu.cn'">
        	<p>Check now</p>
</div>
</div>
<?php
fastcgi_finish_request();
create_vz($nodeno, $appid, $hostname, $password);

$token = random_string(40);
mysql_query("UPDATE shellinfo SET `token`='$token' WHERE `id`='$appid'");
send_activate_mail($email, $appid, $token);
