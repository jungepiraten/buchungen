<?php

function latexSpecialChars( $string )
{
    $map = array( 
            "\\"=>"\\textbackslash{}",
            "\r\n"=>"\n",
            "\""=>"\grqq ",
            "#"=>"\\#",
            "$"=>"\\$",
            "%"=>"\\%",
            "&"=>"\\&",
            "~"=>"\\~{}",
            "_"=>"\\_",
            "^"=>"\\^{}",
            "{"=>"\\{",
            "}"=>"\\}",
	    "\n"=>"\\newline\n",
	);
	return str_replace(array_keys($map), array_values($map), $string);
}

function latexFormatCurrency($cur) {
	return sprintf("%.2f \\texteuro", $cur/100);
}

function getBelegUrl($year, $num) {
	return "http://vpanel.intern.junge-piraten.de/documents.php?dokumentsuche=BGS_F" . $year . "_" . $num;
}
