<?php
include "verify.inc.php";
if (!empty($_REQUEST['name']))
    echo checkname($_REQUEST['name']);
else if (!empty($_REQUEST['email']))
    echo checkemail($_REQUEST['email']);
else if (!empty($_REQUEST['folder']))
    echo checkfolder($_REQUEST['folder']);

