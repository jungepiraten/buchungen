<?php

require_once("sql.inc.php");
require_once("lock.inc.php");
require_once("login.inc.php");
loginRequire("database");

$host = $_SERVER["REMOTE_ADDR"];
$locked = databaseIsLocked($year);

if (!$locked && isset($_REQUEST["lock"])) {
	$password = databaseLock($year, $auth["user"], $host);
	$locked = true;
	header("Location: lock.php?year=" . $year);
	exit;
}

if ($locked) {
	list($lockedBy, $lockedHost, $lockedPassword, $lockedTimestamp) = databaseGetLockMeta($year);
	$isAuth = databaseIsAuth($year, $auth["user"], $host);
}

if ($locked && isset($_REQUEST["unlock"])) {
	databaseUnlock($year);
	$locked = false;
	header("Location: lock.php?year=" . $year);
	exit;
}

include("templates/lock.php");
