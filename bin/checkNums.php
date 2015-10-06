#!/usr/bin/php
<?php

chdir(dirname(__FILE__) . "/..");
require_once("sql.inc.php");
require_once("lock.inc.php");
require_once("kassenbuch.inc.php");

list($accounts, $accounts_code2guid, $journal, $nums, $partners) = getKassenbuch(true);

$numCats = array();

foreach ($nums as $num => $n) {
	if (in_array(substr($num,0,2), array("RE", "RS", "ER", "LB"))) {
		if (count($n["transactions"]) != 1) {
			print($num . " has " . count($n["transactions"]) . " Transactions!" . "\n");
		} else {
			$tx = $journal[reset($n["transactions"])];
			// TODO "BGS_F".$year."_".$num aus VPanel suchen
			// TODO prüfen ob lot stimmt (aka in VPanel-Titel vorkommt)
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
