\documentclass[a4paper]{article}
\usepackage{textcomp}
\usepackage[T1]{fontenc}
\usepackage[utf8]{inputenc}
\usepackage[newdimens]{labels}
\LabelCols=3
\LabelRows=7
% \LabelGridtrue
\LeftLabelBorder=5mm
\TopLabelBorder=10mm
\RightLabelBorder=5mm
\BottomLabelBorder=10mm
\LeftPageMargin=7mm
\TopPageMargin=16mm
\RightPageMargin=7mm
\BottomPageMargin=16mm
\usepackage{rotating}
\usepackage{multirow}
\usepackage{array}
\begin{document}
\fontfamily{pag}\selectfont
\begin{labels}

<?php
$offset = isset($_REQUEST["offset"]) ? intval($_REQUEST["offset"]) : 0;
for ($i=0;$i<$offset;$i++) {
	print("\\ \n\n\n");
}

$lastenterdate = isset($_REQUEST["last"]) ? intval($_REQUEST["last"]) : null;
$firstenterdate = isset($_REQUEST["first"]) ? intval($_REQUEST["first"]) : null;
$showUnpaid = isset($_REQUEST["showunpaid"]);
$nums = isset($_REQUEST["nums"]) ? $_REQUEST["nums"] : null;

$belegkreise = array();
foreach ($journal as $tx) {
	$kreis = trim(preg_replace('/\d+$/','',$tx["num"]),"_");
	if ($kreis != "" && ($nums === null || in_array($tx["num"], $nums))) {
		if (!isset($belegkreise[$kreis])) {
			$belegkreise[$kreis] = array(
				"belege" => array(),
				"firstEnterDate" => $tx["enter_date"],
			);
		}

		$belegkreise[$kreis]["belege"][] = $tx["num"];
		$belegkreise[$kreis]["firstEnterDate"] = min($belegkreise[$kreis]["firstEnterDate"], $tx["enter_date"]);
	}
}

$belege = array();
foreach ($partners as $partner => $info) { foreach ($info["lots"] as $lot => $transactions) {
	$rechnung = $bezahlt = $sachkonten = $kostenstellen = array();
	$lastEnterDate = null;
	$value = 0;
	foreach ($transactions as $data) {
		$lastEnterDate = max($lastEnterDate, $data["tx"]["enter_date"]);
		$value += $data["split"]["value"];
		if (in_array(substr($data["tx"]["num"],0,2), array("RE","ER","RS"))) {
			$rechnung[] = array("num" => $data["tx"]["num"], "date" => $data["tx"]["date"], "value" => $data["split"]["value"]);
		} else {
			$bezahlt[] = array("num" => $data["tx"]["num"], "date" => $data["tx"]["date"], "value" => $data["split"]["value"]);
		}
		$sachkonten = array_merge($sachkonten, array_map(create_function('$s', 'return substr($s["account_code"],1);'), array_filter($data["tx"]["splits"], create_function('$s', 'return in_array(substr($s["account_code"],0,2), array("F2","F3","F4","F5","F6","F7","F8"));'))));
		$kostenstellen = array_merge($kostenstellen, array_map(create_function('$s', 'return substr($s["account_code"],1);'), array_filter($data["tx"]["splits"], create_function('$s', 'return strlen($s["account_code"]) > 1 && in_array(substr($s["account_code"],0,1), array("R"));'))));
	}
	$sachkonten = array_unique($sachkonten);
	$kostenstellen = array_unique($kostenstellen);
	foreach ($rechnung as $re) {
		if ($nums === null && ($value == 0 || $showUnpaid) || $nums !== null && in_array($re["num"], $nums)) {
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
	}
} }

if (empty($belege) && empty($belegkreise)) {
	echo "Keine Belege vorhanden";
}

ksort($belegkreise);

foreach ($belegkreise as $kreis => $info) {
	if (($lastenterdate === null || $lastenterdate <= $info["firstEnterDate"]) && ($firstenterdate === null || $firstenterdate >= $info["firstEnterDate"])) {
?>
\vfill
\begin{tabular}{b{.5cm}b{4.5cm}}
\multirow{2}{*}{\raisebox{2em}{\begin{turn}{-90}\huge \textbf{<?php print(latexSpecialChars($kreis)) ?>}\end{turn}}} & \vfill \textbf{<?php print(latexSpecialChars($kreis)) ?>} \\
& <?php print(latexSpecialChars(getBelegkreisDescription($kreis))) ?>

\end{tabular}
\vspace{.5cm}


<?php
} }

ksort($belege);

foreach ($belege as $beleg => $rechnungen) {
	foreach ($rechnungen as $rechnung) {
		if (($lastenterdate === null || $lastenterdate <= $rechnung["lastEnterDate"]) && ($firstenterdate === null || $firstenterdate >= $rechnung["lastEnterDate"])) {
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
