<?php
include "verify.inc.php";
if (!empty($_REQUEST['email']))
    echo checkemail($_REQUEST['email']);
else if (!empty($_REQUEST['host']))
    echo checkhost($_REQUEST['host']);

