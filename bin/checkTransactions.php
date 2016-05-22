#!/usr/bin/php
<?php

if (count($_SERVER["argv"]) > 1) {
	$_REQUEST["year"] = $_SERVER["argv"][1];
}

chdir(dirname(__FILE__) . "/..");
require_once("sql.inc.php");
require_once("transaction.inc.php");

$result = $sql->query("select guid as guid from transactions");
while ($row = $result->fetch_assoc()) {
	$transaction = sqlGetTransaction($row["guid"]);

	$subBooks = array("F" => 0, "R" => 0, "D" => 0, "K" => 0, "Kostenrechnung" => 0, "Debitoren" => 0, "Kreditoren" => 0);
	foreach ($transaction["splits"] as $split) {
		if (in_array(substr($split["account_code"],0,2), array("R", "F2", "F3", "F4", "F5", "F6", "F7", "F8"))) {
			$subBooks["Kostenrechnung"] += $split["value"];
		}
		if (in_array($split["account_code"], array("F1340", "F1360", "F1390", "F0630", "F0555", "K"))) {
			$subBooks["Kreditoren"] += $split["value"];
		}
		if (in_array($split["account_code"], array("F0650", "F0655", "D"))) {
			$subBooks["Debitoren"] += $split["value"];
		}
		$subBooks[substr($split["account_code"], 0, 1)] += $split["value"];
	}
	foreach ($subBooks as $book => $v) {
		if ($v != 0) {
			print("TX ".$transaction["num"]." ".date("Y-m-d", $transaction["date"])." ".$transaction["description"]." BOOK " . $book . "\n");
		}
	}
}
