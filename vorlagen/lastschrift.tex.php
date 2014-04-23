\documentclass[10pt]{article}
\usepackage[a4paper,left=2cm,right=1cm,top=1cm,bottom=2cm]{geometry}
\usepackage{tabularx}
\usepackage{longtable}
\usepackage{graphicx}
\usepackage[utf8]{inputenc}
\usepackage{lastpage}
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

{\fontfamily{pag}\selectfont\LARGE Lastschriftbericht}

\vspace*{1cm}

\begin{tabularx}{\textwidth}{X|X}
 \hline
 \hline\textbf{Beleg-Nr.} & <?php print(fetchLaTeXInput("beleg")) ?> \\
 \hline\textbf{Buchungsdatum} & <?php print(fetchLaTeXInput("DATUM")) ?> \\
 \hline\textbf{Soll-Konten} & <?php print(fetchLaTeXInput("KONTEN-SOLL")) ?> \\
 \hline\textbf{Haben-Konten} & <?php print(fetchLaTeXInput("KONTEN-HABEN")) ?> \\
 \hline\textbf{Anzahl Posten} & <?php print(fetchLaTeXDTA("DTAUS","COUNT")) ?> \\
 \hline\textbf{Gesamtbetrag} & <?php print(fetchLaTeXDTA("DTAUS","BETRAG")) ?> EUR \\
 \hline\textbf{Beschluss} & <?php print(fetchLaTeXInput("BESCHLUSS")) ?> \\
 \hline\textbf{Anlagen} & 1 \\
 \hline
 \hline\multicolumn{2}{l}{\textbf{Anmerkungen}} \\
 \hline\multicolumn{2}{l}{\parbox{\textwidth}{<?php print(fetchLaTeXInput("ANMERKUNGEN")) ?>}} \\
 \hline
 \hline
\end{tabularx}

\clearpage

\footnotesize
\fontfamily{pcr}\selectfont
\setcounter{page}{1}
\cfoot{}
\rfoot{\thepage{} / \pageref{LastPage}}

\begin{longtable}{p{2cm}|p{2.5cm}|p{3cm}|p{2.4cm}|p{4cm}}
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
