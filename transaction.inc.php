<?php

function getTransaction($guid) {
	global $sql;

	$transaction = formatTransaction($sql->query("select guid, num, unix_timestamp(convert_tz(post_date,\"+00:00\",\"+01:00\")) as date, description from transactions where guid = '".$sql->real_escape_string($guid)."'")->fetch_assoc());
	$transaction["splits"] = array();
	$result = $sql->query("select s.guid as guid, s.memo as memo, s.reconcile_state as reconcile, a.guid as account_guid, a.code as account_code, a.name as account_name, (s.value_num/s.value_denom) as value from splits s left join accounts a ON(a.guid = s.account_guid) where s.tx_guid = '".$sql->real_escape_string($guid)."'");
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

function verifyTransaction($username, $transaction) {
	$splitHashes = array();
	foreach ($transaction["splits"] as $split) {
		$splitHashes[] = serialize(array(
			$split["guid"], $split["memo"], $split["account_guid"], $split["account_code"], $split["value"]
		));
	}
	sort($splitHashes);
	return md5(serialize(array(
		$username, $transaction["guid"], $transaction["num"], $transaction["date"], $transaction["description"], $splitHashes
	)));
}

function isValidated($transaction, $validation) {
	return verifyTransaction($validation["username"], $transaction) == $validation["hash"];
}
