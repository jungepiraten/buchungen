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

{\fontfamily{pag}\selectfont\LARGE Vermerk}

\vspace*{1cm}

\begin{tabularx}{\textwidth}{X|X}
 \hline
 \hline\textbf{Beleg-Nr.} & <?php print(fetchLaTeXInput("beleg")) ?> \\
 \hline
 \hline\multicolumn{2}{l}{\textbf{Anmerkungen}} \\
 \hline\multicolumn{2}{l}{\parbox{\textwidth}{<?php print(fetchLaTeXInput("ANMERKUNGEN")) ?>}} \\
 \hline
 \hline
\end{tabularx}

\end{document}
