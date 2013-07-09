<?php

require_once("sql.inc.php");
require_once("login.inc.php");
require_once("transaction.inc.php");
loginRequire();

function matchFilter($transaction, $filter) {
	switch ($filter["type"]) {
	case "daterange":
		return ($filter["start"] <= $transaction["date"]) && ($filter["end"] >= $transaction["date"]);
	case "account":
		return in_array($filter["guid"], array_map(create_function('$a','return $a["account_guid"];'), $transaction["splits"]));
	case "num":
		if (isset($filter["num"]))
			return $transaction["num"] == formatNum($filter["num"]);
		else
			return !empty($transaction["num"]);
	case "accountCodeStartsWith":
		return array_reduce($transaction["splits"], create_function('$a,$b','return $a || substr($b["account_code"],0,'.strlen($filter["prefix"]).') == "'.intval($filter["prefix"]).'";'), false);
	case "descStartsWith":
		return (substr($transaction["description"],0,strlen($filter["prefix"])) == $filter["prefix"]);
	case "verifiedAbove":
		return $transaction["validValidations"] > $filter["count"];
	case "failedVerificationsAbove":
		return (count($transaction["validations"]) - $transaction["validValidations"]) > $filter["count"];
	case "and":
		return !in_array(false, array_map("matchFilter", array_fill(0, count($filter["conds"]), $transaction), $filter["conds"]));
	case "or":
		return in_array(true, array_map("matchFilter", array_fill(0, count($filter["conds"]), $transaction), $filter["conds"]));
	case "not":
		return !matchFilter($transaction, $filter["cond"]);
	case "true":
		return true;
	default:
	case "false":
		return false;
	}
}

$i = $offset = (isset($_REQUEST["offset"]) ? intval($_REQUEST["offset"]) : 0);
$sorting_field = "post_date";
$sorting_order = "asc";
if (isset($_REQUEST["sorting"])) {
	if (in_array($_REQUEST["sorting"]["field"], array("post_date","num"))) {
		$sorting_field = $_REQUEST["sorting"]["field"];
	}
	if (in_array($_REQUEST["sorting"]["order"], array("asc","desc"))) {
		$sorting_order = $_REQUEST["sorting"]["order"];
	}
}

$transactions = array();
$result = $sql->query("select guid as guid from transactions order by " . $sorting_field . " " . $sorting_order . " limit " . $offset . ",100");
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
print(json_encode(array("transactions" => $transactions, "num_rows" => $result->num_rows, "nextOffset" => ( ($result->num_rows >= 100 || $offset + $result->num_rows > $i) ? $i : null))));
