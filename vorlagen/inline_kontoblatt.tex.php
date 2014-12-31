<?php

if (!function_exists("getHierarchyCount")) {
	function getHierarchyCount($guid) {
		global $accounts;
		if ($accounts[$guid]["parent_guid"] && $accounts[$accounts[$guid]["parent_guid"]]["code"]) {
			return getHierarchyCount($accounts[$guid]["parent_guid"]) + 1;
		}
		return 0;
	}
}

/** \hspace{<?php print(getHierarchyCount($account["guid"])) ?>cm} **/

?>
\section{<?php print(substr($account["code"],1)) ?> <?php print(latexSpecialChars($account["label"])) ?>}
\label{konto:<?php print($account["guid"]) ?>}
<?php

if (!function_exists("printFullPath")) {
	function printFullPath($guid) {
		$roots = array("F" => array("ref" => "kontenbuch", "label" => "Finanzbuchhaltung"), "R" => array("ref" => "kostenstellen", "label" => "Kostenrechnung"));
		global $accounts;
		if ($accounts[$guid]["parent_guid"] && $accounts[$accounts[$guid]["parent_guid"]]["code"]) {
			if (isset($roots[$accounts[$accounts[$guid]["parent_guid"]]["code"]])) {
				$root = $roots[$accounts[$accounts[$guid]["parent_guid"]]["code"]];
?>\mbox{\hyperref[<?php print($root["ref"]) ?>]{<?php print(latexSpecialChars($root["label"])) ?>}}<?php
			} else {
				printFullPath($accounts[$guid]["parent_guid"]);
			}
		}
?> :: \mbox{\hyperref[konto:<?php print($guid) ?>]{<?php print(substr($accounts[$guid]["code"],1)) ?> <?php print(latexSpecialChars($accounts[$guid]["label"])) ?>}}<?php
	}
}

printFullPath($account["guid"]);

?> \\
<?php print(latexSpecialChars($account["description"])) ?>

<?php if (!empty($account["subAccounts"])) { ?>
\subsection*{Unterkonten}
\begin{longtable}{L{1.5cm}L{9.5cm}R{2.5cm}R{2.5cm}}
 \hline
 \hline \textbf{\#} & \textbf{Konto} & \textbf{Teilsaldo} & \textbf{Saldo} \\
 \hline
 \hline
 \endhead
<?php $saldo = 0; foreach ($account["subAccounts"] as $code => $child_guid) { $subAccount = $accounts[$child_guid]; $accSaldo = $subAccount["saldo"]*$subAccount["saldoSign"]; $saldo += $accSaldo; ?>
 \hline \textbf{\hyperref[konto:<?php print($subAccount["guid"]) ?>]{<?php print(substr($subAccount["code"],1)) ?>}} & \hyperref[konto:<?php print($subAccount["guid"]) ?>]{<?php print(latexSpecialChars($subAccount["label"])) ?>} & <?php print(latexFormatCurrency($accSaldo)) ?> & <?php print(latexFormatCurrency($saldo)) ?> \\
<?php } $ownSaldo = $account["saldo"]*$account["saldoSign"] - $saldo; if ($ownSaldo != 0 && strlen($account["code"]) > 1) { ?>
 \hline & Direkt gebucht & <?php print(latexFormatCurrency($ownSaldo)) ?> & <?php print(latexFormatCurrency($account["saldo"]*$account["saldoSign"])) ?> \\
<?php } ?>
 \hline
 \hline
\end{longtable}
<?php } ?>

<?php if (!empty($account["transactions"]) && strlen($account["code"]) > 1) { ?>
\subsection*{Buchungen}
\begin{longtable}{R{1cm}L{1.3cm}L{6.2cm}R{2.2cm}R{2.2cm}R{2.5cm}}
 \hline
 \hline \textbf{\#} & \textbf{Beleg} & \textbf{Vorgang} & \textbf{Soll} & \textbf{Haben} & \textbf{Saldo} \\
 \hline
 \hline
 \endhead
<?php $saldo = 0; foreach ($account["transactions"] as $buchung) { ?>
 \hline \textbf{\hyperref[buchung:<?php print($buchung["id"]) ?>]{<?php print($buchung["id"]) ?>}} & \href{<?php print(getBelegUrl($year, $buchung["num"])) ?>}{<?php print(latexSpecialChars($buchung["num"])) ?>} & \multicolumn{4}{p{11cm}}{<?php print(latexSpecialChars($buchung["description"])) ?>} \\
<?php $i = 0; foreach ($buchung["splits"] as $split) { if ($split["account_guid"] == $account["guid"]) { $i++; $saldo += $split["value"]*$account["saldoSign"]; ?>
<?php if ($i < 3) { ?> \nopagebreak <?php } ?>
 \multicolumn{2}{l}{\hspace{2mm}<?php if ($i == 1) {print(date("d.m.Y", $buchung["date"]));} ?>} & <?php print(latexSpecialChars($split["memo"])) ?> & <?php if ($split["value"] > 0) print(latexFormatCurrency($split["value"])) ?> & <?php if ($split["value"] < 0) print(latexFormatCurrency((-1)*$split["value"])) ?> & <?php print(latexFormatCurrency($saldo)) ?> \\
<?php } ?>
<?php } } ?>
 \hline
 \hline
\end{longtable}
<?php } ?>

<?php if (empty($account["subAccounts"]) && empty($account["transactions"])) { ?>
\subsection*{Nicht bebucht}
<?php } ?>
