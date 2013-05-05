<?php

require_once("sql.inc.php");
require_once("pdf.inc.php");
require_once("login.inc.php");
require_once("kassenbuch.inc.php");
loginRequire();

$guid = $_REQUEST["guid"];

list($accounts, $journal) = getKassenbuch();
$account = $accounts[$guid];

sendPDF("konto-" . $account["guid"] . ".pdf", "vorlagen/kontoblatt.tex.php", array(
	"account" => $account
));