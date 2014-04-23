\subsection{<?php print($account["code"]) ?> <?php print(latexSpecialChars($account["label"])) ?>}

<?php if ($account["parent_guid"] && $accounts[$account["parent_guid"]]["code"]) { ?>\hyperref[konto:<?php print($account["parent_guid"]) ?>]{Hauptkonto: <?php print($accounts[$account["parent_guid"]]["code"]) ?> <?php print(latexSpecialChars($accounts[$account["parent_guid"]]["label"])) ?>} \\<?php } ?>
<?php print(latexSpecialChars($account["description"])) ?>

<?php if (!empty($account["subAccounts"])) { ?>
\subsubsection{Unterkonten}
\begin{longtable}{p{1.5cm}p{9.5cm}R{2.5cm}R{2.5cm}}
 \hline
 \hline \textbf{\#} & \textbf{Konto} & \textbf{Saldo} & \textbf{Saldo} \\
 \hline
 \endhead
 \hline
 \hline
 \endfoot
<?php $saldo = 0; foreach ($account["subAccounts"] as $code => $child_guid) { $subAccount = $accounts[$child_guid]; $accSaldo = $subAccount["saldo"]; $saldo += $accSaldo; ?>
 \hline \textbf{\hyperref[konto:<?php print($subAccount["guid"]) ?>]{<?php print($subAccount["code"]) ?>}} & \hyperref[konto:<?php print($subAccount["guid"]) ?>]{<?php print(latexSpecialChars($subAccount["label"])) ?>} & <?php printf("%.2f \\texteuro",$accSaldo) ?> & <?php printf("%.2f \\texteuro",$saldo) ?> \\
<?php } $ownSaldo = $account["saldo"] - $saldo; if ($ownSaldo != 0) { ?>
 \hline & Direkt gebucht & <?php printf("%.2f \\texteuro", $ownSaldo) ?> & <?php printf("%.2f \\texteuro", $account["saldo"]) ?> \\
<?php } ?>
 \hline
\end{longtable}
<?php } ?>

<?php if (!empty($account["transactions"])) { ?>
\subsubsection{Buchungen}
\begin{longtable}{p{1cm}p{1.5cm}p{6cm}R{2.2cm}R{2.2cm}R{2.5cm}}
 \hline
 \hline \textbf{\#} & \textbf{Beleg} & \textbf{Vorgang} & \textbf{Soll} & \textbf{Haben} & \textbf{Saldo} \\
 \hline
 \endhead
 \hline
 \hline
 \endfoot
<?php $saldo = 0; foreach ($account["transactions"] as $buchung) { ?>
 \hline \textbf{\hyperref[buchung:<?php print($buchung["id"]) ?>]{<?php print($buchung["id"]) ?>}} & \href{<?php print(getBelegUrl($year, $buchung["num"])) ?>}{<?php print(latexSpecialChars($buchung["num"])) ?>} & \multicolumn{4}{p{11cm}}{<?php print(latexSpecialChars($buchung["description"])) ?>} \\
<?php $firstline = true; foreach ($buchung["splits"] as $split) { if ($split["account_guid"] == $account["guid"]) { $saldo -= $split["value"]; ?>
 \nopagebreak
 \multicolumn{2}{l}{\hspace{1cm}<?php if ($firstline) {print(date("d.m.Y", $buchung["date"])); $firstline = false;} ?>} & <?php print(latexSpecialChars($split["memo"])) ?> & <?php if ($split["value"] > 0) printf("%.2f \\texteuro",$split["value"]) ?> & <?php if ($split["value"] < 0) printf("%.2f \\texteuro",(-1)*$split["value"]) ?> & <?php printf("%.2f \\texteuro",$saldo) ?> \\
<?php } ?>
<?php } } ?>
 \hline
\end{longtable}
<?php } ?>
