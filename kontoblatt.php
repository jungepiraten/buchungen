<?php

require_once("sql.inc.php");
require_once("pdf.inc.php");
require_once("login.inc.php");
require_once("kassenbuch.inc.php");
loginRequire();

$guid = $_REQUEST["guid"];

list($accounts, $accounts_code2guid, $journal, $nums) = getKassenbuch();
$account = $accounts[$guid];

sendPDF("konto-" . $account["guid"] . ".pdf", "vorlagen/kontoblatt.tex.php", array(
	"year" => $year,
	"accounts" => $accounts,
	"account" => $account
));
