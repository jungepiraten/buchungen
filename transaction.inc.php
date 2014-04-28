<?php

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
