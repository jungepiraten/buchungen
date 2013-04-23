<?php

require_once("sql.inc.php");
require_once("login.inc.php");
require_once("transaction.inc.php");
loginRequire();

$title = "";

if (isset($_REQUEST["account_guid"])) {
	$account = sqlGetAccount($_REQUEST["account_guid"]);
}

$accounts = array();
$result = $sql->query("select parent_guid, guid, code, name, placeholder, hidden from accounts where hidden = 0 order by code");
while ($acc = $result->fetch_assoc()) {
	$acc = formatAccount($acc);
	$accounts[] = $acc;
}

include("templates/transactions.php");
