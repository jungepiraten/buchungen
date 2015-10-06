<?php

$target = $_SERVER["argv"][1];

if (!is_numeric($target) && is_numeric($_GET["value"])) {
	$target = $_GET["value"];
}

chdir(dirname(__FILE__) . "/..");
require_once("sql.inc.php");
require_once("lock.inc.php");
require_once("kassenbuch.inc.php");

if (databaseIsLocked($year)) {
	exit;
}
databaseLock($year, basename(__FILE__), "localhost");

list($accounts, $accounts_code2guid, $journal, $nums, $partners) = getKassenbuch(true);

$aim = array();

foreach ($partners as $num => $partner) {
	foreach ($partner["lots"] as $label => $lot) {
		$sum = array_sum(array_map(create_function('$l', 'return $l["split"]["value"];'), $lot));
		if ($sum == $target) {
			$aim[] = array("kreditor" => $num, "kreditorname" => $partner["account"]["label"], "vorgang" => $label);
		}
	}
}

databaseUnlock($year);

if (count($aim) == 1) {
	print(json_encode(array_shift($aim)));
} else {
	exit(1);
}
