<?php

require_once("sql.inc.php");
require_once("login.inc.php");
require_once("transaction.inc.php");
loginRequire();

$list = array();
$result = $sql->query("select code, name from accounts");
while ($row = $result->fetch_object()) {
	if (strlen($row->code) > 1) {
		$list[] = array("code" => $row->code, "label" => formatAccountName(iconv("iso-8859-1","utf-8",$row->name), $row->code));
	}
}

header("Content-Type: application/json");
print(json_encode($list));
