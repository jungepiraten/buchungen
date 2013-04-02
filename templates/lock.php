<?php
$title = "Datenbank";
include("header.php");
?>
<p>
	Aktueller Status: <?php if ($locked) { ?><span class="label label-warning">Gesperrt seit <?php echo date("d.m. H:i", $lockedTimestamp); ?> durch <?php echo $lockedBy; ?></span><?php } else { ?><span class="label label-success">Frei</span><?php } ?><br />
	<?php if ($locked) { ?><a href="?unlock=true&amp;year=<?php echo $year; ?>" class="btn btn-danger btn-mini">Entsperren</a> (Vorsicht: <strong>Immer</strong> mit dem momentanen Benutzer absprechen!)<?php } else { ?><a href="?lock=true&amp;year=<?php echo $year; ?>" class="btn btn-danger btn-mini">Sperren</a><?php } ?>
</p>
<?php if ($locked && $isAuth) { ?>
	<h2>Zugangsdaten</h2>
	<pre>
Server: verwaltung.junge-piraten.de
Datenbank: <?php echo $database . "\n"; ?>
User: <?php echo $lockedBy . "\n"; ?>
Passwort: <?php echo $lockedPassword . "\n"; ?>
</pre>
<?php } ?>
<?php
include("footer.php");
?>
