#!/usr/bin/php
<?php

chdir(dirname(__FILE__) . "/..");
require_once("sql.inc.php");
require_once("lock.inc.php");
require_once("kassenbuch.inc.php");

if (databaseIsLocked($year)) {
	exit;
}
databaseLock($year, basename(__FILE__), "localhost");

$account = $_SERVER["argv"][1];
$value = $_SERVER["argv"][2];
$num = $_SERVER["argv"][3];
$date = $_SERVER["argv"][4];

$timestamp = date_create_from_format("Y-m-d", $date)->getTimestamp();

$rslt = $sql->query("select guid from transactions where num = 'SAMMEL'");
if ($rslt->num_rows === 1) {
	$guid = $rslt->fetch_object()->guid;
	$tx = sqlGetTransaction($guid);
	foreach ($tx["splits"] as $split) {
		if ($split["account_code"] == $account && $split["value"] == $value) {
			sqlSetTransactionTimestamp($guid, $timestamp);
			sqlSetTransactionNum($guid, $num);
			databaseUnlock($year);
			exit(0);
		}
	}
}

databaseUnlock($year);
exit(1);
