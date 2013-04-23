<?php

require_once("sql.inc.php");
require_once("latex.inc.php");
require_once("login.inc.php");
require_once("kassenbuch.inc.php");
loginRequire();

$guid = $_REQUEST["guid"];

list($accounts, $journal) = getKassenbuch();
$account = $accounts[$guid];

$tempfile = "temp/kontoblatt_" . rand(10,99);

ob_start();
include("vorlagen/kontoblatt.tex.php");
$tex = ob_get_contents();
ob_end_clean();

file_put_contents($tempfile . ".tex", $tex);
// 3 Times, to make pagenumbering work (also on toc)
system("/usr/bin/pdflatex -output-directory temp " . $tempfile . " >/dev/null");
system("/usr/bin/pdflatex -output-directory temp " . $tempfile . " >/dev/null");
system("/usr/bin/pdflatex -output-directory temp " . $tempfile . " >/dev/null");
if (!file_exists($tempfile . ".pdf")) {
	die("Could not generate PDF");
}
unlink($tempfile . ".tex");
unlink($tempfile . ".out");
unlink($tempfile . ".log");
unlink($tempfile . ".aux");

header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=kontoblatt.pdf");
header("Content-Length: " . filesize($tempfile . ".pdf"));
readfile($tempfile . ".pdf");

unlink($tempfile . ".pdf");
