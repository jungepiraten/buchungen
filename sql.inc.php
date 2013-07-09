<?php

require_once("config.inc.php");

if (!isset($year)) {
	$year = 2013;
}
$database = MYSQLPREFIX_GNUCASH . $year;
$sql = new mysqli(MYSQLHOST, MYSQLUSER, MYSQLPASS, $database);
$sql->set_charset("utf-8");

function isAllowedAccount($account) {
	global $sql, $auth;

	if (!is_array($account)) {
		$account = sqlGetAccount($account);
	}

	if ($auth == false) {
		return false;
	}
	if (!is_array($auth["accountPrefixes"])) {
		return false;
	}
	foreach ($auth["accountPrefixes"] as  $prefix) {
		if (substr($account["code"],0,strlen($prefix)) == $prefix) {
			return true;
		}
	}
	return false;
}

$cache_accounts = array();
function sqlGetAccount($guid) {
	global $sql, $cache_accounts;

	if (! isset($cache_accounts[$guid])) {
		$cache_accounts[$guid] = formatAccount($sql->query("select guid, code, name, parent_guid, description, placeholder, hidden from accounts where guid = '".$sql->real_escape_string($guid)."'")->fetch_assoc());
	}
	return $cache_accounts[$guid];
}

function formatAccount($account) {
	foreach ($account as &$value) {
		$value = iconv("iso-8859-1","utf-8",$value);
	}
	$account["label"] = formatAccountName($account["name"]);
	return $account;
}

function formatTransaction($transaction) {
	foreach (array("description") as $key) {
		if (isset($transaction[$key])) {
			$transaction[$key] = iconv("iso-8859-1","utf-8",$transaction[$key]);
		}
	}
	if (isset($transaction["account_name"])) {
		$transaction["account_label"] = formatAccountName($transaction["account_name"]);
	}
	$transaction["num"] = formatNum($transaction["num"]);
	return $transaction;
}

function formatSplit($split) {
	foreach ($split as &$value) {
		$value = iconv("iso-8859-1","utf-8",$value);
	}
	$split["account_label"] = formatAccountName($split["account_name"]);
	return $split;
}

function formatAccountName($name) {
	return intval(trim(substr($name,0,2))) == 0 ? $name : trim(substr($name,2));
}

function formatNum($num) {
	if (strlen($num) != 0) {
		if (substr($num,0,2) == "BP")
			$num = "BP" . str_pad(substr($num,2),2,"0",STR_PAD_LEFT);
		else if (substr($num,0,2) == "BS")
			$num = "BS" . str_pad(substr($num,2),2,"0",STR_PAD_LEFT);
		else if (substr($num,0,2) == "GK")
			$num = "GK" . str_pad(substr($num,2),2,"0",STR_PAD_LEFT);
		else if (substr($num,0,2) == "PP")
			$num = "PP" . str_pad(substr($num,2),2,"0",STR_PAD_LEFT);
		else if (substr($num,0,1) == "B")
			$num = "B" . str_pad(substr($num,1),4,"0",STR_PAD_LEFT);
	}
	return $num;
}
