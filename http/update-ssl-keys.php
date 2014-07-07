<?php
date_default_timezone_set('Asia/Chongqing');
session_start();
session_write_close();
include_once "proxy.inc.php";
include_once "ssl.inc.php";
include_once "db.php";

if (empty($_SESSION['appid']) || !is_numeric($_SESSION['appid']))
    include "logout.php";
if (empty($_REQUEST['domain']))
    include "logout.php";

$info = mysql_fetch_array(checked_mysql_query("SELECT * FROM cname WHERE id='".$_SESSION['appid']."' AND domain='".addslashes($_REQUEST['domain'])."'"));
if (empty($info) || $info['id'] != $_SESSION['appid'])
    include "logout.php";

if (!empty($_FILES)) {
    if (handle_uploaded_ssl_keys($_REQUEST['domain'])) {
        checked_mysql_query("UPDATE cname SET is_ssl=TRUE WHERE id='".$_SESSION['appid']."' AND domain='".addslashes($_REQUEST['domain'])."'");
        update_proxy_conf();
        display_success();
    }
    // should never reach here
}

function display_success() {
    ?>
<html>
<head>
<meta charset="utf-8" />
<link rel="stylesheet" href="css/register.css" type="text/css" media="screen" />
</head>
<body>
<div id="wrapper">
<div id="regtitle">
        	<h1>SSL Key Updated</h1>
        	<div id="progbar">
            </div>
<p>New SSL key and certificate has been applied for <?=$_REQUEST['domain']?>.</p>
<p>If you have set up DNS for <?=$_REQUEST['domain']?> correctly, you should be able to access your site securely via <a href="https://<?=$_REQUEST['domain']?>">https://<?=$_REQUEST['domain']?></a></p>
</div>
<div id="regbutton" onclick="javascript:document.location.href='admin.php'">
        	<p>Back to Home</p>
</div>
</div>
<?php
    exit();
}

function display_error($error) {
?>
<html>
<head>
<meta charset="utf-8" />
<link rel="stylesheet" href="css/register.css" type="text/css" media="screen" />
</head>
<body>
<div id="wrapper">
<div id="regtitle">
        	<h1>SSL Config Error</h1>
        	<div id="progbar">
            </div>
<p><?=$error?></p>
<p>Please double check and try again. If the error persists, please contact us.</p>
</div>
<div id="regbutton" onclick="javascript:document.location.href='update-ssl-keys.php?domain=<?=$_REQUEST['domain']?>'">
        	<p>Back</p>
</div>
</div>
<?php
    exit();
}

function check_upload($name, $dirname, $filename, $errormsg) {
	if (!move_uploaded_file($_FILES[$name]['tmp_name'], "$dirname/$filename")) {
		display_error("Failed to save uploaded $errormsg");
		return false;
	}
	return true;
}

function handle_uploaded_ssl_keys($domain) {
	if (!$_FILES['ssl-key']['size'] && !$_FILES['ssl-cert']['size'] && !$_FILES['ssl-intermediate-cert']['size']) {
        display_error('You did not upload any file!');
		return false;
	}
	if (!$_FILES['ssl-key']['size'] || !$_FILES['ssl-cert']['size'] || !$_FILES['ssl-intermediate-cert']['size']) {
		display_error('SSL Key or Certificate or Intermediate Certificate not uploaded');
		return false;
	}
	$dirname = "/tmp/freeshell-uploaded-keys/$domain";
	mkdir($dirname, 0700, true);
	if (!check_upload('ssl-key', $dirname, 'ssl.key', 'SSL Key'))
		return false;
	if (!check_upload('ssl-cert', $dirname, 'ssl.crt', 'SSL Certificate'))
		return false;
	if (!check_upload('ssl-intermediate-cert', $dirname, 'intermediate.crt', 'Intermediate Certificate'))
		return false;

	$status = install_ssl_key($domain);
	switch ($status) {
	case 0:
		return true;
	case 2:
		display_error('The domain name must contain only lower-case letters, digits and hyphen');
		return false;
	case 3:
		display_error('Internal error: Failed to invoke SSL key installer');
		return false;
	case 100:
		display_error('Internal error: unable to read uploaded keys');
		return false;
	case 101:
		display_error('Invalid SSL key. Key must be in PEM format.');
		return false;
	case 102:
		display_error('Invalid SSL certificate. Certificate must be in PEM format.');
		return false;
	case 103:
		display_error('SSL key and certificate does not match.');
		return false;
	case 104:
		display_error('Your intermediate cert is either invalid or untrusted. Please get PEM format intermediate certificate from your certificate issuer.');
		return false;
	case 105:
	    display_error('Your certificate and intermediate certificate does not match');
		return false;
	case 106:
		display_error('Your certificate and your domain name (Site Address) does not match');
		return false;
	default:
		display_error('Internal error ' . $status['status'] . '. Please contact us.');
		return false;
	}
}

?>
<html>
<head>
<meta charset="utf-8" />
</head>
<body>
<h1>HTTPS proxy <?=$_REQUEST['domain']?> config</h1>
<p class="note">
<?php if ($info['is_ssl']) { ?>
SSL key and certificate for your domain has been uploaded. If you want to update it, upload another pair of SSL key and certificates.
<?php } else { ?>
Upload SSL key and certificate below if you want to use HTTPS for your own domain.
<?php } ?>
</p>
<form method="post" action="update-ssl-keys.php" enctype="multipart/form-data">
<input type="hidden" name="domain" value="<?=$_REQUEST['domain']?>" />
<table>
<tr><th>SSL Key (.key)</th><td><input name="ssl-key" type="file" id="ssl-key" /></td></tr>
<tr><th>SSL Certificate (.crt/.pem)</th><td><input name="ssl-cert" type="file" id="ssl-cert" /></td></tr>
<tr><th>Intermediate Certificate (.crt/.pem)</th><td><input name="ssl-intermediate-cert" type="file" id="ssl-intermediate-cert" /></td></tr>
</table>
<p><button type="submit">Upload Files</button></p>
<hr />
<p class="note">The SSL certificate must be in PEM format, must be globally valid and must match your Site Address.</p>
<p class="note">You need to apply a valid SSL certificate from StartSSL or other certificate authorities.</p>
<p class="note">The SSL key must be in decrypted form. Some certificate issuers provide keys in encrypted form.</p>
<p class="note">The intermediate certificate should be retrieved from the certificate issuer (PEM format).</p>
</form>

