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

?>
\subsection{\hspace{<?php print(getHierarchyCount($account["guid"])) ?>cm} <?php print($account["code"]) ?> <?php print(latexSpecialChars($account["label"])) ?>}
\label{konto:<?php print($account["guid"]) ?>}
<?php

if (!function_exists("printFullPath")) {
	function printFullPath($guid) {
		global $accounts;
		if ($accounts[$guid]["parent_guid"] && $accounts[$accounts[$guid]["parent_guid"]]["code"]) {
			printFullPath($accounts[$guid]["parent_guid"]);
		} else {
?>\mbox{\hyperref[kontenbuch]{Kontenbuch}}<?php
		}
?> :: \mbox{\hyperref[konto:<?php print($guid) ?>]{<?php print($accounts[$guid]["code"]) ?> <?php print(latexSpecialChars($accounts[$guid]["label"])) ?>}}<?php
	}
}

printFullPath($account["guid"]);

?> \\
<?php print(latexSpecialChars($account["description"])) ?>

<?php if (!empty($account["subAccounts"])) { ?>
\subsubsection{Unterkonten}
\begin{longtable}{L{1.5cm}L{9.5cm}R{2.5cm}R{2.5cm}}
 \hline
 \hline \textbf{\#} & \textbf{Konto} & \textbf{Teilsaldo} & \textbf{Saldo} \\
 \hline
 \hline
 \endhead
<?php $saldo = 0; foreach ($account["subAccounts"] as $code => $child_guid) { $subAccount = $accounts[$child_guid]; $accSaldo = $subAccount["saldo"]; $saldo += $accSaldo; ?>
 \hline \textbf{\hyperref[konto:<?php print($subAccount["guid"]) ?>]{<?php print($subAccount["code"]) ?>}} & \hyperref[konto:<?php print($subAccount["guid"]) ?>]{<?php print(latexSpecialChars($subAccount["label"])) ?>} & <?php print(latexFormatCurrency($accSaldo)) ?> & <?php print(latexFormatCurrency($saldo)) ?> \\
<?php } $ownSaldo = $account["saldo"] - $saldo; if ($ownSaldo != 0) { ?>
 \hline & Direkt gebucht & <?php print(latexFormatCurrency($ownSaldo)) ?> & <?php print(latexFormatCurrency($account["saldo"])) ?> \\
<?php } ?>
 \hline
 \hline
\end{longtable}
<?php } ?>

<?php if (!empty($account["transactions"])) { ?>
\subsubsection{Buchungen}
\begin{longtable}{R{1cm}L{1.3cm}L{6.2cm}R{2.2cm}R{2.2cm}R{2.5cm}}
 \hline
 \hline \textbf{\#} & \textbf{Beleg} & \textbf{Vorgang} & \textbf{Soll} & \textbf{Haben} & \textbf{Saldo} \\
 \hline
 \hline
 \endhead
<?php $saldo = 0; foreach ($account["transactions"] as $buchung) { ?>
 \hline \textbf{\hyperref[buchung:<?php print($buchung["id"]) ?>]{<?php print($buchung["id"]) ?>}} & \href{<?php print(getBelegUrl($year, $buchung["num"])) ?>}{<?php print(latexSpecialChars($buchung["num"])) ?>} & \multicolumn{4}{p{11cm}}{<?php print(latexSpecialChars($buchung["description"])) ?>} \\
<?php $i = 0; foreach ($buchung["splits"] as $split) { $i++; if ($split["account_guid"] == $account["guid"]) { $saldo -= $split["value"]; ?>
<?php if ($i < 3 || count($buchung["splits"])-$i < 3) { ?> \nopagebreak <?php } ?>
 \multicolumn{2}{l}{\hspace{2mm}<?php if ($i == 1) {print(date("d.m.Y", $buchung["date"]));} ?>} & <?php print(latexSpecialChars($split["memo"])) ?> & <?php if ($split["value"] > 0) print(latexFormatCurrency($split["value"])) ?> & <?php if ($split["value"] < 0) print(latexFormatCurrency((-1)*$split["value"])) ?> & <?php print(latexFormatCurrency($saldo)) ?> \\
<?php } ?>
<?php } } ?>
 \hline
 \hline
\end{longtable}
<?php } ?>
