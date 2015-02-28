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

$buchungen = array();
$lastschriften = array();

foreach ($journal as $transaction) {
	foreach ($transaction["splits"] as $split) {
		$account_code = $accounts[$split["account_guid"]]["code"];
		if (preg_match('/^(K|D)\\d+/', $account_code) && preg_match('/\\[(.*?)\\].*\\{(.*?)\\}$/', $transaction["description"] . $split["memo"], $m)) {
			$kst = array_reduce($transaction["splits"], create_function('$k,$s', 'return (substr($s["account_code"],0,1)=="R" && strlen($s["account_code"]) > 1) ? $k." ".$s["account_code"] : $k;'), "");
			$vermerk = substr($transaction["guid"],0,8) . " " . $m[1] . " " . ltrim($kst) . " " . $account_code;

			$f = array_combine(
				array_map(create_function('$r', 'list($k,$v)=explode(":",$r,2);return strtolower($k);'), explode(" +", substr($m[2],1))),
				array_map(create_function('$r', 'list($k,$v)=explode(":",$r,2);return $v;'), explode(" +", substr($m[2],1)))
			);
			if (isset($f["iban"])) {
				if ($split["value"] < 0) {
					$buchungen[] = array(
						"inhaber" => $f["inhaber"],
						"iban" => $f["iban"],
						"bic" => $f["bic"],
						"vermerk" => $vermerk,
						"value" => sprintf("%.2f EUR", (-1) * $split["value"] / 100),
					);
					sqlSetTransaction($transaction["guid"], $transaction["num"], $transaction["date"], str_replace(" {".$m[2]."}", "", $transaction["description"]));
				} else if ($split["value"] > 0 && isset($f["mandat"])) {
					$lastschriften[] = array(
						"inhaber" => $f["inhaber"],
						"iban" => $f["iban"],
						"bic" => $f["bic"],
						"mandatsreferenz" => $f["mandat"],
						"vermerk" => $vermerk,
						"value" => sprintf("%.2f EUR", $split["value"] / 100),
					);
					sqlSetTransaction($transaction["guid"], $transaction["num"], $transaction["date"], str_replace(" {".$m[2]."}", "", $transaction["description"]));
				}
			}
		}
	}
}

foreach ($buchungen as $buchung) {
	mail("schatzmeister@junge-piraten.de", "Ueberweisung", <<<EOT
Kontoinhaber:
	{$buchung["inhaber"]}
IBAN:
	{$buchung["iban"]}
BIC:
	{$buchung["bic"]}
Betrag:
	{$buchung["value"]}
Verwendungszweck:
	{$buchung["vermerk"]}
EOT
, "From: schatzmeister@junge-piraten.de");
}

foreach ($lastschriften as $lastschrift) {
	mail("schatzmeister@junge-piraten.de", "Lastschrift", <<<EOT
Kontoinhaber:
	{$lastschrift["inhaber"]}
IBAN:
	{$lastschrift["iban"]}
BIC:
	{$lastschrift["bic"]}
Mandatsreferenz:
	{$lastschrift["mandatsreferenz"]}
Betrag:
	{$lastschrift["value"]}
Verwendungszweck:
	{$lastschrift["vermerk"]}
EOT
, "From: schatzmeister@junge-piraten.de");
}

databaseUnlock($year);
