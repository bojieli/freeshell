<?php
session_start();
session_write_close();
include_once "../db.php";
include_once "../nodes.inc.php";

if (empty($_SESSION['isadmin']))
    exit();
?>
<h1>Search User</h1>
<?php
$rs = array();
if (is_numeric($_POST['appid']))
    $rs = checked_mysql_query("SELECT * FROM shellinfo WHERE id=".$_POST['appid']);
else if (!empty($_POST['email']))
    $rs = checked_mysql_query("SELECT * FROM shellinfo WHERE email LIKE '%".addslashes($_POST['email'])."%'");
else if (!empty($_POST['hostname']))
    $rs = checked_mysql_query("SELECT * FROM shellinfo WHERE hostname LIKE '%".addslashes($_POST['hostname'])."%'");
else if (!empty($_POST['http_subdomain']))
    $rs = checked_mysql_query("SELECT * FROM shellinfo WHERE http_subdomain LIKE '%".addslashes($_POST['http_subdomain'])."%'");
else
    goto print_table;
?>
<style>
.shell-select, .shell-select-head {
    border-bottom: 1px dashed #AAA;
}
.shell-select td, .shell-select-head th {
	font-size: 20px !important;
	font-family: "Segoe UI","Helvetica Neue", Helvetica, Ubuntu;
    padding: 0 20px 0 20px;
}
.shell-select:hover {
    background: #DDD;
}
</style>
<table>
<tr class="shell-select-head"><th>ID</th><th>Email</th><th>HostName</th><th>Node</th><th>Disk Soft Quota</th><th>Disk Hard Quota</th><th>HTTP Subdomain</th><th>Admin Operations</th></tr>
<?php
$counter = 0;
while ($row = mysql_fetch_array($rs)) {
    $counter++;
    $id = $row['id'];
    echo "<tr class=\"shell-select\"><td>".
        "$id</td><td>".
        $row['email']."</td><td>".
        $row['hostname']."</td><td>".
        $row['nodeno']."</td><td>".
        $row['diskspace_softlimit']."</td><td>".
        $row['diskspace_hardlimit']."</td><td>".
        $row['http_subdomain']."</td><td>".
        "<button onclick=\"manage('force-stop', $id)\">Shutdown</button> ".
        (!$row['blocked'] ?
        "<button onclick=\"manage('block', $id)\">Block</button>" :
        "<button onclick=\"manage('unblock', $id)\">Unblock</button>").
        "</td></tr>\n";
}
?>
</table>
<?php
if ($counter == 0)
    echo "<p>No matches found</p>";
?>
<hr />
<?php 
print_table:
?>
<form action="find-user.php" method="post">
<table>
<tr><td>Shell ID</td><td><input name="appid" value="<?=$_POST['appid']?>" /></td></tr>
<tr><td>Email</td><td><input name="email" value="<?=$_POST['email']?>" /></td></tr>
<tr><td>HostName</td><td><input name="hostname" value="<?=$_POST['hostname']?>" /></td></tr>
<tr><td>HTTP Subdomain</td><td><input name="http_subdomain" value="<?=$_POST['http_subdomain']?>" /></td></tr>
<tr><td></td><td><button type="submit">Search</button></td></tr>
</table>
</form>
<p>Note: Match by shell ID or email or hostname. Shell ID is exact match. Email, hostname and HTTP subdomain are substring match.</p>
<p>"Shutdown" means to shutdown a freeshell immediately. For efficiency we do not detect the current status of the freeshell, so even if the freeshell is already down, the "Shutdown" button still appears.</p>
<p>"Block" means to block a user's access to freeshell control panel. It will not shutdown the freeshell.</p>
<p>Management operations will send email to the affected user, as well as you.</p>

<script src="../js/jquery.js" type="text/javascript"></script>
<script type="text/javascript">
function ajaxSuccessFunc(msg){
    if (msg.length > 0)
        alert(msg);
    else
        alert('OK. Please refresh the page for the new status.');
}
function ajaxErrorFunc() {
    alert('Failed to connect to server!');
}
function ajaxManage(data) {
    $.ajax({
        url: '../manage.php',
        type: 'post',
        async: true,
        data: data,
        success: ajaxSuccessFunc,
        error: ajaxErrorFunc,
    });
}
function manage(action, id) {
    if (!confirm("Do you really want to " + action + " freeshell #" + id + "?"))
        return;
    ajaxManage({appid: id, action: action});
}
</script>
