\documentclass{article}
\usepackage{textcomp}
\usepackage[newdimens]{labels}
\LabelCols=3
\LabelRows=8
% \LabelGridtrue
\LeftLabelBorder=5mm
\TopLabelBorder=10mm
\RightLabelBorder=5mm
\BottomLabelBorder=10mm
\LeftPageMargin=7mm
\TopPageMargin=20mm
\RightPageMargin=15mm
\BottomPageMargin=-10mm
\begin{document}
\fontfamily{pag}\selectfont
\begin{labels}

<?php
$offset = isset($_REQUEST["offset"]) ? intval($_REQUEST["offset"]) : 0;
for ($i=0;$i<$offset;$i++) {
	print("\\ \n\n\n");
}

$enterdate = isset($_REQUEST["last"]) ? intval($_REQUEST["last"]) : null;

$belege = array();
foreach ($partners as $partner => $info) { foreach ($info["lots"] as $lot => $transactions) {
	$rechnung = $bezahlt = $sachkonten = $kostenstellen = array();
	$lastEnterDate = null;
	foreach ($transactions as $data) {
		$lastEnterDate = max($lastEnterDate, $data["tx"]["enter_date"]);
		if (in_array(substr($data["tx"]["num"],0,2), array("RE","ER"))) {
			$rechnung[] = array("num" => $data["tx"]["num"], "date" => $data["tx"]["date"], "value" => $data["split"]["value"]);
		} else {
			$bezahlt[] = array("num" => $data["tx"]["num"], "date" => $data["tx"]["date"], "value" => $data["split"]["value"]);
		}
		$sachkonten = array_merge($sachkonten, array_map(create_function('$s', 'return substr($s["account_code"],1);'), array_filter($data["tx"]["splits"], create_function('$s', 'return in_array(substr($s["account_code"],0,2), array("F2","F3","F4","F5","F6","F7","F8"));'))));
		$kostenstellen = array_merge($kostenstellen, array_map(create_function('$s', 'return substr($s["account_code"],1);'), array_filter($data["tx"]["splits"], create_function('$s', 'return strlen($s["account_code"]) > 1 && in_array(substr($s["account_code"],0,1), array("R"));'))));
	}
	foreach ($rechnung as $re) {
		if (!isset($belege[$re["num"]])) {
			$belege[$re["num"]] = array();
		}
		$belege[$re["num"]][] = array(
			"lastEnterDate" => $lastEnterDate,
			"rechnung" => $re,
			"partner" => $partner,
			"sachkonten" => $sachkonten,
			"kostenstellen" => $kostenstellen,
			"bezahlt" => $bezahlt,
		);
	}
} }

if (empty($belege)) {
	echo "Keine Belege vorhanden";
}

ksort($belege);

foreach ($belege as $beleg => $rechnungen) {
	foreach ($rechnungen as $rechnung) {
		if ($enterdate === null || $enterdate <= $rechnung["lastEnterDate"]) {
?>
\textbf{<?php print(latexSpecialChars($beleg)) ?>}
\textit{Kreditorennummer}: <?php print(latexSpecialChars(substr($rechnung["partner"],1))) ?>

\textit{Betrag}: <?php print(latexFormatCurrency(abs($rechnung["rechnung"]["value"]))) ?>

\textit{Sachkonten}: <?php print(latexSpecialChars(implode(", ", $rechnung["sachkonten"]))) ?>

\textit{Kostenstellen}: <?php print(latexSpecialChars(implode(", ", $rechnung["kostenstellen"]))) ?>

\textit{Gezahlt}: <?php print(latexSpecialChars(implode(", ", array_map(create_function('$b', 'return date("d.m.Y",$b["date"])." (".$b["num"].")";'), $rechnung["bezahlt"])))); ?>


<?php } } } ?>

\end{labels}
\end{document}
