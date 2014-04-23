\documentclass[12pt,a4paper]{article}
\usepackage{geometry}
\geometry{a4paper,left=1.5cm,right=1.5cm,top=2cm,bottom=2cm}
\usepackage{tabularx}
\usepackage{textcomp}
\usepackage{longtable}
\newcolumntype{L}[1]{>{\raggedright\arraybackslash}p{#1}} % linksbündig mit Breitenangabe
\newcolumntype{C}[1]{>{\centering\arraybackslash}p{#1}} % zentriert mit Breitenangabe
\newcolumntype{R}[1]{>{\raggedleft\arraybackslash}p{#1}} % rechtsbündig mit Breitenangabe
\usepackage{graphicx}
\usepackage[utf8]{inputenc}
\usepackage[pdfpagelabels]{hyperref}
\usepackage{lastpage}
\usepackage{fancyhdr}
\pagestyle{fancy}
\begin{document}

\fontencoding{T1}
\fontfamily{pag}\selectfont
\large

\renewcommand{\thesection}{}
\renewcommand{\thesubsection}{}
\renewcommand{\thesubsubsection}{}

\setcounter{page}{1}
\lfoot{Stand: <?php print(date("d.m.Y")) ?>}
\cfoot{}
\rfoot{\thepage{} / \pageref{LastPage}}

\footnotesize
\fontfamily{pcr}\selectfont

<?php include(dirname(__FILE__) . "/inline_kontoblatt.tex.php") ?>

\end{document}
