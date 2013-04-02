<?php

require_once("api.inc.php");
require_once("login.inc.php");

if (loginMatchPassword($_REQUEST["loginUser"], $_REQUEST["loginPass"])) {
	loginInitSession($_REQUEST["loginUser"]);
} else {
	out(403, array("error" => "AUTH_FAILED"));
}
