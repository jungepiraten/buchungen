<?php

require_once("sql.inc.php");
require_once("pdf.inc.php");
loginRequire();


sort($beschluesse);

/*
sendPDF("kassenbuch.pdf", "vorlagen/kassenbuch.tex.php", array(
));
*/
