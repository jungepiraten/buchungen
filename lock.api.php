<?php

$year = date("Y");
if (isset($_REQUEST["year"])) {
	$year = intval($_REQUEST["year"]);
}

require_once("api.inc.php");
require_once("lock.inc.php");
require_once("login.api.php");

if (! $auth["database"]) {
	out(403, array("error" => "NOT_ALLOWED"));
}

$host = $_SERVER["REMOTE_ADDR"];
$locked = databaseIsLocked($year);

$action = "";
if (isset($_REQUEST["action"])) {
	$action = stripslashes($_REQUEST["action"]);
}

if (!$locked && $action == "lock") {
	list($sqluser, $password) = databaseLock($year, $auth["user"], $host);
	$locked = true;
	out(200, array("status" => "locked", "action" => "locked", "username" => $sqluser, "password" => $password, "host" => "mysql.intern.junge-piraten.de", "userHost" => $host, "database" => $database, "timestamp" => time()));
}

if ($locked) {
	list($lockedBy,	$lockedHost, $lockedPassword, $lockedTimestamp) = databaseGetLockMeta($year);
}

if ($locked && $action == "unlock") {
	databaseUnlock($year);
	$locked = false;
	out(200, array("status" => "free", "action" => "unlocked"));
}

if ($locked && databaseIsAuth($year, $auth["user"], $host)) {
	out(200, array("status" => "locked", "username" => $lockedBy, "password" => $lockedPassword, "host" => "mysql.intern.junge-piraten.de", "userHost" => $lockedHost, "database" => $database, "timestamp" => $lockedTimestamp));
} else if ($locked) {
	out(200, array("status" => "locked", "username" => $lockedBy, "timestamp" => $lockedTimestamp));
} else {
	out(200, array("status" => "free"));
}
