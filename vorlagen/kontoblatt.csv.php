<?php
function csvFormat($str) {
	return "\"".addcslashes($str, "\"")."\"";
}
function csvFormatCurrency($cur) {
	return sprintf("%.2f", $cur/100);
}
?>
<?php if (!empty($account["transactions"])) { ?>
#,Datum,Beleg,Vorgang,Soll,Haben,Saldo
<?php $saldo = 0; foreach ($account["transactions"] as $buchung) { ?>
<?php $i = 0; foreach ($buchung["splits"] as $split) { if ($split["account_guid"] == $account["guid"]) { $i++; $saldo += $split["value"]*$account["saldoSign"]; ?>
<?php print($buchung["id"]) ?>,<?php print(date("d.m.Y", $buchung["date"])) ?>,<?php print(csvFormat($buchung["num"])) ?>,<?php print(csvFormat($buchung["description"])) ?>,<?php if ($split["value"] > 0) print(csvFormatCurrency($split["value"])) ?>,<?php if ($split["value"] < 0) print(csvFormatCurrency((-1)*$split["value"])) ?>,<?php print(csvFormatCurrency($saldo)) ?>
<?php } } ?>

<?php } ?>
<?php } ?>
