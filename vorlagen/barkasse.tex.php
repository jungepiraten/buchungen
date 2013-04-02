\documentclass[12pt,a4paper]{article}
\usepackage{fullpage}
\usepackage{tabularx}
\usepackage{graphicx}
\usepackage[utf8]{inputenc}
\pagestyle{empty}
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

{\fontfamily{pag}\selectfont\LARGE Barkassenabrechnung}

\vspace*{1cm}

\begin{tabularx}{\textwidth}{X|X}
 \hline
 \hline\textbf{Beleg-Nr.} & <?php print(fetchLaTeXInput("beleg")) ?> \\
 \hline\textbf{Zeitraum} & <?php print(fetchLaTeXInput("ZEITRAUM_START")) ?> bis <?php print(fetchLaTeXInput("ZEITRAUM_ENDE")) ?> \\
 \hline\textbf{Buchungskonto} & <?php print(fetchLaTeXInput("BUCHUNGSKONTO")) ?> \\
 \hline\textbf{Summe Soll} & <?php print(fetchLaTeXInput("BETRAG-SOLL")) ?> EUR \\
 \hline\textbf{Summe Haben} & <?php print(fetchLaTeXInput("BETRAG-HABEN")) ?> EUR \\
 \hline\textbf{Soll-Konten} & <?php print(fetchLaTeXInput("KONTEN-SOLL")) ?> \\
 \hline\textbf{Haben-Konten} & <?php print(fetchLaTeXInput("KONTEN-HABEN")) ?> \\
 \hline\textbf{Anlagen} & <?php print(fetchLaTeXInput("ANLAGEN")) ?> \\
 \hline
 \hline\multicolumn{2}{l}{\textbf{Anmerkungen}} \\
 \hline\multicolumn{2}{l}{\parbox{\textwidth}{<?php print(fetchLaTeXInput("ANMERKUNGEN")) ?>}} \\
 \hline
 \hline
\end{tabularx}

\end{document}
