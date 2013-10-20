<?php
session_start();
include_once "../db.php";
include_once "../nodes.inc.php";

if (empty($_SESSION['isadmin']))
    exit();
?>
<h1>Search User</h1>
<?php
$rs = array();
if (is_numeric($_POST['appid']))
    $rs = mysql_query("SELECT * FROM shellinfo WHERE id=".$_POST['appid']);
else if (!empty($_POST['email']))
    $rs = mysql_query("SELECT * FROM shellinfo WHERE email LIKE '%".addslashes($_POST['email'])."%'");
else if (!empty($_POST['hostname']))
    $rs = mysql_query("SELECT * FROM shellinfo WHERE hostname LIKE '%".$_POST['hostname']."%'");
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
<tr class="shell-select-head"><th>ID</th><th>Email</th><th>HostName</th><th>Node</th><th>Disk Soft Quota</th><th>Disk Hard Quota</th></tr>
<?php
$counter = 0;
while ($row = mysql_fetch_array($rs)) {
    $counter++;
    echo "<tr class=\"shell-select\"><td>".
        $row['id']."</td><td>".
        $row['email']."</td><td>".
        $row['hostname']."</td><td>".
        $row['nodeno']."</td><td>".
        $row['diskspace_softlimit']."</td><td>".
        $row['diskspace_hardlimit']."</td></tr>\n";
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
<tr><td></td><td><button type="submit">Search</button></td></tr>
</table>
</form>
<p>Note: Match by shell ID or email or hostname. Shell ID is exact match, email and hostname are substring match.</p>
