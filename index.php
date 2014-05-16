<?php

require_once("sql.inc.php");
require_once("login.inc.php");
loginRequire();

if ($auth["grant"] && isset($_POST["createUser"])) {
	if (empty($_POST["username"])) {
		die("Leerer Benutzername");
	} else {
		loginCreateUser(stripslashes($_POST["username"]), (empty($_POST["accountPrefixes"]) ? array() : explode(",", $_POST["accountPrefixes"])), isset($_POST["grant"]), isset($_POST["database"]), isset($_POST["buchen"]), isset($_POST["belege"]), isset($_POST["verifyTransaction"]), isset($_POST["simpleTransactions"]));
	}
	header("Location: index.php");
	exit;
}

if ($auth["grant"] && isset($_POST["modifyUser"])) {
	loginModifyUser(stripslashes($_POST["username"]), (empty($_POST["accountPrefixes"]) ? array() : explode(",", $_POST["accountPrefixes"])), isset($_POST["grant"]), isset($_POST["database"]), isset($_POST["buchen"]), isset($_POST["belege"]), isset($_POST["verifyTransaction"]), isset($_POST["simpleTransactions"]));
	header("Location: index.php");
	exit;
}

if ($auth["grant"] && isset($_POST["removeUser"])) {
	loginRemoveUser($_POST["username"]);
	header("Location: index.php");
	exit;
}

if ($auth["grant"]) {
	$users = array();
	foreach ($authDb as $username => $user) {
		$users[] = array(
			"username" => $username,
			"accountPrefixes" => $user->accountPrefixes,
			"grant" => isset($user->grant) && $user->grant,
			"database" => isset($user->database) && $user->database,
			"buchen" => isset($user->buchen) && $user->buchen,
			"belege" => isset($user->belege) && $user->belege,
			"verifyTransaction" => isset($user->verifyTransaction) && $user->verifyTransaction,
			"simpleTransactions" => isset($user->simpleTransactions) && $user->simpleTransactions,
		);
	}
}

include("templates/settings.php");
