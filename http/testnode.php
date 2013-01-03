<?php
if (empty($argv) || count($argv) < 3)
	exit();
$nodeno = $argv[1];
$id = $argv[2];
$hostname = $argv[3];
$password = $argv[4];
include_once "nodes.inc.php";
echo create_vz($nodeno, $id, $hostname, $password);
echo activate_vz($nodeno, $id);
