<?php

require_once("sql.inc.php");
require_once("login.inc.php");
require_once("kassenbuch.inc.php");
loginRequire();

list($accounts, $accounts_code2guid, $journal, $nums, $partners) = getKassenbuch();

$stats = array();

$lastdate = strtotime("2014-01-01");

foreach ($journal as $buchung) {
	if (substr($buchung["description"],0,12) == "Mitgliedsbei") {
		for ($d = $lastdate; $d < $buchung["date"]; $d += 24*60*60) {
			$statid = date("Y-m-d", $d);
			if (!isset($stats[$statid])) {
				$stats[$statid] = 0;
			}
		}
		$statid = date("Y-m-d", $buchung["date"]);
		if (!isset($stats[$statid])) {
			$stats[$statid] = 0;
		}
		$stats[$statid]++;
		$lastdate = $buchung["date"];
	}
}

ksort($stats);
header("Content-Type: text/csv; filename=stats-mb.csv");
#header("Content-Type: text/plain");
print("Datum;Einzahlungen\r\n");
foreach($stats as $datum => $count) {
	print($datum . ";" . $count . "\r\n");
}
