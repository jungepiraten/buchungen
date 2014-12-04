<?php

require_once("config.inc.php");
require_once("transaction.inc.php");

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

	$transaction = formatTransaction($sql->query("select guid, num, unix_timestamp(convert_tz(post_date,\"UTC\",\"Europe/Berlin\")) as date, unix_timestamp(convert_tz(enter_date,\"UTC\",\"Europe/Berlin\")) as enter_date, description from transactions where guid = '".$sql->real_escape_string($guid)."'")->fetch_assoc());
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

$cache_accounts = array("code" => array(), "accs" => array());
function sqlGetAccount($guid) {
	global $sql, $cache_accounts;

	if (! isset($cache_accounts["guid"][$guid])) {
		$account = formatAccount($sql->query("select guid, code, name, parent_guid, description, placeholder, hidden from accounts where guid = '".$sql->real_escape_string($guid)."'")->fetch_assoc());
		if ($account["guid"] != $guid) {
			throw new Exception("Account not found");
		}
		$cache_accounts["accs"][$account["guid"]] = $account;
		$cache_accounts["code"][$account["code"]] = $account["guid"];
	}
	return $cache_accounts["accs"][$guid];
}
function sqlGetAccountByCode($code) {
	global $sql, $cache_accounts;

	if (! isset($cache_accounts["code"][$code])) {
		$account = formatAccount($sql->query("select guid, code, name, parent_guid, description, placeholder, hidden from accounts where code = '".$sql->real_escape_string($code)."'")->fetch_assoc());
		if ($account["code"] != $code) {
			throw new Exception("Account not found: ". $code);
		}
		$cache_accounts["accs"][$account["guid"]] = $account;
		$cache_accounts["code"][$account["code"]] = $account["guid"];
	}
	return $cache_accounts["accs"][$cache_accounts["code"][$code]];
}

function sqlGetAccountNotes($guid) {
	global $sql;
	$result = $sql->query("select string_val from slots where obj_guid = '".$sql->real_escape_string($guid)."' and name = 'notes'");
	if ($result->num_rows > 0) {
		return iconv("iso-8859-1","utf-8",$result->fetch_object()->string_val);
	}
	return "";
}

function sqlMaybeAddAccount($guid, $parent_guid, $type, $code, $name, $description) {
	try {
		sqlAddAccount($guid, $parent_guid, $type, $code, $name, $description);
	} catch (Exception $e) {}
}

function sqlAddAccount($guid, $parent_guid, $type, $code, $name, $description) {
	global $sql;

	if ($sql->query("select guid from accounts where guid = '". $sql->real_escape_string($guid) . "'")->num_rows > 0) {
		throw new Exception("Account exists");
	}
	$currency = $sql->query("select guid from commodities where namespace = 'CURRENCY' and mnemonic = 'EUR'")->fetch_object()->guid;
	$stmt = $sql->prepare("insert into accounts (guid,name,account_type,commodity_guid,commodity_scu,non_std_scu,parent_guid,code,description,hidden,placeholder) values (?,?,?,?,100,0,?,?,?,0,0)");
	$stmt->bind_param("sssssss", $guid, iconv("utf-8","iso-8859-1",$name), $type, $currency, $parent_guid, $code, iconv("utf-8","iso-8859-1",$description));
	$stmt->execute();
	$stmt = $sql->prepare("insert into slots (obj_guid,name,slot_type,int64_val,string_val,double_val,timespec_val,guid_val,numeric_val_num,numeric_val_denom,gdate_val) values (?,'color',4,0,'Not Set',0,NULL,NULL,0,1,NULL)");
	$stmt->bind_param("s", $guid);
	$stmt->execute();
}

function sqlMaybeAddTransaction($guid, $num, $timestamp, $description) {
	try {
		sqlAddTransaction($guid, $num, $timestamp, $description);
	} catch (Exception $e) {}
}

