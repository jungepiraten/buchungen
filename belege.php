<?php

require_once("login.inc.php");
require_once("latex.inc.php");
loginRequire("belege");

require_once("config.inc.php");
require_once("vpanel.inc.php");
require_once("Payment/DTA.php");

function fetchLaTeXInput($varname) {
	if (isset($_REQUEST[$varname])) {
		return latexSpecialChars(stripslashes($_REQUEST[$varname]));
	}
	return "";
}

$dtaus = array();
function fetchLaTeXDTA($varname, $field) {
	global $dtaus;
	if (!isset($dtaus[$varname])) {
		$dta = new DTA(file_get_contents($_FILES[$varname]["tmp_name"]));
		$meta = $dta->getMetaData();
		$dtaus[$varname] = array(
			"COUNT" => latexSpecialChars($meta["count"]),
			"BETRAG" => latexSpecialChars(sprintf("%0.2f", $meta["sum_amounts"])),
			"BUCHUNGEN" => array()
		);
		while ($cur = @$dta->current()) {
			$dtaus[$varname]["BUCHUNGEN"][] = array(
				"BLZ" => latexSpecialChars($cur["receiver_bank_code"]),
				"KONTO" => latexSpecialChars($cur["receiver_account_number"]),
				"INHABER" => latexSpecialChars($cur["receiver_name"]),
				"BETRAG" => latexSpecialChars(sprintf("%0.2f", $cur["amount"]/100)),
				"VERWENDUNG" => implode("", array_map("latexSpecialChars", $cur["purposes"]))
			);
			$dta->next();
		}
	}
	return $dtaus[$varname][$field];
}

if (isset($_REQUEST["beleg"])) {
	$beleg = stripslashes($_REQUEST["beleg"]);
	$vorlage = "vorlagen/" . basename($_REQUEST["type"]) . ".tex.php";

	if (!file_exists($vorlage)) {
		die("Ungueltige Vorlagenauswahl!");
	}

	$tempfile = "temp/beleg_" . $beleg;

	ob_start();
	include($vorlage);
	$tex = ob_get_contents();
	ob_end_clean();

	file_put_contents($tempfile . ".tex", $tex);
	// 2 Times, to make pagenumbering work
	system("/usr/bin/pdflatex -output-directory temp " . $tempfile . " >/dev/null");
	system("/usr/bin/pdflatex -output-directory temp " . $tempfile . " >/dev/null");
	if (!file_exists($tempfile . ".pdf")) {
		die("Could not generate PDF");
	}
	unlink($tempfile . ".tex");
	unlink($tempfile . ".log");
	unlink($tempfile . ".aux");

	if (isset($_REQUEST["upload_vpanel"])) {
		$vpanel = new VPanel(VPANELBASE);
		$vpanel->startSession(VPANELUSER, VPANELPASS);
//		if (!$vpanel->uploadDocument(4, $tempfile . ".pdf", array("label" => "Beleg 2013-" . $beleg, "kommentar" => "Erhalten von " . $_SERVER["REMOTE_ADDR"], "data" => array("request" => $_REQUEST, "dtaus" => $dtaus)))) {
		if (!$vpanel->uploadDocument(4, $tempfile . ".pdf", array("label" => "Lastschriftbericht", "kommentar" => "Erhalten von " . $_SERVER["REMOTE_ADDR"] . " (Quelle in Ticket #" . $_REQUEST["ticket"] . ")", "dtaus" => $dtaus))) {
			die("Could not upload PDF");
		}
//		file_put_contents("beleg.txt", ++$beleg);
	}
	if (isset($_REQUEST["download"])) {
		header("Content-Type: application/pdf");
		header("Content-Disposition: inline");
		header("Content-Length: " . filesize($tempfile . ".pdf"));
		readfile($tempfile . ".pdf");
	}

	unlink($tempfile . ".pdf");

}

$beleg = intval(file_get_contents("beleg.txt"));

include("templates/belege.php");
