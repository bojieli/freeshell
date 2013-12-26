<?php
session_start();
if (empty($_SESSION['isadmin']))
    exit();
include_once '../db.php';
include_once '../admin.inc.php';

function is_numlist($str) {
    $splits = explode(',', $str);
    foreach ($splits as $split)
        if (!is_numeric($split))
            return false;
    return true;
}

$targets = array();
if ($_POST['shells'] == 'all' || $_POST['nodes'] == 'all') {
    $rs = mysql_query("SELECT id, email from shellinfo WHERE isactive=1");
    while ($row = mysql_fetch_array($rs))
        $targets[$row['id']] = $row['email'];
}
else {
    if (!empty($_POST['shells'])) {
        if (!is_numlist($_POST['shells'])) {
            alert_noredirect('Not numeric!');
            exit();
        }
        $rs = mysql_query("SELECT id, email FROM shellinfo WHERE isactive=1 AND id IN (".$_POST['shells'].")");
        while ($row = mysql_fetch_array($rs))
            $targets[$row['id']] = $row['email'];
    }
    if (!empty($_POST['nodes'])) {
        if (!is_numlist($_POST['nodes'])) {
            alert_noredirect('Not numeric!');
            exit();
        }
        $rs = mysql_query("SELECT id, email FROM shellinfo WHERE isactive=1 AND nodeno IN (".$_POST['nodes'].")");
        while ($row = mysql_fetch_array($rs))
            $targets[$row['id']] = $row['email'];
    }
}

if (empty($targets)) {
    $targets[$_SESSION['appid']] = $_SESSION['email'];
}

if (!empty($_POST['title']) && !empty($_POST['content'])) {
    $idlist = array();
    foreach ($targets as $id => $email) {
        send_admin_email($email, $id, $_POST['title'], $_POST['content']);
        $idlist[] = $id;
    }
    alert_noredirect("Email sent to freeshells ".implode(',', $idlist));
} else {
    if (!empty($_POST))
        alert_noredirect("Title or content should not be empty");
}
?>
<h1>Send Email to Users</h1>
<p>If to all users, use "all" (without quote) in shell ID or Node ID.</p>
<p>If no shell ID and Node ID is specified, or no such shell is found, email is sent to you as a test.</p>
<form action="sendmail.php" method="post">
<table>
<tr><td>Shell ID</td><td><input name="shells" /> (comma separated list of IDs)</td></tr>
<tr><td>Node ID</td><td><input name="nodes" /> (comma separated list of IDs)</td></tr>
<tr><td>Title</td><td><input name="title" style="width:400px" value="<?=$_POST['title']?>" /> (NO Chinese chars please)</td></tr>
<tr><td>Content</td><td><textarea name="content" rows="20" cols="80"><?=$_POST['content']?></textarea></td></tr>
<tr><td></td><td><button type="submit">Send</button></td></tr>
</table>
</form>
