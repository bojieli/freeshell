<?php
include_once "header.php";
include_once "db.php";

if (!isset($_SESSION['email']))
	die("<script>window.location.href='index.php';</script>");
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $email = mysql_result(mysql_query("SELECT email FROM shellinfo WHERE id='".$_GET['id']."'"), 0);
    if ($email == $_SESSION['email']) {
        $_SESSION['appid'] = $_GET['id'];
        die("<script>window.location.href='login.php';</script>");
    }
}

$email = addslashes($_SESSION['email']);
$rs = mysql_query("SELECT * FROM shellinfo WHERE `email`='$email' ORDER BY id");
?>
<style>
.shell-list {
	margin-left: 100px !important;
}
.shell-select, .shell-select-head {
    border-bottom: 1px dashed #AAA;
}
.shell-select td, .shell-select-head th {
	font-size: 20px !important;
	font-family: "Segoe UI","Helvetica Neue", Helvetica, Ubuntu;
    padding: 0 20px 0 20px;
}
.shell-select:hover {
    cursor: pointer;
    background: #DDD;
}
</style>
<div id="wrapper">
<div id="regtitle">
        	<h1>Select Freeshell</h1>
        	<div id="progbar">
            </div>
<table class="shell-list">
<tr class="shell-select-head">
    <th>ID</th>
    <th>Node</th>
    <th>Hostname</th>
    <th>Status</th>
</tr>
<?php
while ($info = mysql_fetch_array($rs)) {
    echo '<tr class="shell-select" shell-id="'.$info['id'].'">';
    echo "<td>".$info['id']."</td>";
    echo "<td>".$info['nodeno']."</td>";
    echo "<td>".$info['hostname']."</td>";
    echo "<td>".($info['isactive'] ? 'Active' : 'Not Activated')."</td>";
    echo "</tr>\n";
}
?>
</table>
<div class="progbar"></div>
<p>
Need more freeshells? <a href="logout.php">Logout</a> and register a new one.
</p>
</div>
</div>
<script src="js/jquery.js" type="text/javascript"></script>
<script>
$('.shell-select').on('click', function(){
    window.location.href = 'select-shell.php?id=' + $(this).attr('shell-id');
})
</script>
