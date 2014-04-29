<?php

require_once("config.inc.php");

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

function sqlGetTransaction($guid) {
	global $sql;

	$transaction = formatTransaction($sql->query("select guid, num, unix_timestamp(convert_tz(post_date,\"UTC\",\"Europe/Berlin\")) as date, description from transactions where guid = '".$sql->real_escape_string($guid)."'")->fetch_assoc());
	$transaction["splits"] = array();
	$result = $sql->query("select s.guid as guid, s.memo as memo, s.reconcile_state as reconcile, a.guid as account_guid, a.code as account_code, a.name as account_name, s.value_num as value from splits s left join accounts a ON(a.guid = s.account_guid) where s.tx_guid = '".$sql->real_escape_string($guid)."' order by s.memo");
	while ($split = $result->fetch_assoc()) {
		$split = formatSplit($split);
		$transaction["splits"][] = $split;
	}
	
	$validations = array();
	$validValidations = 0;
	$result = $sql->query("select guid_tx, username, hash, timestamp from validations where guid_tx = '" . $sql->real_escape_string($guid) . "'");
	while ($validation = $result->fetch_assoc()) {	
		$validation["valid"] = isValidated($transaction, $validation);
		$validations[] = $validation;
		$validValidations += ($validation["valid"] ? 1 : 0);
	}
	$transaction["validValidations"] = $validValidations;
	$transaction["validations"] = $validations;

	return $transaction;
}

$cache_accounts = array();
function sqlGetAccount($guid) {
	global $sql, $cache_accounts;

	if (! isset($cache_accounts[$guid])) {
		$cache_accounts[$guid] = formatAccount($sql->query("select guid, code, name, parent_guid, description, placeholder, hidden from accounts where guid = '".$sql->real_escape_string($guid)."'")->fetch_assoc());
	}
	return $cache_accounts[$guid];
}

function sqlGetAccountNotes($guid) {
	global $sql;
	$result = $sql->query("select string_val from slots where obj_guid = '".$sql->real_escape_string($guid)."' and name = 'notes'");
	if ($result->num_rows > 0) {
		return $result->fetch_object()->string_val;
	}
	return "";
}

function sqlMaybeAddTransaction($guid, $num, $timestamp, $description) {
	global $sql;

	if ($sql->query("select guid from transactions where guid = '" . $sql->real_escape_string($guid) . "'")->num_rows == 0) {
		$currency = $sql->query("select guid from commodities where namespace = 'CURRENCY' and mnemonic = 'EUR'")->fetch_object()->guid;
		$stmt = $sql->prepare("insert into transactions (guid, currency_guid, num, post_date, enter_date, description) VALUES (?, ?, ?, ?, NOW(), ?)");
		$stmt->bind_param("sssss", $guid, $currency, $num, date("Y-m-d H:i:s", $timestamp), $description);
		$stmt->execute();
		$stmt = $sql->prepare("insert into slots (obj_guid, name, slot_type, int64_val, string_val, double_val, timespec_val, guid_val, numeric_val_num, numeric_val_denom, gdate_val) values (?, 'date-posted', 10, 0, NULL, 0, NULL, NULL, 0, 1, ?)");
		$stmt->bind_param("ss", $guid, date("Y-m-d", $timestamp));
		$stmt->execute();
	}
}

function sqlAddSplits($guid, $splits) {
	global $sql;

	$sum_value = 0;
	foreach ($splits as $split_options) {
		$split_guid = md5(uniqid());
		$stmt = $sql->prepare("INSERT INTO splits (guid, tx_guid, account_guid, memo, value_num, value_denom, quantity_num, quantity_denom, action, reconcile_state, reconcile_date) VALUES (?, ?, ?, ?, ?, 100, ?, 100, '', 'n', NULL)");
		$stmt->bind_param("ssssii", $split_guid, $guid, $split_options["account_guid"], $split_options["memo"], $split_options["value"], $split_options["value"]);
		$stmt->execute();
		$sum_value += $split_options["value"];
	}
	return $sum_value;
}

function sqlReplaceSplit($guid, $splits) {
	global $sql;

	sqlAddSplits($guid,$splits);
	$sql->query("DELETE FROM splits WHERE guid = '".$guid."';");
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
		if (substr($num,0,1) == "*")
			$num = "*" . formatNum(substr($num,1));
		else if (substr($num,0,2) == "BP")
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
