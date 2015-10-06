<?php

$authDb = json_decode(file_get_contents("lock/authDb"));

session_start();

$auth = false;

if (isset($_REQUEST["login"])) {
	if (loginMatchPassword($_REQUEST["loginUser"], $_REQUEST["loginPass"])) {
		$_SESSION["_finanzenlock"] = $_REQUEST["loginUser"];
	}

	header("Location: index.php");
	exit;
}

if (isset($_REQUEST["logout"])) {
	unset($_SESSION["_finanzenlock"]);

	header("Location: index.php");
	exit;
}

if (isset($_SESSION["_finanzenlock"])) {
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

function http_do_post($url, $data) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST,count($data));
	curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($data));
	return curl_exec($ch);
}

function loginInitSession($username) {
	global $auth, $authDb;
	$groups = $_SESSION["_groups"][$username];

	$auth = array(
		"user" => $username,
		"accountPrefixes" => array(),
		"grant" => false,
		"database" => false,
		"buchen" => false,
		"kostenstellen" => false,
		"kreditoren" => false,
		"belege" => false,
		"verifyTransaction" => false,
		"simpleTransactions" => false,
	);
	foreach ($groups as $group) {
		if (isset($authDb->{$group})) {
			$auth["accountPrefixes"] = array_merge($auth["accountPrefixes"], $authDb->{$group}->accountPrefixes);
			foreach (array("grant", "database", "buchen", "kostenstellen", "kreditoren", "belege", "verifyTransaction", "simpleTransactions") as $a) {
				$auth[$a] = $auth[$a] || (isset($authDb->{$group}->{$a}) && $authDb->{$group}->{$a});
			}
		}
	}
}

function loginMatchPassword($user, $pass) {
	//global $auth;

	// http://pear.php.net/package/Net_LDAP2
	require_once("Net/LDAP2.php");

	$dn = "uid=".$user.",ou=People,o=Junge Piraten,c=DE";
	$ldap = Net_LDAP2::connect(array("binddn" => $dn, "bindpw" => $pass, "basedn" => "o=junge piraten,c=de", "host" => "storage"));
	if (Net_LDAP2::isError($ldap)) {
		return false;
	}
	$groups = array();
	foreach ($ldap->search("ou=Groups,o=Junge Piraten,c=DE", "(uniqueMember=".$dn.")", array("attributes" => array("cn"))) as $group_dn => $entry) {
		$groups[] = $entry->getValue("cn","single");
	}
	$_SESSION["_groups"][$user] = $groups;
	return true;

	$reply = json_decode(http_do_post("https://ucp.junge-piraten.de/json/checkUser", array("user" => $user, "password" => $pass)));
	if ($reply->status == "success") {
		$_SESSION["_groups"][$user] = $reply->groups;
		return true;
	} else {
		return false;
	}
}

function loginCreateUser($user, $accountPrefixes = array(), $grant = 0, $database = 0, $buchen = 0, $kostenstellen = 0, $kreditoren = 0, $belege = 0, $verifyTransaction = 0, $simpleTransactions = 1) {
	global $authDb;

	$authDb->$user = (object) array(
		"accountPrefixes" => $accountPrefixes,
		"grant" => $grant,
		"database" => $database,
		"buchen" => $buchen,
		"kostenstellen" => $kostenstellen,
		"kreditoren" => $kreditoren,
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

function loginModifyUser($user, $accountPrefixes, $grant, $database, $buchen, $kostenstellen, $kreditoren, $belege, $verifyTransaction, $simpleTransactions) {
	global $authDb;

	$authDb->$user->accountPrefixes = $accountPrefixes;
	$authDb->$user->grant = $grant;
	$authDb->$user->database = $database;
	$authDb->$user->buchen = $buchen;
	$authDb->$user->kostenstellen = $kostenstellen;
	$authDb->$user->kreditoren = $kreditoren;
	$authDb->$user->belege = $belege;
	$authDb->$user->verifyTransaction = $verifyTransaction;
	$authDb->$user->simpleTransactions = $simpleTransactions;
	file_put_contents("lock/authDb", json_encode($authDb));	
}
