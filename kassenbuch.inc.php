<?php

require_once("transaction.inc.php");

function getKassenbuch() {
	global $sql;

	$accounts = array();
	$result = $sql->query("select parent_guid, guid, code, name, placeholder, hidden, description from accounts order by code");
	while ($acc = $result->fetch_assoc()) {
		$acc = formatAccount($acc);
		$acc["subAccounts"] = array();
		$acc["saldo"] = 0;
		$accounts[$acc["guid"]] = $acc;
		if ($acc["parent_guid"]) {
			$accounts[$acc["parent_guid"]]["subAccounts"][$acc["code"]] = $acc["guid"];
		}
	}

	$i = 0;

	$transactions = array();
	$journal = array();
	$result = $sql->query("select guid as guid from transactions order by post_date asc");
	while ($row = $result->fetch_assoc()) {
		$transaction = getTransaction($row["guid"]);

		$allowed = false;
		foreach ($transaction["splits"] as $split) {
			if (isAllowedAccount($split["account_guid"])) {
				$allowed = true;
			}
		}

		if ($allowed) {
			$transaction["id"] = ++$i;

			$journal[] = $transaction;
			$account_guids = array();
			foreach ($transaction["splits"] as $split) {
				$saldoAccount = $split["account_guid"];
				while ($saldoAccount) {
					$accounts[$saldoAccount]["saldo"] += $split["value"];
					$saldoAccount = $accounts[$saldoAccount]["parent_guid"];
				}
				if (!in_array($split["account_guid"], $account_guids)) {
					$accounts[$split["account_guid"]]["transactions"][] = $transaction;
					$account_guids[] = $split["account_guid"];
				}
			}
		}
	}

	return array($accounts, $journal);
}

?>
