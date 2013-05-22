\documentclass[12pt,a4paper]{article}
\usepackage{geometry}
\geometry{a4paper,left=2cm,right=1cm,top=2cm,bottom=2cm}
\usepackage{tabularx}
\usepackage{longtable}
\usepackage{graphicx}
\usepackage[utf8]{inputenc}
\usepackage[pdfpagelabels]{hyperref}
\usepackage{lastpage}
\usepackage{fancyhdr}
\pagestyle{fancy}
\begin{document}

\centering
\fontencoding{T1}
\fontfamily{pag}\selectfont
\large

\renewcommand{\thesection}{}
\renewcommand{\thesubsection}{}

\setcounter{page}{1}
\lfoot{Stand: <?php print(date("d.m.Y")) ?>}
\cfoot{}
\rfoot{\thepage{} / \pageref{LastPage}}

\footnotesize
\fontfamily{pcr}\selectfont

<?php include(dirname(__FILE__) . "/inline_kontoblatt.tex.php") ?>

\end{document}
