<?php

function latexSpecialChars( $string )
{
    $map = array( 
            "\\"=>"\\textbackslash{}",
            "\r\n"=>"\n",
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

