<?php
$name = $_POST['name'];
$pass = $_POST['pass'];
$appid = get_appid_by_field('username', $name);
if ($appid <= 0) {
    die("<script>history.go(-1);</script>");
}
$info = get_appinfo($appid);
$host = $info['appname'].'.'.$_SERVER['HTTP_HOST'];
$login_helper = "http://$host/wp-login-crossdomain.php";
$redirect = "http://$host/wp-admin/";
?>
<meta charset="utf-8">
<script type="text/javascript" src="js/jquery.js"></script>
<form id="login" action="<?=$login_helper?>" method="post">
  <input type="hidden" name="log" value="<?=$name?>">
  <input type="hidden" name="pwd" value="<?=$pass?>">
  <input type="hidden" name="testcookie" value="1">
  <input type="hidden" name="wp-submit" value="登录">
  <input type="hidden" name="redirect_to" value="<?=$redirect?>">
</form>
<script>
$(function(){
    $('#login').submit();
});
</script>
