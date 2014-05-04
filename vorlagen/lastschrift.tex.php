\documentclass[10pt]{article}
\usepackage[a4paper,left=2cm,right=1cm,top=1cm,bottom=2cm]{geometry}
\usepackage{tabularx}
\usepackage{longtable}
\usepackage{graphicx}
\usepackage[utf8]{inputenc}
\usepackage{lastpage}
\usepackage{fancyhdr}
\pagestyle{fancy}

\footnotesize
\fontfamily{pcr}\selectfont
\setcounter{page}{1}
\lfoot{Lastschriftbericht}
\cfoot{}
\rfoot{\thepage{} / \pageref{LastPage}}

\begin{document}

\fontencoding{T1}

\fontfamily{pcr}\selectfont
\begin{longtable}{p{2cm}|p{2.5cm}|p{3cm}|p{2.4cm}|p{5cm}}
 \hline
 \hline \textbf{Bank} & \textbf{Konto} & \textbf{Inhaber} & \textbf{Betrag} & \textbf{Verwendungszweck} \\
 \hline
 \endhead
 \hline
 \hline
 \endfoot
<?php foreach (fetchLaTeXDTA("DTAUS","BUCHUNGEN") as $buchung) { ?>
 \hline <?php print($buchung["BLZ"]) ?> & <?php print($buchung["KONTO"]) ?> & <?php print($buchung["INHABER"]) ?> & <?php print(str_replace(" ", "\\ ", sprintf("%7.2f", $buchung["BETRAG"]))) ?> EUR & <?php print($buchung["VERWENDUNG"]) ?> \\
<?php } ?>
 \hline
 \hline\multicolumn{3}{l|}{} & \textbf{<?php print(str_replace(" ", "\\ ", sprintf("%7.2f", fetchLaTeXDTA("DTAUS","BETRAG")))) ?> EUR} & \textbf{Summe <?php print(fetchLaTeXDTA("DTAUS","COUNT")) ?> Posten} \\
 \hline
\end{longtable}

\end{document}
