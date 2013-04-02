<?php

function bash_encode($out, $prefix = "FINANZEN") {
	if (is_array($out)) {
		$o = "";
		foreach($out as $k => $v) {
			$o .= bash_encode($v, $prefix . "_" . $k);
		}
		return $o;
	}
	return $prefix . "=" . $out . "\n";
}

function out($code, $out) {
	header("Status: " . $code);
	switch (isset($_REQUEST["format"]) ? stripslashes($_REQUEST["format"]) : "") {
	case "bash":
		die(bash_encode($out));
	case "php":
		die(serialize($out));
	default:
	case "json":
		die(json_encode($out));
	}
}
