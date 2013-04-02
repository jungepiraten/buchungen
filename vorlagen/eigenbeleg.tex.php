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

{\fontfamily{pag}\selectfont\LARGE Eigenbeleg}

\vspace*{1cm}

\begin{tabularx}{\textwidth}{X|X}
 \hline
 \hline\textbf{Beleg-Nr.} & <?php print(fetchLaTeXInput("beleg")) ?> \\
 \hline\textbf{Buchungsdatum} & <?php print(fetchLaTeXInput("DATUM")) ?> \\
 \hline\textbf{Soll-Konten} & <?php print(fetchLaTeXInput("KONTEN-SOLL")) ?> \\
 \hline\textbf{Haben-Konten} & <?php print(fetchLaTeXInput("KONTEN-HABEN")) ?> \\
 \hline\textbf{Betrag} & <?php print(fetchLaTeXInput("BETRAG")) ?> EUR \\
 \hline\textbf{Beschluss} & <?php print(fetchLaTeXInput("BESCHLUSS")) ?> \\
 \hline\textbf{Vorgang} & <?php print(fetchLaTeXInput("VORGANG")) ?> \\
 \hline\textbf{Zahlungsempfänger} & <?php print(fetchLaTeXInput("EMPFAENGER")) ?> \\
 \hline\textbf{Begründung} & <?php print(fetchLaTeXInput("BEGRUENDUNG")) ?> \\
 \hline\textbf{Anlagen} & <?php print(fetchLaTeXInput("ANLAGEN")) ?> \\
 \hline
 \hline\multicolumn{2}{l}{\textbf{Anmerkungen}} \\
 \hline\multicolumn{2}{l}{\parbox{\textwidth}{<?php print(fetchLaTeXInput("ANMERKUNGEN")) ?>}} \\
 \hline
 \hline
\end{tabularx}

\vfill
\hfill
\hfill
\hrulefill

\hfill
\hfill
Unterschrift

\end{document}
