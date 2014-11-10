<?php
include_once '../port-forwarding.inc.php';
$fwd = new PortForwarding();
echo $fwd->commit();
