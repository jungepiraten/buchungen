<?php

require_once("sql.inc.php");
require_once("vpanel.inc.php");
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

$vp = new VPanel(VPANELBASE);
$vp->startSession(VPANELUSER, VPANELPASS);

$spendenkonten = array("F3221", "F3225", "F3230");

#header("Content-Type: text/csv; charset=utf-8");
foreach ($transactions as $identifier => $ts) {
	if (substr($identifier,0,1) == "#") {
		$mitglied = $vp->getMitglied(substr($identifier,1));
		$a = array(
			isset($mitglied->latest->natperson) ? $mitglied->latest->natperson->vorname . " " . $mitglied->latest->natperson->name : $mitglied->jurperson->label,
			$mitglied->latest->kontakt->adresszusatz,
			$mitglied->latest->kontakt->strasse . " " . $mitglied->latest->kontakt->hausnummer,
			$mitglied->latest->kontakt->ort->plz . " " . $mitglied->latest->kontakt->ort->label
		);
		$identifier = implode(", ", array_filter($a, create_function('$i', 'return $i != "";')));
	}

	foreach ($ts as $t) {
		$b = 0;
		foreach ($t["splits"] as $s) {
			if (in_array($s["account_code"], $spendenkonten)) {
				$b -= $s["value"];
			}
		}
		if ($b > 0) {
			print(date("d.m.Y", $t["date"]) . ";" . $identifier . ";" . $b . "\r\n");
		}
	}
}
