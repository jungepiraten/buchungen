% \documentclass[10pt,landscape]{article}
\documentclass[10pt]{article}
% \usepackage[a4paper,left=1cm,right=1cm,top=2cm,bottom=2cm]{geometry}
\usepackage[a4paper,left=2cm,right=1cm,top=1cm,bottom=2cm]{geometry}
\usepackage{textcomp}
\usepackage{tabularx}
\newcolumntype{L}[1]{>{\raggedright\arraybackslash}p{#1}} % linksbündig mit Breitenangabe
\newcolumntype{C}[1]{>{\centering\arraybackslash}p{#1}} % zentriert mit Breitenangabe
\newcolumntype{R}[1]{>{\raggedleft\arraybackslash}p{#1}} % rechtsbündig mit Breitenangabe
\usepackage{longtable}
\usepackage{graphicx}
\usepackage[utf8]{inputenc}
\usepackage{lastpage}
\usepackage{fancyhdr}
\pagestyle{fancy}
\renewcommand{\headrulewidth}{0pt}
\renewcommand{\footrulewidth}{0pt}

\footnotesize
\fontfamily{pcr}\selectfont
\setcounter{page}{1}
\lfoot{Lastschriftbericht <?php print(date("d.m.Y", $date)) ?>}
\cfoot{}
\rfoot{\thepage{} / \pageref{LastPage}}

\begin{document}

\fontencoding{T1}

\fontfamily{pcr}\selectfont
\begin{longtable}{L{4.6cm}|L{2.8cm}|L{2.7cm}|L{4cm}|R{2cm}}
 \hline
 \hline \textbf{Konto, BIC} & \textbf{Inhaber} & \textbf{Mandat} & \textbf{Verwendungszweck} & \textbf{Betrag} \\
 \hline
 \endhead
 \hline
 \hline
 \endfoot
<?php foreach ($charges as $buchung) { ?>
 \hline <?php print(latexSpecialChars($buchung["konto"]["iban"])) ?>\newline <?php print($buchung["konto"]["bic"]) ?> & <?php print($buchung["konto"]["inhaber"]) ?> & <?php print($buchung["mandat"]["id"]) ?> & <?php print($buchung["verwendung"]) ?> & <?php print(latexFormatCurrency($buchung["betrag"])) ?> \\
<?php } ?>
 \hline
 \hline\multicolumn{4}{l|}{\textbf{Summe <?php print($count) ?> Posten}} & \textbf{<?php print(latexFormatCurrency($sum_betrag)) ?>} \\
 \hline
\end{longtable}

\end{document}