function sqlAddTransaction($guid, $num, $timestamp, $description) {
	global $sql;

	if ($sql->query("select guid from transactions where guid = '" . $sql->real_escape_string($guid) . "'")->num_rows > 0) {
		throw new Exception("Transaction exists");
	}
	$currency = $sql->query("select guid from commodities where namespace = 'CURRENCY' and mnemonic = 'EUR'")->fetch_object()->guid;
	$stmt = $sql->prepare("insert into transactions (guid, currency_guid, num, post_date, enter_date, description) VALUES (?, ?, ?, ?, NOW(), ?)");
	$stmt->bind_param("sssss", $guid, $currency, $num, date("Y-m-d H:i:s", $timestamp), iconv("utf-8","iso-8859-1",$description));
	$stmt->execute();
	$stmt = $sql->prepare("insert into slots (obj_guid, name, slot_type, int64_val, string_val, double_val, timespec_val, guid_val, numeric_val_num, numeric_val_denom, gdate_val) values (?, 'date-posted', 10, 0, NULL, 0, NULL, NULL, 0, 1, ?)");
	$stmt->bind_param("ss", $guid, date("Y-m-d", $timestamp));
	$stmt->execute();
}

function sqlSetTransaction($guid, $num, $timestamp, $description) {
	global $sql;

	$stmt = $sql->prepare("update transactions set num = ?, post_date = ?, description = ? where guid = ?");
	$stmt->bind_param("ssss", $num, date("Y-m-d H:i:s", $timestamp), iconv("utf-8","iso-8859-1",$description), $guid);
	$stmt->execute();
}

function sqlAddSplits($guid, $splits) {
	global $sql;

	$sum_value = 0;
	foreach ($splits as $split_options) {
		$split_guid = md5(uniqid());
		$stmt = $sql->prepare("INSERT INTO splits (guid, tx_guid, account_guid, memo, value_num, value_denom, quantity_num, quantity_denom, action, reconcile_state, reconcile_date) VALUES (?, ?, ?, ?, ?, 100, ?, 100, '', 'n', NULL)");
		$stmt->bind_param("ssssii", $split_guid, $guid, $split_options["account_guid"], iconv("utf-8","iso-8859-1",$split_options["memo"]), $split_options["value"], $split_options["value"]);
		$stmt->execute();
		$sum_value += $split_options["value"];
	}
	return $sum_value;
}

function sqlReplaceSplit($guid, $splits) {
	global $sql;

	$tx_guid = $sql->query("select tx_guid from splits where guid = '".$guid."'")->fetch_object()->tx_guid;
	sqlAddSplits($tx_guid,$splits);
	$sql->query("DELETE FROM splits WHERE guid = '".$guid."';");
}

function sqlSetTransactionNum($guid, $num) {
	global $sql;
	$stmt = $sql->prepare("UPDATE transactions SET num = ? WHERE guid = ?");
	$stmt->bind_param("ss", $num, $guid);
	$stmt->execute();
}

function sqlSetTransactionTimestamp($guid, $timestamp) {
	global $sql;
	$stmt = $sql->prepare("UPDATE transactions SET post_date = ? WHERE guid = ?");
	$stmt->bind_param("ss", date("Y-m-d H:i:s", $timestamp), $guid);
	$stmt->execute();
	$stmt = $sql->prepare("UPDATE slots SET gdate_val = ? WHERE obj_guid = ? AND name = 'date-posted' AND slot_type = 10");
	$stmt->bind_param("ss", date("Y-m-d", $timestamp), $guid);
	$stmt->execute();
}

function formatAccount($account) {
	if (!$account) return;
	foreach ($account as &$value) {
		$value = iconv("iso-8859-1","utf-8",$value);
	}
	$account["label"] = formatAccountName($account["name"], $account["code"]);
	return $account;
}

function formatTransaction($transaction) {
	foreach (array("description") as $key) {
		if (isset($transaction[$key])) {
			$transaction[$key] = iconv("iso-8859-1","utf-8",$transaction[$key]);
		}
	}
	if (isset($transaction["account_name"])) {
		$transaction["account_label"] = formatAccountName($transaction["account_name"], $transaction["account_code"]);
	}
	$transaction["num"] = formatNum($transaction["num"]);
	return $transaction;
}

function formatSplit($split) {
	foreach ($split as &$value) {
		$value = iconv("iso-8859-1","utf-8",$value);
	}
	$split["account_label"] = formatAccountName($split["account_name"], $split["account_code"]);
	return $split;
}

function formatAccountName($name, $code) {
	$a = explode(" ", $name, 2);
	if (count($a) > 1) {
		list($c,$d) = $a;
		return substr($code,(-1)*strlen($c)) == $c ? $d : $name;
	}
	return array_shift($a);
}

function formatNum($num) {
	global $year;
	if ($year == "2013" && strlen($num) != 0) {
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
