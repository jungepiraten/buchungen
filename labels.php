<?php

require_once("sql.inc.php");
require_once("pdf.inc.php");
require_once("login.inc.php");
require_once("kassenbuch.inc.php");
loginRequire();

list($accounts, $accounts_code2guid, $journal, $nums, $partners) = getKassenbuch();

sendPDF("labels.pdf", "vorlagen/labels.tex.php", array(
	"year" => $year,
	"accounts" => $accounts,
	"accounts_code2guid" => $accounts_code2guid,
	"journal" => $journal,
	"nums" => $nums,
	"partners" => $partners,
));
