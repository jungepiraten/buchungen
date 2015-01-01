<?php

switch ($_REQUEST["hidedetails"]) {
default:
case "0":	$details = 2;	break;
case "1":	$details = 1;	break;
case "2":	$details = 0;	break;
}

function getKostenrechnung($accountcode) {
	if ($accountcode == "R") {
		return "";
	}
	if (substr($accountcode,0,1) == "R") {
		return substr($accountcode,1);
	}
	return false;
}

function getFiBu($accountcode) {
	if (substr($accountcode,0,1) == "F") {
		return substr($accountcode,1);
	}
	return false;
}

function getEigenkapital($accountcode) {
	if (substr($accountcode,0,1) == "E") {
		return substr($accountcode,1);
	}
	return false;
}

?>
\documentclass[12pt,a4paper]{book}
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
\usepackage{multicol}
\usepackage{multirow}
\usepackage[utf8]{inputenc}
\usepackage[pdfpagelabels]{hyperref}
\usepackage{lastpage}
\usepackage{fancyhdr}
\usepackage[ngerman]{babel}
\usepackage{titlesec}
\titleformat{\chapter}{\huge}{}{0em}{}{}
\titlespacing{\chapter}{0pt}{-2.5em}{6pt}
\titleformat{\section}{\large}{}{0em}{}{}
\begin{document}

\makeatletter
<?php if ($details >= 1) { ?>
\renewcommand\chapter{\clearpage{\pagestyle{empty}\cleardoublepage}
                    \thispagestyle{fancy}
                    \secdef\@chapter\@schapter}
<?php } else { ?>
\renewcommand\chapter{\clearpage\pagestyle{empty}
                    \thispagestyle{empty}
                    \secdef\@chapter\@schapter}
<?php } ?>
\makeatother

