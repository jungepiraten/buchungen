<?php

require_once("sql.inc.php");
require_once("login.inc.php");
loginRequire("verifyTransaction");

$guid = $_POST["guid"];

$transaction = sqlGetTransaction($guid);
foreach ($transaction["splits"] as $split) {
	if (!isAllowedAccount($split["account_guid"])) {
		die("NOT_ALLOWED");
	}
}

$sql->query("insert into validations (guid_tx, username, hash, timestamp) VALUES ('".$sql->real_escape_string($guid)."','".$sql->real_escape_string($auth["user"])."','".$sql->real_escape_string(verifyTransaction($auth["user"], $transaction))."', NOW())");

header("Content-Type: application/json; charset=utf-8");
print(json_encode(array("transaction" => sqlGetTransaction($guid))));

