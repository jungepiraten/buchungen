<?php

require_once("sql.inc.php");
require_once("login.inc.php");
require_once("lock.inc.php");
loginRequire("buchen");

if (isset($_POST["buchen"])) {
	try {
		foreach (array("guid","beleg","postdate","description","splits") as $param) {
			if (!isset($_POST[$param])) {
				throw new Exception("Missing parameter ".$param);
			}
		}

		$guid = $_POST["guid"];
		$num = $_POST["beleg"];
		$postdate = strtotime($_POST["postdate"]);
		$description = $_POST["description"];
		$splits = array();
		foreach ($_POST["splits"] as $split) {
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

		$result = array("status" => "ok", "guid" => $guid);
	} catch (Exception $e) {
		$result = array("status" => "fail", "message" => $e->getMessage());
	}
	die(json_encode($result));
}

include("templates/buchen.php");
