<?php

require_once("sql.inc.php");
require_once("login.inc.php");
require_once("lock.inc.php");
loginRequire("kreditoren");

if (isset($_POST["add"])) {
	try {
		if (databaseIsLocked($year)) {
			throw new Exception("Datenbank gesperrt");
		}
		databaseLock($year, basename(__FILE__), "localhost");

		$offset = $_REQUEST["offset"];
		$code = substr($offset,0,1);
		$num = substr($offset,1);

		if ($code == "K") {
			$type = 'PAYABLE';
		} elseif ($code == "D") {
			$type = 'RECEIVABLE';
		} else {
			die("error");
		}

		$account = sqlGetAccountByCode($code);
		$parent_guid = $account["guid"];

		do {
			// While account already exists, increase $num
			try {
				sqlGetAccountByCode($code . $num);
				$num++;
			} catch (Exception $e) {
				$code = $code . $num;
				$num = null;
			}
		} while ($num != null);

		$guid = md5($year.$code);
		$name = substr($code,1)." ".$_REQUEST["name"];
		$description = "";
		sqlAddAccount($guid, $parent_guid, $type, $code, $name, $description);

		databaseUnlock($year);
		$result = array("status" => "ok", "guid" => $guid, "code" => $code);
	} catch (Exception $e) {
		$result = array("status" => "fail", "message" => $e->getMessage());
	}
	die(json_encode($result));
}

include("templates/kreditoren.php");
