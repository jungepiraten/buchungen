\documentclass[12pt,a4paper]{article}
\usepackage{geometry}
\geometry{a4paper,left=2cm,right=1cm,top=2cm,bottom=2cm}
\usepackage{tabularx}
\usepackage{longtable}
\usepackage{graphicx}
\usepackage[utf8]{inputenc}
\usepackage[pdfpagelabels]{hyperref}
\usepackage{totpages}
\usepackage{fancyhdr}
\pagestyle{fancy}
\thispagestyle{empty}
\begin{document}

\centering
\fontencoding{T1}
\fontfamily{pag}\selectfont
\large

\vspace*{2cm}

\includegraphics{logo.png}

\vspace*{0cm}

{\fontfamily{pag}\selectfont\Huge Buchhaltung}

\vspace*{1.5cm}

{\fontfamily{pag}\selectfont\LARGE Kassenbuch}

\vspace*{1cm}

\clearpage

\renewcommand{\thesection}{}
\renewcommand{\thesubsection}{}

\setcounter{page}{1}
\lfoot{}
\cfoot{}
\rfoot{\thepage{} / \ref{TotPages}}

\def\numberline#1{}
\tableofcontents

\clearpage

\footnotesize
\fontfamily{pcr}\selectfont

\section{Journal}

\begin{longtable}{p{1cm}p{1.5cm}p{7cm}p{1cm}rr}
 \hline
 \hline \textbf{\#} & \textbf{Beleg} & \textbf{Vorgang} & \textbf{Konto} & \textbf{Soll} & \textbf{Haben} \\
 \hline
 \endhead
 \hline
 \hline
 \endfoot
<?php foreach ($journal as $buchung) { ?>
 \hline \textbf{<?php print($buchung["id"]) ?>} & <?php print($buchung["num"]) ?> & \multicolumn{4}{p{11cm}}{<?php print(latexSpecialChars($buchung["description"])) ?>} \\
<?php $i=0; foreach ($buchung["splits"] as $split) { $i++; ?>
 \nopagebreak
 \multicolumn{2}{l}{\hspace{1cm}<?php print($i == 1 ? date("d.m.Y", $buchung["date"]) : "") ?>} & <?php print(latexSpecialChars($split["memo"])) ?> & <?php print($split["account_code"]) ?> & <?php if ($split["value"] < 0) printf("%.2f EUR",(-1)*$split["value"]) ?> & <?php if ($split["value"] > 0) printf("%.2f EUR",$split["value"]) ?> \\
<?php } ?>
<?php } ?>
 \hline
\end{longtable}

\clearpage
\section{Kontobuch}

\begin{longtable}{p{1cm}|p{13cm}r}
 \hline
 \hline \textbf{\#} & \textbf{Konto} & \textbf{Buchungen} \\
 \hline
 \endhead
 \hline
 \hline
 \endfoot
<?php $accountPrefixes = array(); foreach ($accounts as $account) {if (isset($account["transactions"]) || $account["code"] != "") { $accountPrefixes[$account["guid"]] = (isset($accountPrefixes[$account["parent_guid"]]) ? $accountPrefixes[$account["parent_guid"]] : "") . "\hspace{5mm}"; ?>
 \hline <?php print($account["code"]) ?> & <?php print($accountPrefixes[$account["guid"]] . latexSpecialChars($account["label"])) ?> & <?php print(isset($account["transactions"]) ? count($account["transactions"]) : "-") ?> \\
<?php } } ?>
 \hline
\end{longtable}

<?php foreach ($accounts as $account) {if (isset($account["transactions"])) { ?>
\clearpage
\subsection{<?php print($account["code"]) ?> <?php print(latexSpecialChars($account["label"])) ?>}

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
<?php foreach ($buchung["splits"] as $split) { if ($split["account_guid"] == $account["guid"]) { $saldo += $split["value"]; ?>
 \nopagebreak
 \multicolumn{2}{l}{\hspace{1cm}<?php print(date("d.m.Y", $buchung["date"])) ?>} & <?php print(latexSpecialChars($split["memo"])) ?> & <?php if ($split["value"] > 0) printf("%.2f EUR",$split["value"]) ?> & <?php if ($split["value"] < 0) printf("%.2f EUR",(-1)*$split["value"]) ?> & <?php printf("%.2f EUR",$saldo) ?> \\
<?php } ?>
<?php } } } ?>
 \hline
\end{longtable}
<?php } } ?>

\end{document}