<?php if ($details >= 1) { ?>
\pagestyle{empty}
\centering
\fontencoding{T1}
\fontfamily{pag}\selectfont
\large

\vspace*{2cm}

\includegraphics{logo.png}

\vspace*{0cm}

{\fontfamily{pag}\selectfont\Huge <?php $D = array("", "Jahresabschluss", "Buchhaltung"); print($D[$details]) ?>}

\vspace*{1.5cm}

{\fontfamily{pag}\selectfont\LARGE <?php print($year) ?>}

\vspace*{1cm}

\raggedright
\clearpage

\renewcommand{\chaptername}{}
\renewcommand{\thechapter}{}
\renewcommand{\thesection}{}
\renewcommand{\thesubsection}{}
\renewcommand{\thesubsubsection}{}
\renewcommand{\chaptermark}[1]{ \markboth{\MakeUppercase{#1}}{} }
\renewcommand{\sectionmark}[1]{ \markright{\MakeUppercase{#1}}{} }

% \def\numberline#1{}
\setcounter{tocdepth}{2}
\tableofcontents
<?php } ?>

\footnotesize
\fontfamily{pcr}\selectfont
\newcounter{buchungno}

<?php

$cats = array("bilanz-aktiva" => array(), "bilanz-passiva" => array(), "guv-ideell" => array(), "guv-vermoegen" => array(), "guv-zweck" => array(), "guv-wirtschaft" => array());
$gesamt = array(-1 => 0, 1 => 0);
$sums = array_combine(array_keys($cats), array_fill(0, count($cats), $gesamt));
$assignment = array("F0" => "bilanz", "F1" => "bilanz", "F2" => "guv-ideell", "F3" => "guv-ideell", "F4" => "guv-vermoegen", "F5" => "guv-zweck", "F6" => "guv-zweck", "F7" => "guv-wirtschaft", "F8" => "guv-wirtschaft");
$erfolg = 0;
foreach ($accounts as $account) {
	if (isset($assignment[substr($account["code"],0,2)]) && $account["saldo"] != 0) {
		$cats[$assignment[substr($account["code"],0,2)]][] = array(
			"guid" => $account["guid"],
			"code" => getFiBu($account["code"]),
			"label" => $account["label"],
			"saldo" => $account["saldo"],
//			"saldoSign" => $account["saldoSign"],
			// $account["saldoSign"] ist unabhaengig vom tatsaechlichen Saldo. Bilanzposten koennen aber die Seite wechseln (Kreditkarte, Bankkonten)
			"saldoSign" => $account["saldo"] < 0 ? -1 : 1,
		);
		if (substr($assignment[substr($account["code"],0,2)],0,3) == "guv") {
			$gesamt[$account["saldoSign"]] += $account["saldo"];
		}
		$sums[$assignment[substr($account["code"],0,2)]][$account["saldoSign"]] += $account["saldo"];
	}
	if (in_array(substr($account["code"],0,2), array("F2","F3","F4","F5","F6","F7","F8"))) {
		$gewinn += $account["saldo"];
	}
}

$cats["bilanz"][] = array(
	"guid" => "guv",
	"code" => "GUV",
	"label" => "Jahresergebnis",
//	"label" => $gewinn < 0 ? "Jahresüberschuss" : "Jahresfehlbetrag",
	"saldo" => $gewinn,
	"saldoSign" => -1,
);

function printBilanz($bereich, $saldoSign) {
?>
\begin{longtable}{L{1.7cm}L{12cm}R{2.5cm}}
 \hline
 \hline \textbf{Konto} & \textbf{Bezeichnung} & \textbf{Betrag} \\
 \hline
 \hline
 \endhead
<?php $sum = 0; foreach ($bereich as $account) { if ($account["saldoSign"] == $saldoSign) { $sum += $account["saldo"]; ?>
 \hline \hyperref[<?php if ($account["guid"] != "guv") { ?>konto:<?php } ?><?php print($account["guid"]) ?>]{<?php print($account["code"]) ?>} & \hyperref[<?php if ($account["guid"] !=	"guv") { ?>konto:<?php } ?><?php print($account["guid"]) ?>]{<?php print(latexSpecialChars($account["label"])) ?>} & <?php print(latexFormatCurrency($account["saldo"] * $saldoSign )) ?> \\
<?php } } ?>
 \hline
 \hline & \textbf{Gesamt} & \textbf{<?php print(latexFormatCurrency($sum * $saldoSign)) ?>} \\
 \hline
 \hline
\end{longtable}
<?php
}

function printBereich($label, $bereich, $saldoSign) {
	if (count($bereich) > 0) {
?>
\section*{<?php print(latexSpecialChars($label)) ?>}
\begin{longtable}{L{1.7cm}L{9.5cm}R{2.5cm}R{2.5cm}}
 \hline
 \hline \textbf{SKR49} & \textbf{Konto} & \textbf{Einnahmen} & \textbf{Ausgaben} \\
 \hline
 \hline
 \endhead
<?php $sums = array(-1 => 0, 1 => 0); foreach ($bereich as $account) { $sums[$account["saldoSign"]] += $account["saldo"]; ?>
 \hline \hyperref[konto:<?php print($account["guid"]) ?>]{<?php print($account["code"]) ?>} & \hyperref[konto:<?php print($account["guid"]) ?>]{<?php print(latexSpecialChars($account["label"])) ?>} & <?php if ($account["saldo"] * $saldoSign > 0) print(latexFormatCurrency($account["saldo"] * $saldoSign )) ?> & <?php if ($account["saldo"] * $saldoSign < 0) print(latexFormatCurrency($account["saldo"] * $saldoSign * -1)) ?> \\
<?php } ?>
 \hline
 \hline & \textbf{Summe} & \textbf{<?php print(latexFormatCurrency($sums[-1] * $saldoSign)) ?>} & \textbf{<?php print(latexFormatCurrency($sums[1] * -1 * $saldoSign)) ?>} \\
 \hline
 \hline
\end{longtable}
<?php
	}
}
?>

\chapter{Bilanz}
\label{bilanz}

\pagestyle{fancy}
\fancyhead{}
\fancyhead[LE]{\leftmark}
\fancyhead[RO]{\rightmark}
\fancyfoot{}
\fancyfoot[C]{Stand: <?php print(date("d.m.Y")) ?>}
\fancyfoot[RO,LE]{\thepage{} / \pageref{LastPage}}
\setcounter{page}{1}

\section*{Aktiva}
<?php printBilanz($cats["bilanz"], 1) ?>

\section*{Passiva}
<?php printBilanz($cats["bilanz"], -1) ?>

\chapter{Gewinn und Verlustrechnung}
\label{guv}

\begin{longtable}{L{8.7cm}R{2.5cm}R{2.5cm}R{2.5cm}}
 \hline
 \hline  & \textbf{Einnahmen} & \textbf{Ausgaben} & \textbf{Saldo} \\
 \hline
 \hline
 \endhead
 \hline Ideeller Bereich & <?php print(latexFormatCurrency($sums["guv-ideell"][-1]*-1)) ?> & <?php print(latexFormatCurrency($sums["guv-ideell"][1])) ?> & <?php print(latexFormatCurrency(($sums["guv-ideell"][-1] + $sums["guv-ideell"][1])*-1)) ?> \\
 \hline Vermögensverwaltung & <?php print(latexFormatCurrency($sums["guv-vermoegen"][-1]*-1)) ?> & <?php print(latexFormatCurrency($sums["guv-vermoegen"][1])) ?> & <?php print(latexFormatCurrency(($sums["guv-vermoegen"][-1] + $sums["guv-vermoegen"][1])*-1)) ?> \\
 \hline Zweckbetriebe & <?php print(latexFormatCurrency($sums["guv-zweck"][-1]*-1)) ?> & <?php print(latexFormatCurrency($sums["guv-zweck"][1])) ?> & <?php print(latexFormatCurrency(($sums["guv-zweck"][-1] + $sums["guv-zweck"][1])*-1)) ?> \\
 \hline Wirtschaftliche Geschäftsbetriebe & <?php print(latexFormatCurrency($sums["guv-wirtschaft"][-1]*-1)) ?> & <?php print(latexFormatCurrency($sums["guv-wirtschaft"][1])) ?> & <?php print(latexFormatCurrency(($sums["guv-wirtschaft"][-1] + $sums["guv-wirtschaft"][1])*-1)) ?> \\
 \hline
 \hline \textbf{Summe} & \textbf{<?php print(latexFormatCurrency($gesamt[-1]*-1)) ?>} & \textbf{<?php print(latexFormatCurrency($gesamt[1])) ?>} & \textbf{<?php print(latexFormatCurrency(($gesamt[-1] + $gesamt[1])*-1)) ?>} \\
 \hline
 \hline
\end{longtable}

<?php printBereich("Ideeller Bereich", $cats["guv-ideell"], -1) ?>
<?php printBereich("Vermögensverwaltung", $cats["guv-vermoegen"], -1) ?>
<?php printBereich("Zweckbetriebe", $cats["guv-zweck"], -1) ?>
<?php printBereich("Wirtschaftliche Geschäftsbetriebe", $cats["guv-wirtschaft"], -1) ?>

<?php if ($details >= 1) { ?>
\chapter{Rechnungsabgrenzungsposten}
\label{rap}

<?php

function printRAP($code) {
	global $accounts_code2guid, $accounts;

	$guid = $accounts_code2guid[$code];
	$account = $accounts[$guid];

?>
\begin{longtable}{R{1cm}L{1.3cm}L{8.4cm}R{2.2cm}R{2.5cm}}
 \hline
 \hline \textbf{\#} & \textbf{Beleg} & \textbf{Vorgang} & \textbf{Betrag} & \textbf{Saldo} \\
 \hline
 \hline
 \endhead
<?php if (!empty($account["transactions"])) { $saldo = 0; foreach ($account["transactions"] as $buchung) { ?>
 \hline \textbf{\hyperref[buchung:<?php print($buchung["id"]) ?>]{<?php print($buchung["id"]) ?>}} & \href{<?php print(getBelegUrl($year, $buchung["num"])) ?>}{<?php print(latexSpecialChars($buchung["num"])) ?>} & \multicolumn{3}{p{11cm}}{<?php print(latexSpecialChars($buchung["description"])) ?>} \\
<?php $i = 0; foreach ($buchung["splits"] as $split) { if ($split["account_guid"] == $guid) { $i++; $saldo += $split["value"]*$account["saldoSign"]; ?>
<?php if ($i < 3) { ?> \nopagebreak <?php } ?>
 \multicolumn{2}{l}{\hspace{2mm}<?php if ($i == 1) {print(date("d.m.Y", $buchung["date"]));} ?>} & <?php print(latexSpecialChars($split["memo"])) ?> & <?php print(latexFormatCurrency($account["saldoSign"]*$split["value"])) ?> & <?php print(latexFormatCurrency($saldo)) ?> \\
<?php } ?>
<?php } } } else { ?>
 \hline \multicolumn{5}{l}{Keine abgegrenzten Posten vorhanden} \\
<?php } ?>
 \hline
 \hline
\end{longtable}
<?php
}

?>

\section*{Aktiva}
<?php printRAP("F0990") ?>

\section*{Passiva}
<?php printRAP("F1990") ?>

<?php } ?>

<?php if ($details >= 2) { ?>
\chapter{Journal}

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
\section{<?php print(latexSpecialChars($zeitPeriode)) ?>}
\begin{longtable}{>{\refstepcounter{buchungno}}R{1cm}L{1.3cm}L{6.7cm}L{1.7cm}R{2.2cm}R{2.2cm}}
 \hline
 \hline \textbf{\#} & \textbf{Beleg} & \textbf{Vorgang} & \textbf{Konto} & \textbf{Soll} & \textbf{Haben} \\
 \hline
 \hline
 \endhead
<?php for (; $b<count($journal) && $zeitPeriode == getZeitPeriode($journal[$b]["date"]); $b++) { $buchung=$journal[$b]; ?>
 \hline \label{buchung:<?php print($buchung["id"]) ?>} \textbf{<?php print($buchung["id"]) ?>} & \href{<?php print(getBelegUrl($year, $buchung["num"])) ?>}{<?php print(latexSpecialChars($buchung["num"])) ?>} & \multicolumn{4}{p{14cm}}{<?php print(latexSpecialChars($buchung["description"])) ?>} \\
<?php $i=0; foreach ($buchung["splits"] as $split) { if (getFiBu($split["account_code"]) !== false) { $i++; ?>
<?php if ($i < 3 || count($buchung["splits"])-$i < 10) { ?> \nopagebreak <?php } ?>
 \multicolumn{2}{l}{\hspace{2mm}<?php print($i == 1 ? date("d.m.Y", $buchung["date"]) : "") ?>} & <?php print(latexSpecialChars($split["memo"])) ?> & \hyperref[konto:<?php print($split["account_guid"]) ?>]{<?php print(getFiBu($split["account_code"])) ?>} & <?php if ($split["value"] < 0) print(latexFormatCurrency((-1)*$split["value"])) ?> & <?php if ($split["value"] > 0) print(latexFormatCurrency($split["value"])) ?> \\
<?php } } } ?>
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
<?php } ?>

<?php if ($details >= 1) { ?>
\chapter{Kontobuch}
\label{kontenbuch}

\begin{longtable}{L{1.7cm}L{7cm}R{2.5cm}R{2.5cm}R{2.5cm}}
 \hline
 \hline \textbf{\#} & \textbf{Konto} & \textbf{Soll} & \textbf{Haben} & \textbf{Saldo} \\
 \hline
 \hline
 \endhead
<?php foreach ($accounts as $account) { if (getFiBu($account["code"]) !== false && (!empty($account["transactions"]) || !empty($account["subAccounts"]))) { ?>
 \hline \hyperref[konto:<?php print($account["guid"]) ?>]{<?php print(getFiBu($account["code"])) ?>} & \hyperref[konto:<?php print($account["guid"]) ?>]{<?php print(latexSpecialChars($account["label"])) ?>} & <?php print(latexFormatCurrency($account["soll"])) ?> & <?php print(latexFormatCurrency($account["haben"])) ?> & <?php print(latexFormatCurrency($account["saldo"]*$account["saldoSign"])) ?> \\
<?php } } ?>
 \hline
 \hline
\end{longtable}

<?php if ($details >= 2) { ?>
<?php foreach ($accounts as $account) { if (getFiBu($account["code"]) !== false && (!empty($account["transactions"]) || !empty($account["subAccounts"]))) { ?>
\clearpage
<?php include(dirname(__FILE__) . "/inline_kontoblatt.tex.php") ?>
<?php } } ?>

\chapter{Belegverzeichnis}
\label{nums}

\begin{multicols}{3}
<?php $lastNum = null; foreach ($nums as $num => $n) { ?>
 <?php if ($lastNum === null || preg_replace('/\d+$/','',$num) != preg_replace('/\d+$/','',$lastNum)) { ?>
<?php if ($lastNum !== null) { ?>\end{description}<?php } ?>
\section*{<?php print(latexSpecialChars(trim(preg_replace('/\d+$/','',$num),"_"))) ?>}
<?php print(latexSpecialChars(getBelegkreisDescription(trim(preg_replace('/\d+$/','',$num),"_")))) ?>
\begin{description}
<?php } ?>
 \item [\href{<?php print(getBelegUrl($year, $num)) ?>}{<?php print(latexSpecialChars($num)) ?>}] {<?php foreach ($n["transactions"] as $tid) { ?> \hyperref[buchung:<?php print($tid) ?>]{<?php print(latexSpecialChars($tid)) ?>}<?php } ?>}
<?php $lastNum = $num;} ?>
<?php if ($lastNum !== null) { ?>\end{description}<?php } ?>
\end{multicols}
<?php } ?>

<?php
function printPartners($p, $details) {
	global $partners, $year;
	$factor = ($p == "K" ? -1 : 1);

	$totals = array("solL"=>0,"haben"=>0);
	$sums = array();
?>
\begin{longtable}{L{1.4cm}L{7cm}R{2.5cm}R{2.5cm}R{2.5cm}}
 \hline
 \hline \textbf{\#} & \textbf{Partner*in} & \textbf{Rechnung} & \textbf{Bezahlt} & \textbf{Offen} \\
 \hline
 \hline
 \endhead
<?php
	foreach ($partners as $partner => $info) {
		if (substr($partner,0,1) == $p) {
			$sums[$partner] = array("soll"=>0,"haben"=>0);
			foreach ($info["lots"] as $lot => $transactions) {
				foreach ($transactions as $tx) {
					$sums[$partner][$tx["split"]["value"] * $factor > 0 ? "haben" : "soll"] += $tx["split"]["value"];
					$totals[$tx["split"]["value"] * $factor > 0 ? "haben" : "soll"] += $tx["split"]["value"];
				}
			}
?>
 \hyperref[partner:<?php print(latexSpecialChars($partner)) ?>]{<?php print(latexSpecialChars(substr($partner,1))) ?>} & \hyperref[partner:<?php print(latexSpecialChars($partner)) ?>]{<?php print(latexSpecialChars($info["account"]["label"])) ?>} & <?php print(latexFormatCurrency($sums[$partner]["haben"] * $factor)) ?> & <?php print(latexFormatCurrency($sums[$partner]["soll"] * -1 * $factor)) ?> & <?php print(latexFormatCurrency(($sums[$partner]["soll"]+$sums[$partner]["haben"]) * $factor)) ?> \\
 \hline
<?php
		}
	}
?>
 \hline
 \hline & \textbf{Summe} & \textbf{<?php print(latexFormatCurrency($totals["haben"] * $factor)) ?>} & \textbf{<?php print(latexFormatCurrency($totals["soll"] * -1 * $factor)) ?>} & \textbf{<?php print(latexFormatCurrency(($totals["soll"]+$totals["haben"]) * $factor)) ?>} \\
 \hline
 \hline
\end{longtable}
\clearpage
<?php

	foreach ($partners as $partner => $info) {
		if (substr($partner,0,1) == $p) {
			if ($details >= 2 || $sums[$partner]["haben"]*(-1) != $sums[$partner]["soll"]) {
?>
\section{<?php print(latexSpecialChars(substr($partner,1))) ?> <?php print(latexSpecialChars($info["account"]["label"])) ?>}
\label{partner:<?php print(latexSpecialChars($partner)) ?>}
\begin{longtable}{L{8cm}R{1.3cm}L{1.3cm}L{2cm}R{2.5cm}}
 \hline
 \hline \textbf{Rechnung} & \textbf{Buchung} & \textbf{Beleg} & \textbf{Datum} & \textbf{Betrag} \\
 \hline
 \hline
 \endhead
<?php
				foreach ($info["lots"] as $lot => $transactions) {
					if ($details >= 2 || array_sum(array_map(create_function('$tx', 'return $tx["split"]["value"];'), $transactions)) != 0) {
?>
\multirow{<?php print(count($transactions)) ?>}{8cm}{<?php print(latexSpecialChars($lot)) ?>} & <?php
						$i = 0;
						foreach ($transactions as $tx) {
?> <?php if ($i++ > 0) { ?> & <?php } ?> \hyperref[buchung:<?php print($tx["tx"]["id"]) ?>]{<?php print($tx["tx"]["id"]) ?>} & \href{<?php print(getBelegUrl($year, $tx["tx"]["num"])) ?>}{<?php print(latexSpecialChars($tx["tx"]["num"])) ?>} & <?php print(date("d.m.Y", $tx["tx"]["date"])) ?> & <?php print(latexFormatCurrency($tx["split"]["value"] * $factor)) ?> \\
<?php
						}
?> \hline <?php
					}
				}

				if ($sums[$partner]["soll"]+$sums[$partner]["haben"] == 0) {
?>
 \hline
 \hline
 \multicolumn{4}{l}{\textbf{Umsatz}} & \textbf{<?php print(latexFormatCurrency($sums[$partner]["haben"] * $factor)) ?>} \\
<?php
				} else {
					if ($details >= 2) {
?>
 \hline
 \hline
 \multicolumn{4}{l}{\textbf{Summe Rechnung}} & \textbf{<?php print(latexFormatCurrency($sums[$partner]["haben"] * $factor)) ?>} \\ \nopagebreak
 \multicolumn{4}{l}{\textbf{Summe Bezahlt}} & \textbf{<?php print(latexFormatCurrency($sums[$partner]["soll"] * -1 * $factor)) ?>} \\ \nopagebreak
<?php } ?>
 \hline
 \multicolumn{4}{l}{\textbf{Differenz (Offene Posten)}} & \textbf{<?php print(latexFormatCurrency(($sums[$partner]["soll"]+$sums[$partner]["haben"]) * $factor)) ?>} \\
<?php
				}
?>
 \hline
 \hline
\end{longtable}
<?php
			}
		}
	}
}
?>

\chapter{Debitoren}
\label{debitoren}

<?php printPartners("D", $details); ?>

\chapter{Kreditoren}
\label{kreditoren}

<?php printPartners("K", $details); ?>

\chapter{Eigenkapital}
\label{eigenkapital}

\begin{longtable}{L{0.8cm}L{7cm}R{2.5cm}R{2.5cm}R{2.5cm}}
 \hline
 \hline \textbf{\#} & \textbf{Gliederung} & \textbf{Vorjahr} & \textbf{Saldo <?php print($year) ?>} & \textbf{Endbestand} \\
 \hline
 \hline
 \endhead
<?php $sums = array("v" => 0, "s" => 0); foreach ($accounts as $account) { if (getEigenkapital($account["code"]) !== false) { $kstaccount = $accounts[$accounts_code2guid["R".getEigenkapital($account["code"])]]; $sums["v"] += $account["saldo"]*-1; $sums["s"] += $kstaccount["saldo"]*-1; ?>
 \hline \hyperref[konto:<?php print($kstaccount["guid"]) ?>]{<?php print(getEigenkapital($account["code"])) ?>} & \hyperref[konto:<?php print($kstaccount["guid"]) ?>]{<?php print(latexSpecialChars($account["label"])) ?>} & <?php print(latexFormatCurrency($account["saldo"]*-1)) ?> & <?php print(latexFormatCurrency($kstaccount["saldo"]*-1)) ?> & <?php print(latexFormatCurrency($account["saldo"]*(-1)-$kstaccount["saldo"])) ?> \\
<?php } } ?>
 \hline
 \hline & \textbf{Summe} & \textbf{<?php print(latexFormatCurrency($sums["v"])) ?>} & \textbf{<?php print(latexFormatCurrency($sums["s"])) ?>} & \textbf{<?php print(latexFormatCurrency($sums["v"]+$sums["s"]))} ?> \\
 \hline
 \hline
\end{longtable}

<?php if ($details >= 2) { ?>

\chapter{Kostenrechnung}
\label{kostenstellen}

\begin{longtable}{L{1.7cm}L{7cm}R{2.5cm}R{2.5cm}R{2.5cm}}
 \hline
 \hline \textbf{\#} & \textbf{Kostenstelle} & \textbf{Soll} & \textbf{Haben} & \textbf{Saldo} \\
 \hline
 \hline
 \endhead
<?php foreach ($accounts as $account) { if (getKostenrechnung($account["code"]) !== false && $account["code"] !== "R" && (!empty($account["transactions"]) || !empty($account["subAccounts"]))) { if ($details >= 2 || $account["level"] <= 2) { ?>
 \hline \hyperref[konto:<?php print($account["guid"]) ?>]{<?php print(getKostenrechnung($account["code"])) ?>} & \hyperref[konto:<?php print($account["guid"]) ?>]{<?php print(latexSpecialChars($account["label"])) ?>} & <?php print(latexFormatCurrency($account["soll"])) ?> & <?php print(latexFormatCurrency($account["haben"])) ?> & <?php print(latexFormatCurrency($account["saldo"]*$account["saldoSign"])) ?> \\
<?php } } } ?>
 \hline
 \hline
\end{longtable}

<?php if ($details >= 2) { ?>
<?php foreach ($accounts as $account) { if (getKostenrechnung($account["code"]) !== false && $account["code"] !== "R" && (!empty($account["transactions"]) || !empty($account["subAccounts"]))) { ?>
\clearpage
<?php include(dirname(__FILE__) . "/inline_kontoblatt.tex.php") ?>
<?php } } ?>
<?php } ?>

<?php } ?>

<?php } ?>

\end{document}
