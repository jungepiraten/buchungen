<?php

require_once("sql.inc.php");
require_once("transaction.inc.php");
require_once("login.inc.php");
loginRequire();

$guid = $_POST["guid"];
$allowed = false;

$transaction = getTransaction($guid);
foreach ($transaction["splits"] as $split) {
	if (isAllowedAccount($split["account_guid"])) {
		$allowed = true;
	}
}

header("Content-Type: application/json; charset=utf-8");
if ($allowed === false) {
	print(json_encode(array("FORBIDDEN")));
} else {
	print(json_encode($transaction));
}
