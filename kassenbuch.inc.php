<?php

require_once("sql.inc.php");

function getKassenbuch($respectOnlyPrefixes = array(), $ignorePermissions = false) {
	global $sql;

	$accounts = array();
	$accounts_code2guid = array();
	$result = $sql->query("select parent_guid, account_type as type, guid, code, name, placeholder, hidden, description from accounts order by code");
	while ($acc = $result->fetch_assoc()) {
		$acc = formatAccount($acc);
		$acc["hide"] = !empty($respectOnlyPrefixes);
		foreach ($respectOnlyPrefixes as $prefix) {
			if (substr($acc["code"],0,strlen($prefix)) == $prefix && strlen($acc["code"]) >= 2) {
				$acc["hide"] = false;
			}
		}
		$acc["subAccounts"] = array();
		$acc["transactions"] = array();
		$acc["soll"] = 0;
		$acc["haben"] = 0;
		$acc["saldo"] = 0;
		$acc["saldoSign"] = in_array($acc["type"], array("EXPENSE","ASSET","BANK","RECEIVABLE")) ? 1 : -1;
		$accounts[$acc["guid"]] = $acc;
		if ($acc["parent_guid"]) {
			$accounts[$acc["parent_guid"]]["subAccounts"][$acc["code"]] = $acc["guid"];
		}
		if ($acc["code"]) {
			$accounts_code2guid[$acc["code"]] = $acc["guid"];
		}
	}

	$i = 0;

	$transactions = array();
	$journal = array();
	$nums = array();
	$result = $sql->query("select guid as guid from transactions order by post_date asc, num desc");
	while ($row = $result->fetch_assoc()) {
		$transaction = sqlGetTransaction($row["guid"]);

		$allowed = $ignorePermissions;
		if (!$allowed) {
			foreach ($transaction["splits"] as $split) {
				if (isAllowedAccount($accounts[$split["account_guid"]])) {
					$allowed = true;
				}
			}
		}

		if ($allowed) {
			$transaction["id"] = ++$i;

			if (!isset($nums[$transaction["num"]])) {
				$nums[$transaction["num"]] = array("transactions" => array());
			}
			$nums[$transaction["num"]]["transactions"][] = $transaction["id"];

			$account_guids = array();
			$splits = array();
			foreach ($transaction["splits"] as $split) {
				if (! $accounts[$split["account_guid"]]["hide"]) {
					$saldoAccount = $split["account_guid"];
					while ($saldoAccount) {
						$accounts[$saldoAccount]["soll"]  += ($split["value"] > 0 ? $split["value"] : 0);
						$accounts[$saldoAccount]["haben"] += ($split["value"] < 0 ? (-1)*$split["value"] : 0);
						$accounts[$saldoAccount]["saldo"] +=  $split["value"];
						$saldoAccount = $accounts[$saldoAccount]["parent_guid"];
					}
					if (!in_array($split["account_guid"], $account_guids)) {
						$accounts[$split["account_guid"]]["transactions"][] = $transaction;
						$account_guids[] = $split["account_guid"];
					}
					$splits[] = $split;
				}
			}
			if (!empty($splits)) {
				$transaction["splits"] = $splits;
				$journal[] = $transaction;
			}
		}
	}
	ksort($nums);

	return array($accounts, $accounts_code2guid, $journal, $nums);
}

function getBelegkreisDescription($a) {
	global $accounts, $accounts_code2guid;

	if ($a == "B")
		return "Jahresanfangs und -abschlussbilanz";
	if ($a == "BEIO")
		return "Beitragsordnungen des Jahres";
	if (substr($a,0,2) == "BK")
		return "Barkassenabrechnung " . $accounts[$accounts_code2guid["13100-".substr($a,2)]]["label"];
	if (substr($a,0,2) == "GH")
		return "Kontoauszug " . $accounts[$accounts_code2guid["13200-".substr($a,2)]]["label"];
	if (substr($a,0,2) == "KK")
		return "Kreditkartenabrechnung " . $accounts[$accounts_code2guid["23100-".substr($a,2)]]["label"];
	if (substr($a,0,2) == "LB")
		return "Lastschriftenberichte " . $accounts[$accounts_code2guid["13200-".substr($a,2)]]["label"];
	if (substr($a,0,2) == "RE")
		return "Eingehende Rechnungen " . $accounts[$accounts_code2guid["23200-".substr($a,2)]]["label"];
	return "";
}

?>
