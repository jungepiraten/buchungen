#!/usr/bin/php
<?php

if (count($_SERVER["argv"]) > 1) {
	$_REQUEST["year"] = $_SERVER["argv"][1];
}

chdir(dirname(__FILE__) . "/..");
require_once("sql.inc.php");
require_once("lock.inc.php");
require_once("kassenbuch.inc.php");

list($accounts, $accounts_code2guid, $journal, $nums, $partners) = getKassenbuch(true);

$prefixes = array(
	"D200" => "DS",
	"K100" => "RE",
	"K200" => "RS",
	"K300" => "ER",
);

foreach ($partners as $account_code => $partner) {
	if (isset($prefixes[substr($account_code,0,4)])) {
		$numPrefix = $prefixes[substr($account_code,0,4)] . substr($account_code, 4, 2) . "_";
		foreach ($partner["lots"] as $lot => $txs) {
			$good_txs = 0;
			foreach ($txs as $data) {
				$num = $data["tx"]["num"];
				if (strpos($num, $numPrefix) === 0) {
					$good_txs++;
					$dok = json_decode(file_get_contents("http://vpanel.intern.junge-piraten.de/_export/dokumentdetails.php?identifier=BGS_F".$year."_".$num));
					if (count($dok) != 1) {
						print($num . " matches ".count($dok)." documents" . "\n");
					} else if (strpos(reset($dok)->label, $lot."") === false) {
						print($num . " does not match lot \"" . $lot . "\" (" . $account_code . ") [".reset($dok)->label."]" . "\n");
					}
				} else if (in_array(substr($num, 0, 2), $prefixes)) {
					print($num . " is in " . $account_code . "\n");
				}
			}
			if ($good_txs < 1) {
//				print("\"" . $lot . "\" (" . $account_code . ") has no valid transaction." . "\n");
			}
		}
	}
}

$prefixes_flip = array_flip($prefixes);
$numCats = array();

foreach ($nums as $num => $n) {
	$dok = json_decode(file_get_contents("http://vpanel.intern.junge-piraten.de/_export/dokumentdetails.php?identifier=BGS_F".$year."_".$num));
	if (count($dok) != 1) {
		print($num . " matches ".count($dok)." documents" . "\n");
		$dok = null;
	} else {
		$dok = reset($dok);
	}

	if (in_array(substr($num,0,2), array("RE", "RS", "ER", "LB"))) {
		if (count($n["transactions"]) != 1) {
			print($num . " has " . count($n["transactions"]) . " Transactions!" . "\n");
		} else {
			$tx = $journal[reset($n["transactions"])];
			$account_code = $prefixes_flip[substr($num,0,2)] . substr($num,2,2);
			$partner_label = $partners[$account_code]["account"]["label"];

			if ($dok != null && isset($prefixes_flip[substr($num,0,2)]) && strpos($dok->label, $partner_label) === false) {
				print($num . " does not contain \"".$partner_label."\" in document label (\"".$dok->label."\")" .  "\n");
			}
		}
		$numCats[substr($num,0,4)][] = substr($num,5,2);
	}
}

foreach ($numCats as $numCat => $ns) {
	$max = max($ns);
	for ($i = 1; $i <= $max; $i++) {
		if (!in_array(str_pad($i, -2, "0"), $ns)) {
			$num = sprintf("%s_%02d", $numCat, $i);
			
			print($num . " missing"."\n");
		}
	}
}
