<?php
header("Content-Type: text/html; charset=UTF-8");
?>
<!DOCTYPE html>
<html dir="ltr">
        <head>
                <meta http-equiv="content-type" content="text/xhtml; charset=UTF-8" />
                <link href="https://static.junge-piraten.de/bootstrap-2.1.1/css/bootstrap.css" rel="stylesheet" />
                <link href="https://static.junge-piraten.de/bootstrap-2.1.1/css/bootstrap-responsive.css" rel="stylesheet" />
                <link href="https://static.junge-piraten.de/bootstrap-jupis-2.css" rel="stylesheet" />
                <link href="res/treeview.css" rel="stylesheet" />
		<script src="https://haushalt.junge-piraten.de/d3.v3.min.js"></script>
                <script src="https://static.junge-piraten.de/jquery-1.8.2.min.js"></script>
                <script src="https://static.junge-piraten.de/bootstrap-2.1.1/js/bootstrap.min.js"></script>
                <link rel="icon" type="image/png" href="https://static.junge-piraten.de/favicon.png" />

                <meta name="viewport" content="width=device-width, initial-scale=1.0" />

                <!--[if lt IE 9]>
                        <script src="https://static.junge-piraten.de/ie-html5.js"></script>
                <![endif]-->

<style type="text/css">
.strike-trough {text-decoration:line-through;}
</style>

                <title><?php print($title) ?> &bull; Junge Piraten Kassenbuch</title>
        </head>
        <body>
		<div class="visible-desktop spacer-top">&nbsp;</div>

		<div class="navbar navbar-fixed-top navbar-inverse">
			<div class="navbar-inner">
				<div class="container-fluid">
					<a class="brand" href="index.php">
						Kassenbuch
					</a>
					<ul class="nav">
						<li><a href="accounts.php">Konten</a></li>
						<li><a href="transactions.php">Transaktionen</a></li>
<?php if ($auth != null && $auth["belege"]) { ?>
						<li><a href="belege.php">Belege</a></li>
<?php } ?>
<?php if ($auth != null && $auth["database"]) { ?>
						<li><a href="lock.php">Datenbank</a></li>
<?php } ?>
					</ul>
				</div>
			</div>
		</div>

		<div class="container">
			<h1><?php print($title) ?></h1>
