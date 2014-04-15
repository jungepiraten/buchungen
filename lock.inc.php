<?php

require_once("config.inc.php");
require_once("sql.inc.php");

function databaseIsLocked($year) {
	return file_exists("lock/finanzen_" . $year . ".txt");
}

function databaseGetLockMeta($year) {
	$meta = unserialize(file_get_contents("lock/finanzen_" . $year . ".txt"));
	$meta[] = filectime("lock/finanzen_" . $year . ".txt");
	return $meta;
}

function databaseIsAuth($year, $user, $host) {
	list($lockedBy, $lockedHost, $lockedPassword, $lockedTimestamp) = databaseGetLockMeta($year);
	return $lockedBy == databaseGetSqlUser($user) && $lockedHost == $host;
}

function databaseGetSqlUser($user) {
	return substr($user,0,14);
}

function databaseLock($year, $user, $host) {
	global $sql;

	$sqluser = databaseGetSqlUser($user);
	$password = substr(md5(microtime(true) . "-" . $year . "-" . rand(10000,99999)), rand(0,15), 16);

	$sql->query("CREATE USER '".$sql->real_escape_string($sqluser)."'@'".$sql->real_escape_string($host)."' IDENTIFIED BY  '".$sql->real_escape_string($password)."';");
	$sql->query("GRANT ALL PRIVILEGES ON  `finanzen\\_".str_replace("_","\\_",$sql->real_escape_string($year))."` . * TO  '".$sql->real_escape_string($sqluser)."'@'".$sql->real_escape_string($host)."';");

	file_put_contents("lock/finanzen_" . $year . ".txt", serialize(array($sqluser, $host, $password)));
	file_put_contents("lock/finanzen_" . $year . ".log", "[" .strftime("%Y-%m-%d %H:%M",time()). "] Locked ".$sqluser."@".$host." by " . $user . "\n", FILE_APPEND);

	return array($sqluser, $password);
}

function databaseUnlock($year) {
	global $sql;

	list($lockedBy, $lockedHost, $lockedPassword, $lockedTimestamp) = databaseGetLockMeta($year);
	$sql->query("DROP USER '".$sql->real_escape_string($lockedBy)."'@'".$sql->real_escape_string($lockedHost)."'");

	file_put_contents("lock/finanzen_" . $year . ".log", "[" .strftime("%Y-%m-%d %H:%M",time()). "] Unlocked ".$lockedBy."@".$lockedHost. "\n", FILE_APPEND);
	unlink("lock/finanzen_" . $year . ".txt");
}
