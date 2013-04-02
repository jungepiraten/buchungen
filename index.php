<?php

require_once("sql.inc.php");
require_once("login.inc.php");
loginRequire();

if (isset($_POST["changePw"])) {
	if (!loginMatchPassword($auth["user"], stripslashes($_POST["changePwOld"]))) {
		die("Falsches Passwort!");
	} else if (stripslashes($_POST["changePwNew"]) != stripslashes($_POST["changePwConfirm"])) {
		die("Passwörter matchen nicht!");
	} else {
		loginChangePassword($auth["user"], stripslashes($_POST["changePwNew"]));
	}
}

if ($auth["grant"] && isset($_POST["createUser"])) {
	if (empty($_POST["username"])) {
		die("Leerer Benutzername");
	} else {
		loginCreateUser(stripslashes($_POST["username"]), $_POST["password"], (empty($_POST["accountPrefixes"]) ? array() : explode(",", $_POST["accountPrefixes"])), isset($_POST["grant"]), isset($_POST["database"]), isset($_POST["belege"]), isset($_POST["verifyTransaction"]));
	}
	header("Location: index.php");
	exit;
}

if ($auth["grant"] && isset($_POST["modifyUser"])) {
	loginModifyUser(stripslashes($_POST["username"]), (empty($_POST["accountPrefixes"]) ? array() : explode(",", $_POST["accountPrefixes"])), isset($_POST["grant"]), isset($_POST["database"]), isset($_POST["belege"]), isset($_POST["verifyTransaction"]));
	if ($_POST["password"] != "") {
		loginChangePassword(stripslashes($_POST["username"]), stripslashes($_POST["password"]));
	}
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
			"belege" => isset($user->belege) && $user->belege,
			"verifyTransaction" => isset($user->verifyTransaction) && $user->verifyTransaction,
		);
	}
}

include("templates/settings.php");
