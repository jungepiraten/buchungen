<?php

require_once("sql.inc.php");
require_once("transaction.inc.php");
require_once("login.inc.php");
loginRequire("verifyTransaction");

$guid = $_POST["guid"];

$transaction = getTransaction($guid);
foreach ($transaction["splits"] as $split) {
	if (!isAllowedAccount($split["account_guid"])) {
		die("NOT_ALLOWED");
	}
}

$sql->query("delete from validations where guid_tx = '".$sql->real_escape_string($guid)."' and username = '".$sql->real_escape_string($auth["user"])."'");

header("Content-Type: application/json; charset=utf-8");
print(json_encode(array("transaction" => getTransaction($guid))));
