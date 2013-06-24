<?php

require_once("sql.inc.php");
require_once("pdf.inc.php");
require_once("login.inc.php");
require_once("kassenbuch.inc.php");
loginRequire();

list($accounts, $journal) = getKassenbuch();

sendPDF("kassenbuch.pdf", "vorlagen/kassenbuch.tex.php", array(
	"year" => $year,
	"accounts" => $accounts,
	"journal" => $journal
));
