<?php

require_once("sql.inc.php");
require_once("latex.inc.php");
require_once("login.inc.php");
require_once("transaction.inc.php");
loginRequire();

$accounts = array();
$result = $sql->query("select parent_guid, guid, code, name, placeholder, hidden from accounts where hidden = 0 order by code");
while ($acc = $result->fetch_assoc()) {
	$acc = formatAccount($acc);
	$accounts[$acc["guid"]] = $acc;
}

$i = 0;

$transactions = array();
$result = $sql->query("select guid as guid from transactions order by post_date asc");
while ($row = $result->fetch_assoc()) {
	$transaction = getTransaction($row["guid"]);

	$allowed = false;
	foreach ($transaction["splits"] as $split) {
		if (isAllowedAccount($split["account_guid"])) {
			$allowed = true;
		}
	}

	if ($allowed) {
		$transaction["id"] = ++$i;

		$journal[] = $transaction;
		$account_guids = array();
		foreach ($transaction["splits"] as $split) {
			if (!in_array($split["account_guid"], $account_guids)) {
				$accounts[$split["account_guid"]]["transactions"][] = $transaction;
				$account_guids[] = $split["account_guid"];
			}
		}
	}
}

$tempfile = "temp/kassenbuch";

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
#unlink($tempfile . ".toc");
unlink($tempfile . ".log");
unlink($tempfile . ".aux");

header("Content-Type: application/pdf");
header("Content-Disposition: inline");
header("Content-Length: " . filesize($tempfile . ".pdf"));
readfile($tempfile . ".pdf");
