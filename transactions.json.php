<?php

require_once("sql.inc.php");
require_once("login.inc.php");
require_once("transaction.inc.php");
loginRequire();

function matchFilter($transaction, $filter) {
	return true;
	switch ($filter->type) {
	case "and":
		return !in_array(false, array_map("matchFilter", $filter->conds));
	case "or":
		return in_array(true, array_map("matchFilter", $filter->conds));
	case "not":
		return !$this->matchFilter($filter->cond);
	case 
	}
}

$i = $offset = (isset($_REQUEST["offset"]) ? intval($_REQUEST["offset"]) : 0);

$transactions = array();
$result = $sql->query("select guid as guid from transactions order by post_date limit " . $offset . ",100");
while (($row = $result->fetch_assoc()) && count($transactions) < 20) {
	$transaction = getTransaction($row["guid"]);
	$allowed = false;
	foreach ($transaction["splits"] as $split) {
		if (isAllowedAccount($split["account_guid"])) {
			$allowed = true;
		}
	}
	if ($allowed) {
		if (!isset($_REQUEST["filter"]) || matchFilter($transaction, $_REQUEST["filter"])) {
			$transactions[] = $transaction;
		}
	}

	$i++;
}

header("Content-Type: application/json; charset=utf-8");
print(json_encode(array("transactions" => $transactions, "nextOffset" => $i)));
