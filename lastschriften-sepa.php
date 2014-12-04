<?php

require_once("pdf.inc.php");
require_once("config.inc.php");

if (isset($_FILES["PAIN"]) && $_FILES["PAIN"]["error"] == 0) {
	$charges = array(); $sum_betrag = 0;
	$pain = new SimpleXMLElement(file_get_contents($_FILES["PAIN"]["tmp_name"]));
	foreach ($pain->CstmrDrctDbtInitn->PmtInf->DrctDbtTxInf as $pmt) {
		$charges[] = array(
			"end-to-end" => $pmt->PmtId->EndToEndId,
			"betrag" => str_replace(".","",$pmt->InstdAmt),
			"mandat" => array(
				"id" => $pmt->DrctDbtTx->MndtRltdInf->MndtId,
				"datum" => $pmt->DrctDbtTx->MndtRltdInf->DtOfSgntr,
			),
			"konto" => array(
				"inhaber" => $pmt->Dbtr->Nm,
				"iban" => $pmt->DbtrAcct->Id->IBAN,
				"bic" => $pmt->DbtrAgt->FinInstnId->BIC,
			),
			"verwendung" => $pmt->RmtInf->Ustrd,
		);
	}

	sendPDF("lastschriften-sepa.pdf", "vorlagen/lastschriften-sepa.tex.php", array(
		"date" => strtotime($pain->CstmrDrctDbtInitn->GrpHdr->CreDtTm),
		"charges" => $charges,
		"sum_betrag" => str_replace(".","",$pain->CstmrDrctDbtInitn->GrpHdr->CtrlSum),
		"count" => $pain->CstmrDrctDbtInitn->GrpHdr->NbOfTxs,
	));
	exit;
}

include("templates/lastschriften-sepa.php");
