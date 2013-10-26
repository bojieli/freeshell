<?php
session_start();
include_once "../db.php";
include_once "../admin.inc.php";

if (empty($_SESSION['isadmin']))
    exit();
?>
<div id="wrapper">
<div id="regtitle">
        	<h1>Reset Web Password</h1>
        	<div id="progbar">
            </div>
<?php
$rs = array();
if (is_numeric($_POST['appid'])) {
    $id = $_POST['appid'];
    $info = mysql_fetch_array(mysql_query("SELECT * FROM shellinfo WHERE id=".$id));
    if (empty($info)) {
        echo '<p>Not Exist</p>';
        goto print_table;
    }
    $password = random_string(12);
    change_password($id, generate_password($password));
    send_reset_password_email($info['email'], $id, $password);
    echo "<script>alert('Reset Password Email Sent')</script>";
}
print_table:
?>
<form action="reset-web-pass.php" method="post">
<table>
<tr><td>Shell ID</td><td><input name="appid" value="<?=$_POST['appid']?>" /></td></tr>
<tr><td></td><td><button type="submit">Reset Password</button></td></tr>
</table>
</form>
<script src="js/jquery.js" type="text/javascript"></script>
