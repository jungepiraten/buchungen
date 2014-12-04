<?php
$title = "SEPA-Lastschriften";
include("header.php");

?>
<form action="" method="post" class="form-horizontal" accept-charset="UTF-8" enctype="multipart/form-data">
	<fieldset>
		<div class="control-group">
			<label for="PAIN" class="control-label">SEPA-PAIN-Datei:</label>
			<div class="controls">
				<input type="file" name="PAIN" />
			</div>
		</div>
		
		<div class="form-actions">
			<button type="submit" class="btn">Herunterladen</button>
		</div>
	</fieldset>
</form>
<?php
include("footer.php");
?>
