<?php

require_once("sql.inc.php");
require_once("login.inc.php");
require_once("transaction.inc.php");
require_once("kassenbuch.inc.php");
loginRequire();

list($accounts, $accounts_code2guid, $journal, $nums, $partners) = getKassenbuch();

$list = array();
foreach ($partners as $partner => $info) {
	$list[$partner] = array(
		"label" => $info["account"]["label"],
		"lots" => array_map(create_function('$lot', 'return "".$lot;'), array_keys($info["lots"]))
	);
}

header("Content-Type: application/json");
print(json_encode($list));
