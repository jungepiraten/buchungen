\documentclass[12pt,a4paper]{article}
\usepackage{geometry}
\usepackage{array}
\usepackage{textcomp}
\geometry{a4paper,left=1.5cm,right=1.5cm,top=2cm,bottom=2cm}
\usepackage{tabularx}
\usepackage{longtable}
\newcolumntype{L}[1]{>{\raggedright\arraybackslash}p{#1}} % linksbündig mit Breitenangabe
\newcolumntype{C}[1]{>{\centering\arraybackslash}p{#1}} % zentriert mit Breitenangabe
\newcolumntype{R}[1]{>{\raggedleft\arraybackslash}p{#1}} % rechtsbündig mit Breitenangabe
\usepackage{graphicx}
\usepackage[utf8]{inputenc}
\usepackage[pdfpagelabels]{hyperref}
\usepackage{lastpage}
\usepackage{fancyhdr}
\usepackage[ngerman]{babel}
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

{\fontfamily{pag}\selectfont\LARGE <?php print($year) ?>}

\vspace*{1cm}

\raggedright
\clearpage

\renewcommand{\thesection}{}
\renewcommand{\thesubsection}{}
\renewcommand{\thesubsubsection}{}

\setcounter{page}{1}
\lfoot{Stand: <?php print(date("d.m.Y")) ?>}
\cfoot{}
\rfoot{\thepage{} / \pageref{LastPage}}

\def\numberline#1{}
\setcounter{tocdepth}{2}
\tableofcontents
\clearpage

\footnotesize
\fontfamily{pcr}\selectfont
\newcounter{buchungno}

\section{Journal}

<?php
function getZeitPeriode($timestamp) {
	$ms = array("","Januar","Februar","März","April","Mai","Juni","Juli","August","September","Oktober","November","Dezember");
	return $ms[date("n",$timestamp)] . " " . date("Y", $timestamp);
}
function printJournal($b = 0) {
	global $journal, $year;
	if (count($journal) > $b) {
		$zeitPeriode = getZeitPeriode($journal[$b]["date"]);
?>
\subsection{<?php print(latexSpecialChars($zeitPeriode)) ?>}
\begin{longtable}{>{\refstepcounter{buchungno}}R{1cm}L{1.3cm}L{6.7cm}L{1.7cm}R{2.2cm}R{2.2cm}}
 \hline
 \hline \textbf{\#} & \textbf{Beleg} & \textbf{Vorgang} & \textbf{Konto} & \textbf{Soll} & \textbf{Haben} \\
 \hline
 \hline
 \endhead
<?php for (; $b<count($journal) && $zeitPeriode == getZeitPeriode($journal[$b]["date"]); $b++) { $buchung=$journal[$b]; ?>
 \hline \label{buchung:<?php print($buchung["id"]) ?>} \textbf{<?php print($buchung["id"]) ?>} & \href{<?php print(getBelegUrl($year, $buchung["num"])) ?>}{<?php print(latexSpecialChars($buchung["num"])) ?>} & \multicolumn{4}{p{11cm}}{<?php print(latexSpecialChars($buchung["description"])) ?>} \\
<?php $i=0; foreach ($buchung["splits"] as $split) { $i++; ?>
 \nopagebreak
 \multicolumn{2}{l}{\hspace{2mm}<?php print($i == 1 ? date("d.m.Y", $buchung["date"]) : "") ?>} & <?php print(latexSpecialChars($split["memo"])) ?> & \hyperref[konto:<?php print($split["account_guid"]) ?>]{<?php print($split["account_code"]) ?>} & <?php if ($split["value"] < 0) print(latexFormatCurrency((-1)*$split["value"])) ?> & <?php if ($split["value"] > 0) print(latexFormatCurrency($split["value"])) ?> \\
<?php } ?>
<?php } ?>
 \hline
 \hline
\end{longtable}
\clearpage
<?php
		printJournal($b);
	}
}
printJournal();

?>

\clearpage
\section{Kontobuch}
\label{kontenbuch}

\begin{longtable}{L{1.7cm}L{7cm}R{2.5cm}R{2.5cm}R{2.5cm}}
 \hline
 \hline \textbf{\#} & \textbf{Konto} & \textbf{Soll} & \textbf{Haben} & \textbf{Saldo} \\
 \hline
 \endhead
<?php $accountPrefixes = array(); foreach ($accounts as $account) {if (!empty($account["transactions"]) || $account["code"] != "") { $accountPrefixes[$account["guid"]] = (isset($accountPrefixes[$account["parent_guid"]]) ? $accountPrefixes[$account["parent_guid"]] : "") . "\hspace{5mm}"; ?>
 \hline \hyperref[konto:<?php print($account["guid"]) ?>]{<?php print($account["code"]) ?>} & \hyperref[konto:<?php print($account["guid"]) ?>]{<?php print($accountPrefixes[$account["guid"]] . latexSpecialChars($account["label"])) ?>} & <?php print(latexFormatCurrency($account["soll"])) ?> & <?php print(latexFormatCurrency($account["haben"])) ?> & <?php print(latexFormatCurrency($account["saldo"])) ?> \\
<?php } } ?>
 \hline
 \hline
\end{longtable}

<?php foreach ($accounts as $account) { if (!empty($account["transactions"]) || $account["code"] != "") { ?>
\clearpage
<?php include(dirname(__FILE__) . "/inline_kontoblatt.tex.php") ?>
<?php } } ?>

\end{document}
