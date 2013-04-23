<?php

require_once("latex.inc.php");

function generatePDF($sourcefile, $variables) {
	extract($variables);
	$tempfile = "temp/pdflib_" . rand(100,999);

	ob_start();
	include($sourcefile);
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
	if (file_exists($tempfile . ".tex"))
		unlink($tempfile . ".tex");
	if (file_exists($tempfile . ".out"))
		unlink($tempfile . ".out");
	if (file_exists($tempfile . ".toc"))
		unlink($tempfile . ".toc");
	if (file_exists($tempfile . ".log"))
		unlink($tempfile . ".log");
	if (file_exists($tempfile . ".aux"))
		unlink($tempfile . ".aux");
	return $tempfile . ".pdf";
}

function sendPDF($exportFilename, $sourcefile, $variables) {
	$tempfile = generatePDF($sourcefile, $variables);

	header("Content-Type: application/pdf");
	header("Content-Disposition: inline; filename=" . $exportFilename);
	header("Content-Length: " . filesize($tempfile));
	readfile($tempfile);

	unlink($tempfile);
}
