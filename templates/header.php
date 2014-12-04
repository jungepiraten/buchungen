<?php
require_once("login.inc.php");
header("Content-Type: text/html; charset=UTF-8");
?>
<!DOCTYPE html>
<html dir="ltr">
        <head>
                <meta http-equiv="content-type" content="text/xhtml; charset=UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link href="https://ucp.junge-piraten.de/static/bootstrap-3.0.0/css/bootstrap.min.css" rel="stylesheet" />
                <link href="https://static.junge-piraten.de/bootstrap-jupis-2.css" rel="stylesheet" />
		<script src="https://ucp.junge-piraten.de/static/jquery-2.0.3.min.js"></script>
		<script src="https://ucp.junge-piraten.de/static/bootstrap-3.0.0/js/bootstrap.min.js"></script>
<!--
                <link href="https://static.junge-piraten.de/bootstrap-2.1.1/css/bootstrap.css" rel="stylesheet" />
                <link href="https://static.junge-piraten.de/bootstrap-2.1.1/css/bootstrap-responsive.css" rel="stylesheet" />
                <script src="https://static.junge-piraten.de/bootstrap-2.1.1/js/bootstrap.min.js"></script>
                <script src="https://static.junge-piraten.de/jquery-1.8.2.min.js"></script>
-->
                <link href="res/treeview.css" rel="stylesheet" />
		<script src="/typeahead.jquery.js"></script>
		<script src="https://haushalt.junge-piraten.de/d3.v3.min.js"></script>
                <link rel="icon" type="image/png" href="https://static.junge-piraten.de/favicon.png" />

                <meta name="viewport" content="width=device-width, initial-scale=1.0" />

                <!--[if lt IE 9]>
                        <script src="https://static.junge-piraten.de/ie-html5.js"></script>
                <![endif]-->

<style type="text/css">
.strike-trough {text-decoration:line-through;}
.tt-hint {display:none;}
.tt-dropdown-menu {background:white; padding:0.5em 2em;}
.tt-cursor {background:lightgray;}
</style>

                <title><?php print($title) ?> &bull; Junge Piraten Kassenbuch</title>
        </head>
        <body>
		<div class="visible-desktop spacer-top">&nbsp;</div>

		<div class="navbar navbar-fixed-top navbar-default">
			<div class="container-fluid">
				<div class="navbar-header">
					<a class="navbar-brand" href="index.php">
						Kassenbuch
					</a>
					<ul class="nav navbar-nav">
						<li><a href="accounts.php">Konten</a></li>
						<li><a href="transactions.php">Transaktionen</a></li>
<?php if ($auth != null && $auth["database"]) { ?>
						<li><a href="lock.php">Datenbank</a></li>
<?php } ?>
<?php if ($auth != null && $auth["buchen"]) { ?>
						<li><a href="buchen.php">Buchen</a></li>
<?php } ?>
<?php if ($auth != null && $auth["kostenstellen"]) { ?>
						<li><a href="kostenstellen.php">Kostenstellen</a></li>
<?php } ?>
<?php if ($auth != null && $auth["kreditoren"]) { ?>
						<li><a href="kreditoren.php">Kreditoren</a></li>
<?php } ?>
					</ul>
				</div>
			</div>
		</div>

		<div class="container">
			<h1><?php print($title) ?></h1>
