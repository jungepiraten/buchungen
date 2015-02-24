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
				$guid = md5($year.$parent["code"]);
				sqlMaybeAddAccount($guid, $parent_guid, $type, $parent["code"], substr($parent["code"],-2)." ".$parent["name"], "");
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

		$guid = md5($year.$code);
		$name = substr($code,-2)." ".$_REQUEST["name"];
		$description = ($_REQUEST["ticket"] != "" ? "Ticket #" . $_REQUEST["ticket"] . ": " : "") . $_REQUEST["legitimation"] . " (".$_REQUEST["vname"]." <".$_REQUEST["vmail"].">)";
		if ($_REQUEST["betrag"] != "") {
			$description .= " - " . sprintf("%.2f EUR", $_REQUEST["betrag"]);
		}
		sqlAddAccount($guid, $parent_guid, $type, $code, $name, $description);

		$recipients = array($_REQUEST["vmail"]);
		if ($_REQUEST["ticket"] != "") {
			$recipients[] = "ticket+" . $_REQUEST["ticket"] . "@helpdesk.junge-piraten.de";
		}

		foreach ($recipients as $rec) {
			mail($rec, "Kostenstelle {$_REQUEST["name"]} angelegt", <<<EOT
Hey,

deine Kostenstelle {$_REQUEST["name"]} wurde bewilligt und unter der Nummer {$code} angelegt.
Du kannst diese Daten jetzt benutzen, um Ausgaben für dieses Projekt zu bewilligen. Wie
das genau geht, kannst du im Wiki [1] nachlesen.

Für alle Fragen stehen wir dir natürlich gerne zur Seite, melde dich einfach per Mail :)

Viele Grüße,

1: https://wiki.junge-piraten.de/wiki/Finanzen/Ausgaben
EOT
, "From: <kostenstellen@junge-piraten.de>\r\nContent-Type: text/plain; charset=utf8");
		}

		databaseUnlock($year);
		$result = array("status" => "ok", "guid" => $guid, "code" => $code);
	} catch (Exception $e) {
		$result = array("status" => "fail", "message" => $e->getMessage());
	}
	die(json_encode($result));
}

include("templates/kostenstellen.php");
