#!/usr/bin/php
<?php

$data = json_decode($_SERVER["argv"][1]);
$field = $_SERVER["argv"][2];

print($data->$field);

?>
