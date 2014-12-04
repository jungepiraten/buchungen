<?php

require_once("sql.inc.php");
require_once("login.inc.php");
require_once("lock.inc.php");
loginRequire("kostenstellen");

$type = 'EQUITY';

if (isset($_POST["add"])) {
	try {
		if (databaseIsLocked($year)) {
			throw new Exception("Datenbank gesperrt");
		}
		databaseLock($year, basename(__FILE__), "localhost");

		$parent_guid = "";

		foreach ($_REQUEST["parents"] as $parent) {
			try {
				$account = sqlGetAccountByCode($parent["code"]);
				$code = $account["code"];
				$parent_guid = $account["guid"];
			} catch (Exception $e) {
				$guid = md5(rand(100,999).microtime().$parent["code"]);
				sqlMaybeAddAccount($guid, $parent_guid, $type, $parent["code"], $parent["name"], "");
				$code = $parent["code"];
				$parent_guid = $guid;
			}
		}

		$num = 1;
		do {
			// While account already exists, increase $num
			try {
				sqlGetAccountByCode($code . sprintf("%02d", $num));
				$num++;
			} catch (Exception $e) {
				$code = $code . sprintf("%02d", $num);
				$num = null;
			}
		} while ($num != null);

		$guid = $_REQUEST["guid"];
		$name = substr($code,-2)." ".$_REQUEST["name"];
		$description = ($_REQUEST["ticket"] != "" ? "Ticket #" . $_REQUEST["ticket"] . ": " : "") . $_REQUEST["legitimation"] . " (".$_REQUEST["vname"]." <".$_REQUEST["vmail"].">)";
		if ($_REQUEST["betrag"] != "") {
			$description .= " - " . sprintf("%.2f EUR", $_REQUEST["betrag"]);
		}
		sqlAddAccount($guid, $parent_guid, $type, $code, $name, $description);

		if ($_REQUEST["ticket"] != "") {
			mail("ticket+" . $_REQUEST["ticket"] . "@helpdesk.junge-piraten.de", "Kostenstelle angelegt", "Kostenstelle {$code} angelegt", "From: <kostenstellen@junge-piraten.de>");
		}

		databaseUnlock($year);
		$result = array("status" => "ok", "guid" => $guid, "code" => $code);
	} catch (Exception $e) {
		$result = array("status" => "fail", "message" => $e->getMessage());
	}
	die(json_encode($result));
}

include("templates/kostenstellen.php");
