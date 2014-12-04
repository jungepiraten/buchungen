<?php

require_once("api.inc.php");
require_once("sql.inc.php");
require_once("login.inc.php");
require_once("lock.inc.php");

if (!in_array($_SERVER["REMOTE_ADDR"], array("172.16.2.6")) && !$auth["buchen"]) {
	out(403, array("error" => "NOT_ALLOWED"));
}

try {
	foreach (array("beleg","description","splits") as $param) {
		if (!isset($_REQUEST[$param])) {
			throw new Exception("Missing parameter ".$param);
		}
	}

	$guid = $_REQUEST["guid"] ? $_REQUEST["guid"] : md5(microtime(true).rand(100,200));
	$num = $_REQUEST["beleg"];
	$postdate = $_REQUEST["postdate"] ? strtotime($_REQUEST["postdate"]) : time();
	$description = $_REQUEST["description"];
	$splits = array();
	foreach ($_REQUEST["splits"] as $split) {
		$account = sqlGetAccountByCode($split["konto"]);
		if ($account == null) {
			throw new Exception("invalid account " . $split["konto"]);
		}
		$splits[] = array(
			"account_guid" => $account["guid"],
			"memo" => "",
			"value" => $split["value"] * (-1)
		);
	}

	if (databaseIsLocked($year)) {
		throw new Exception("Datenbank gesperrt");
	}
	databaseLock($year, basename(__FILE__), "localhost");
	sqlAddTransaction($guid, $num, $postdate, $description);
	sqlAddSplits($guid, $splits);
	databaseUnlock($year);

	out(200, array("guid" => $guid));
} catch (Exception $e) {
	out(400, array("error" => "GENERIC", "message" => $e->getMessage()));
}
