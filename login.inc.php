<?php

$authDb = json_decode(file_get_contents("lock/authDb"));

session_start();

$auth = false;

if (isset($_REQUEST["login"])) {
	if (loginMatchPassword(stripslashes($_REQUEST["loginUser"]), stripslashes($_REQUEST["loginPass"]))) {
		$_SESSION["_finanzenlock"] = stripslashes($_REQUEST["loginUser"]);
	}

	header("Location: index.php");
	exit;
}

if (isset($_REQUEST["logout"])) {
	unset($_SESSION["_finanzenlock"]);

	header("Location: index.php");
	exit;
}

if (isset($_SESSION["_finanzenlock"]) && isset($authDb->{$_SESSION["_finanzenlock"]})) {
	$username = $_SESSION["_finanzenlock"];
	loginInitSession($username);
}

function loginHasFacility($facility) {
	global $auth;

	if ($auth == null) {
		return false;
	}
	return $auth[$facility];
}

function loginRequire($facility = null) {
	global $auth;

	if ($auth == null) {
		include("templates/login.php");
		exit;
	}
	if ($facility != null && ! $auth[$facility]) {
		include("templates/login.php");
		exit;
	}
}

function loginInitSession($username) {
	global $authDb, $auth;

	$auth = array(
		"user" => $username,
		"accountPrefixes" => $authDb->$username->accountPrefixes,
		"grant" => isset($authDb->$username->grant) && $authDb->$username->grant,
		"database" => isset($authDb->$username->database) && $authDb->$username->database,
		"belege" => isset($authDb->$username->belege) && $authDb->$username->belege,
		"verifyTransaction" => isset($authDb->$username->verifyTransaction) && $authDb->$username->verifyTransaction,
		"simpleTransactions" => isset($authDb->$username->simpleTransactions) && $authDb->$username->simpleTransactions,
	);
}

function loginMatchPassword($user, $pass) {
	global $authDb;
	return $authDb->$user->password == sha1($pass);
}

function loginCreateUser($user, $pass, $accountPrefixes = array(), $grant = 0, $database = 0, $belege = 0, $verifyTransaction = 0, $simpleTransactions = 1) {
	global $authDb;

	$authDb->$user = (object) array(
		"password" => sha1($pass),
		"accountPrefixes" => $accountPrefixes,
		"grant" => $grant,
		"database" => $database,
		"belege" => $belege,
		"verifyTransaction" => $verifyTransaction,
		"simpleTransactions" => $simpleTransactions,
	);
	file_put_contents("lock/authDb", json_encode($authDb));	
}

function loginRemoveUser($user) {
	global $authDb;

	unset($authDb->$user);
	file_put_contents("lock/authDb", json_encode($authDb));
}

function loginChangePassword($user, $pass) {
	global $authDb;

	$authDb->$user->password = sha1($pass);
	file_put_contents("lock/authDb", json_encode($authDb));
}

function loginModifyUser($user, $accountPrefixes, $grant, $database, $belege, $verifyTransaction, $simpleTransactions) {
	global $authDb;

	$authDb->$user->accountPrefixes = $accountPrefixes;
	$authDb->$user->grant = $grant;
	$authDb->$user->database = $database;
	$authDb->$user->belege = $belege;
	$authDb->$user->verifyTransaction = $verifyTransaction;
	$authDb->$user->simpleTransactions = $simpleTransactions;
	file_put_contents("lock/authDb", json_encode($authDb));	
}
