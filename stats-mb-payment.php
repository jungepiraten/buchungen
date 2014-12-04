<?php

require_once("sql.inc.php");
require_once("login.inc.php");
require_once("kassenbuch.inc.php");
loginRequire();

list($accounts, $accounts_code2guid, $journal, $nums, $partners) = getKassenbuch();

$stats = array();

foreach ($journal as $buchung) {
	foreach ($buchung["splits"] as $split) {
		if (in_array($split["account_code"], array("F2110","F2120"))) {
			$n = substr($buchung["num"],0,2);
			if (!isset($stats[$n])) {
				$stats[$n] = array("count" => 0, "sum" => 0);
			}
			$stats[$n]["count"]++;
			$stats[$n]["sum"] += $split["value"];
		}
	}
}

ksort($stats);
header("Content-Type: text/csv; filename=stats-mb-payment.csv");
#header("Content-Type: text/plain");
print("Beleg;Einzahlungen;Summe\r\n");
foreach($stats as $num => $s) {
	print($num . ";" . $s["count"] . ";" . $s["sum"] . "\r\n");
}
