<?php

require_once("sql.inc.php");

function getKassenbuch($ignorePermissions = false) {
	global $sql;

	$accounts = array();
	$accounts_code2guid = array();
	$result = $sql->query("select parent_guid, guid, code, name, placeholder, hidden, description from accounts order by code");
	while ($acc = $result->fetch_assoc()) {
		$acc = formatAccount($acc);
		$acc["subAccounts"] = array();
		$acc["transactions"] = array();
		$acc["soll"] = 0;
		$acc["haben"] = 0;
		$acc["saldo"] = 0;
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
	$result = $sql->query("select guid as guid from transactions order by post_date asc");
	while ($row = $result->fetch_assoc()) {
		$transaction = sqlGetTransaction($row["guid"]);

		$allowed = $ignorePermissions;
		if (!$allowed) {
			foreach ($transaction["splits"] as $split) {
				if (isAllowedAccount($split["account_guid"])) {
					$allowed = true;
				}
			}
		}

		if ($allowed) {
			$transaction["id"] = ++$i;

			$journal[] = $transaction;
			$account_guids = array();
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
			}
		}
	}

	return array($accounts, $accounts_code2guid, $journal);
}

?>
