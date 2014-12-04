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

list($accounts, $accounts_code2guid, $journal, $nums, $partners) = getKassenbuch(true);

function calculateSplits($memo, $value, $verteilungen) {
	global $accounts_code2guid;

	$splits = array();
	foreach ($verteilungen as $verteilung) {
		$account_guid = $accounts_code2guid[$verteilung->konto];
		$anteil = $verteilung->anteil;
		if (substr($anteil,-1) == "%") {
			$anteil = $value * substr($anteil,0,-1) / 100;
		}
		$splits[] = array(
			"account_guid" => $account_guid,
			"memo" => $memo . " (".$verteilung->anteil.")",
			"value" => round($anteil),
			);
	}
	return $splits;
}

function processSplit($transaction, $split, $option) {
	global $accounts;

	switch ($option->type) {
		case "verteil":
			sqlReplaceSplit($split["guid"], calculateSplits($option->prefix . $split["memo"], $split["value"], $option->verteil));
			break;
		case "create":
			$timestamp = $transaction["date"] + (isset($option->date_offset) ? $option->date_offset : 0);
			$txguid = md5($transaction["guid"]);
			$description = $transaction["description"];
			$num = isset($option->num) ? $option->num : $transaction["num"];
			$splits = calculateSplits($option->prefix . $split["memo"], $split["value"], $option->verteil);
			sqlMaybeAddTransaction($txguid, $num, $timestamp, $description);
			sqlAddSplits($txguid, $splits);
			break;
		case "changeTimestamp":
			$timestamp = $transaction["date"] + $option->date_offset;
			sqlSetTransactionTimestamp($transaction["guid"], $timestamp);
			break;
	}
}

foreach ($accounts as $account) {
	$notes = sqlGetAccountNotes($account["guid"]);
	if ($notes != "") {
		$options = json_decode($notes);
		foreach ($account["transactions"] as $transaction) {
			foreach ($transaction["splits"] as $split) {
				if ($split["account_guid"] == $account["guid"]) {
					foreach ($options as $option) {
						// If older processSplits changed something on the split
						$transaction = sqlGetTransaction($transaction["guid"]);
						processSplit($transaction, $split, $option);
					}
				}
			}
		}
	}
}

databaseUnlock($year);
