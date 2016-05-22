#!/usr/bin/php
<?php

if (count($_SERVER["argv"]) != 2) {
	print("Please specify year to create as first arg");
	exit;
}

chdir(dirname(__FILE__) . "/..");
require_once("config.inc.php");

// Hole informationen aus dem alten Jahr
$year = $_SERVER["argv"][1] - 1;

require_once("sql.inc.php");
require_once("kassenbuch.inc.php");

list($accounts, $accounts_code2guid, $journal, $nums, $partners) = getKassenbuch(true);

$bilanz_splits = array();

// Finanzkonten
$cats = array("aktiva" => array(), "passiva" => array());
$sums = array_combine(array_keys($cats), array_fill(0, count($cats), $gesamt));
$assignment = array("F0" => "bilanz", "F1" => "bilanz");
$erfolg = 0;
foreach ($accounts as $account) {
	if (in_array(substr($account["code"],0,2), array("F0","F1")) && $account["saldo"] != 0) {
		$bilanz_splits[] = array(
			"code" => $account["code"],
			"value" => $account["saldo"],
		);
	}
	if (in_array(substr($account["code"],0,2), array("F2","F3","F4","F5","F6","F7","F8"))) {
		$gewinn += $account["saldo"];
	}
}
$bilanz_splits[] = array(
	"code" => "F1080",
	"label" => "Jahresergebnis",
	"value" => $gewinn,
);

// Kreditoren & Debitoren
$ps = array("K" => 0, "D" => 0);
foreach ($partners as $partner => $info) {
	$sums = array("soll"=>0,"haben"=>0);
	foreach ($info["lots"] as $lot => $transactions) {
		$op = array_sum(array_map(create_function('$tx', 'return $tx["split"]["value"];'), $transactions));
		if ($op != 0) {
			$bilanz_splits[] = array(
				"code" => $partner,
				"label" => "Übertrag [" . $lot . "]",
				"value" => $op,
			);
			$ps[substr($partner,0,1)] += $op;
		}
	}
}
$bilanz_splits[] = array(
	"code" => "K",
	"value" => $ps["K"] * (-1),
);
$bilanz_splits[] = array(
	"code" => "D",
	"value" => $ps["D"] * (-1),
);

// Eigenkapital
$gliederungen = array();
$eigenkapital = 0;
foreach ($accounts[$accounts_code2guid["E"]]["subAccounts"] as $code => $guid) {
	$c = substr($accounts[$guid]["code"], 1);
	$gliederungen[] = array(
		"code" => $c,
		"label" => $accounts[$guid]["label"],
	);
	$bilanz_splits[] = array(
		"code" => "E".$c,
		"value" => $accounts[$accounts_code2guid["E".$c]]["saldo"] + $accounts[$accounts_code2guid["R".$c]]["saldo"],
	);
	$eigenkapital += $accounts[$accounts_code2guid["E".$c]]["saldo"] + $accounts[$accounts_code2guid["R".$c]]["saldo"];
}
$bilanz_splits[] = array(
	"code" => "E",
	"value" => $eigenkapital * (-1),
);

// Zusätzliche Kostenstellen für Mitgliedsbeitraege Bund
$mb_ksts = array();
foreach ($accounts[$accounts_code2guid["R0101"]]["subAccounts"] as $code => $guid) {
	// 01 und 02 sind normale Kostenstellen
	if (intval(substr($code,-2)) > 2) {
		$mb_ksts[] = array(
			"code" => $code,
			"label" => $accounts[$guid]["label"],
		);
	}
}

// Speichere Informationen in neuem Jahr
$year = $_SERVER["argv"][1];

$sql = new mysqli(MYSQLHOST, MYSQLUSER, MYSQLPASS);
$sql->set_charset("utf-8");

$sql->query("CREATE DATABASE `".$sql->real_escape_string(MYSQLPREFIX_GNUCASH . $year)."`;");
$sql->select_db(MYSQLPREFIX_GNUCASH . $year);
$sql->multi_query(file_get_contents("database.sql"));
do { if ($r = $sql->store_result()) $r->free(); } while ($sql->next_result());

// Kontenrahmen
$fp = fopen("kontenrahmen.csv", "r");
while (list($kto, $label, $type) = fgetcsv($fp, 4096)) {
	sqlAddAccount(md5($year . "F" . $kto), "26608e50d05429ad798e286b8b71201a", $type, "F".$kto, $kto." ".$label, "");
}
fclose($fp);

// Gliederungen anlegen
foreach ($gliederungen as $gliederung) {
	sqlAddAccount(md5($year . "E" . $gliederung["code"]), "6af64b6de64658e846da4e23fc06bf42", "EQUITY", "E".$gliederung["code"], $gliederung["code"]." ".$gliederung["label"], "");
	sqlAddAccount(md5($year . "R" . $gliederung["code"]), "76c53ef584c5fc41884db195e73cca7e", "EQUITY", "R".$gliederung["code"], $gliederung["code"]." ".$gliederung["label"], "");
	sqlAddAccount(md5($year . "R" . $gliederung["code"] . "01"), md5($year . "R" . $gliederung["code"]), "EQUITY", "R".$gliederung["code"]."01", "01 Mitgliedsbeiträge", "");
	sqlAddAccount(md5($year . "R" . $gliederung["code"] . "0101"), md5($year . "R" . $gliederung["code"] . "01"), "EQUITY", "R".$gliederung["code"]."0101", "01 Ordentlich", "");
	sqlAddAccount(md5($year . "R" . $gliederung["code"] . "0102"), md5($year . "R" . $gliederung["code"] . "01"), "EQUITY", "R".$gliederung["code"]."0102", "02 Förder", "");
	sqlAddAccount(md5($year . "R" . $gliederung["code"] . "11"), md5($year . "R" . $gliederung["code"]), "EQUITY", "R".$gliederung["code"]."11", "11 Spenden", "");
}

// Sonderkostenstellen für bund
$skst = array(
	array("31", "", "Verwaltung"),
		array("01", "31", "Kontoführungsgebühr"),
		array("02", "31", "Lastschriftgebühren"),
);

foreach ($mb_ksts as $mb_kst) {
	$skst[] = array(substr($mb_kst["code"], -2), "01", $mb_kst["label"]);
}

foreach ($skst as $ks) {
	sqlAddAccount(md5($year . "R01" . $ks[1] . $ks[0]), md5($year . "R01" . $ks[1]), "EQUITY", "R01" . $ks[1] . $ks[0], $ks[0] . " " . $ks[2], "");
}


// Partner uebertragen
$partner_parents = array("K" => "3e88f28388aab619a9fd6d4f9264758b", "D" => "98d2d35a07fbf3038f3d57c96f4c7d22", "E" => "6af64b6de64658e846da4e23fc06bf42");
$partner_types = array("K" => "PAYABLE", "D" => "RECEIVABLE");
foreach ($partners as $partner => $info) {
	sqlAddAccount(md5($year . $partner), $partner_parents[substr($partner,0,1)], $partner_types[substr($partner,0,1)], $partner, substr($partner,1)." ".$info["account"]["label"], "");
}

// Bilanz uebertragen
$guid = md5($year."Bilanz");
sqlAddTransaction($guid, "B" . ($year - 1), mktime(0,0,0,1,1,$year), "Eröffnungsbilanz");
sqlAddSplits($guid, array_map(create_function('$split', 'global $year, $partner_parents; return array("account_guid" => isset($partner_parents[$split["code"]]) ? $partner_parents[$split["code"]] : md5($year . $split["code"]), "memo" => $split["label"], "value" => $split["value"]);'), $bilanz_splits));

?>
