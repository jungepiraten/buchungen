<?php

require_once("sql.inc.php");
require_once("latex.inc.php");
require_once("login.inc.php");
require_once("kassenbuch.inc.php");
loginRequire();

list($accounts, $journal) = getKassenbuch();

$tempfile = "temp/kassenbuch_" . rand(10,99);

ob_start();
include("vorlagen/kassenbuch.tex.php");
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
unlink($tempfile . ".toc");
unlink($tempfile . ".log");
unlink($tempfile . ".aux");

header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=kassenbuch.pdf");
header("Content-Length: " . filesize($tempfile . ".pdf"));
readfile($tempfile . ".pdf");

unlink($tempfile . ".pdf");
