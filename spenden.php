<?php

require_once("sql.inc.php");
require_once("login.inc.php");
loginRequire();

$transactions = array();
$result = $sql->query("select guid as guid from transactions where description like '%spende%' order by post_date");
while ($row = $result->fetch_assoc()) {
	$transaction = sqlGetTransaction($row["guid"]);

	$identifier = null;

	preg_match('~\\[(.*)\\]~', $transaction["description"], $m);
	if (isset($m[1])) {
		$identifier = $m[1];
	}
	
	if (!isset($identifier)) {
		preg_match('~[A-Z]{1,2}#([0-9]+)~', $transaction["description"], $m);
		if (isset($m[1])) {
			$identifier = "#" . $m[1];
		}
	}

	if (isset($identifier)) {
		$transactions[$identifier][] = $transaction;
	} else {
#		var_dump($transaction);
	}
}

$spendenkonten = array("ea8d4718a2bb1a27738eccd771512775", "152e7a7cff14e4039c62f76dec027978", "281e9c206d1c5b8139276bafecc15ab3", "4c320002f54a6584ca6bc08b729f7405", "5a0f343484b7b032dd49bf100c7c06b0", "6657eeac7403e568f63c1283250e8f40", "1a23789215829346907c4d2dd7649b6d", "e62a8dcc90ae44be5b22be9211df0b8a", "f4b78b180eb0172f96145dd61244feae", "ce3e44633645ded2c8f180c6889e7962", "69da5f86428dd8afa3c325d305f4da75", "a3e3add5877fff37359ea2da232559bd", "c587a1ac1d3d4c4ea61d06d589c08143", "dbb2021e07eddbeabd643971d34cdbd4", "7c12c6f83e43dbf9a6f63a6b2d5b17bf");

#header("Content-Type: text/csv; charset=utf-8");
foreach ($transactions as $identifier => $ts) {
	foreach ($ts as $t) {
		$b = 0;
		foreach ($t["splits"] as $s) {
			if (in_array($s["account_guid"], $spendenkonten)) {
				$b -= $s["value"];
			}
		}
		print(date("d.m.Y", $t["date"]) . ";" . $identifier . ";" . $b . ";" . $t["description"] . "\r\n");
	}
}
