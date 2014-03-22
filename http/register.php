<?php
include_once "header.php";
include_once "db.php";
include_once "verify.inc.php";
include_once "nodes.inc.php";
include_once "admin.inc.php";

$password = $_POST['regpassword'];
if ($password !== $_POST['regconfpass'])
    alert('Passwords mismatch.');
$email = $_POST['regemail'];
$hostname = addslashes($_POST['hostname']);

if (checkhost($hostname) || strlen($password)<6 || checkemail($email)) {
    alert('Sorry, sanity check failed.');
}

list($appid, $nodeno) = create_freeshell_in_db($hostname, generate_password($password), $email, $_POST['nodeno']);
if (!$appid)
    alert('Database error, please retry. If the problem persists, please contact support@freeshell.ustc.edu.cn');

$info = mysql_fetch_array(mysql_query("SELECT * FROM shellinfo WHERE `id`='$appid'"));
if (empty($info))
    die('Failed to create freeshell, please contact us at support@freeshell.ustc.edu.cn');
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
create_vz($nodeno, $appid, $hostname, $password, $info['diskspace_softlimit'], $info['diskspace_hardlimit']);

$token = random_string(40);
mysql_query("UPDATE shellinfo SET `token`='$token' WHERE `id`='$appid'");
send_activate_mail($email, $appid, $token);
