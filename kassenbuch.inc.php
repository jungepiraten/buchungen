<?php

require_once("sql.inc.php");

function getKassenbuch($ignorePermissions = false) {
	global $sql;

	$accounts = array();
	$accounts_code2guid = array();
	$result = $sql->query("select parent_guid, account_type as type, guid, code, name, placeholder, hidden, description from accounts order by code");
	while ($acc = $result->fetch_assoc()) {
		$acc = formatAccount($acc);
		$acc["subAccounts"] = array();
		$acc["transactions"] = array();
		$acc["soll"] = 0;
		$acc["haben"] = 0;
		$acc["saldo"] = 0;
		$acc["saldoSign"] = in_array($acc["type"], array("EXPENSE","ASSET","BANK","RECEIVABLE")) ? 1 : -1;
		$acc["level"] = $acc["parent_guid"] ? $accounts[$acc["parent_guid"]]["level"] + 1 : 0;
		if ($acc["parent_guid"]) {
			$accounts[$acc["parent_guid"]]["subAccounts"][$acc["code"]] = $acc["guid"];
		}
		if ($acc["code"]) {
			$accounts_code2guid[$acc["code"]] = $acc["guid"];
		}
		$accounts[$acc["guid"]] = $acc;
	}

	$i = 0;

	$transactions = array();
	$journal = array();
	$nums = array();
	$partners = array();
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
				$account_code = $accounts[$split["account_guid"]]["code"];
				if (preg_match('/^(K|D)\\d+/', $account_code) && preg_match('/\\[(.*?)\\]/', $transaction["description"] . $split["memo"], $m)) {
					$lot = $m[1];
					if (!isset($partners[$account_code])) {
						$partners[$account_code] = array("account" => $accounts[$split["account_guid"]], "lots" => array());
					}
					if (!isset($partners[$account_code]["lots"][$lot])) {
						$partners[$account_code]["lots"][$lot] = array();
					}
					$partners[$account_code]["lots"][$lot][] = array("tx" => $transaction, "split" => $split);
				}
				$splits[] = $split;
			}
			if (!empty($splits)) {
				$transaction["splits"] = $splits;
				$journal[] = $transaction;
			}
		}
	}
	ksort($nums);
	ksort($partners);

	return array($accounts, $accounts_code2guid, $journal, $nums, $partners);
}

function getBelegkreisDescription($a) {
	global $accounts, $accounts_code2guid;

	if ($a == "")
		return "Ohne Beleg";
	if ($a == "B")
		return "Jahresanfangsbilanz";
	if ($a == "BEIO")
		return "Beitragsordnungen des Jahres";
	if (substr($a,0,2) == "BK")
		return "Barkassenabrechnung " . $accounts[$accounts_code2guid["F09".(20+substr($a,2))]]["label"];
	if (substr($a,0,2) == "GH")
		return "Kontoauszug " . $accounts[$accounts_code2guid["F09".(50+substr($a,2))]]["label"];
	if (substr($a,0,2) == "KK")
		return "Kreditkartenabrechnung " . $accounts[$accounts_code2guid["F09".(80+substr($a,2))]]["label"];
	if (substr($a,0,2) == "LB")
		return "Lastschriftenberichte " . $accounts[$accounts_code2guid["F09".(50+substr($a,2))]]["label"];
	if (substr($a,0,2) == "RE")
		return "Eingehende Rechnungen " . $accounts[$accounts_code2guid["K100".substr($a,2,2)]]["label"];
	if (substr($a,0,2) == "RS")
		return "Rechnungen Sammelkreditor " . $accounts[$accounts_code2guid["K200".substr($a,2,2)]]["label"];
	if (substr($a,0,2) == "ER")
		return $accounts[$accounts_code2guid["K300".substr($a,2,2)]]["label"];
	return "";
}

?>
