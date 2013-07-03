\subsection{<?php print($account["code"]) ?> <?php print(latexSpecialChars($account["label"])) ?>}

<?php print(latexSpecialChars($account["description"])) ?>

\begin{longtable}{p{1cm}p{1.5cm}p{6cm}rrr}
 \hline
 \hline \textbf{\#} & \textbf{Beleg} & \textbf{Vorgang} & \textbf{Soll} & \textbf{Haben} & \textbf{Saldo} \\
 \hline
 \endhead
 \hline
 \hline
 \endfoot
<?php if (isset($account["transactions"])) { $saldo = 0; foreach ($account["transactions"] as $buchung) { ?>
 \hline \textbf{<?php print($buchung["id"]) ?>} & <?php print($buchung["num"]) ?> & \multicolumn{4}{p{11cm}}{<?php print(latexSpecialChars($buchung["description"])) ?>} \\
<?php foreach ($buchung["splits"] as $split) { if ($split["account_guid"] == $account["guid"]) { $saldo -= $split["value"]; ?>
 \nopagebreak
 \multicolumn{2}{l}{\hspace{1cm}<?php print(date("d.m.Y", $buchung["date"])) ?>} & <?php print(latexSpecialChars($split["memo"])) ?> & <?php if ($split["value"] > 0) printf("%.2f EUR",$split["value"]) ?> & <?php if ($split["value"] < 0) printf("%.2f EUR",(-1)*$split["value"]) ?> & <?php printf("%.2f EUR",$saldo) ?> \\
<?php } ?>
<?php } } } ?>
 \hline
\end{longtable}
