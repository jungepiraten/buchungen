<?php
$title = "Login";
include("header.php");
?>
<form action="" method="post" class="form-horizontal">
	<fieldset>
		<div class="control-group">
			<label class="control-label" for="loginUser">Username</label>
			<div class="controls">
				<input type="text" name="loginUser" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="loginPass">Passwort</label>
			<div class="controls">
				<input type="password" name="loginPass" />
			</div>
		</div>
		<div class="form-actions">
			<button type="submit" name="login" class="btn btn-primary" value="1">Anmelden</button>
		</div>
	</fieldset>
</form>
<?php
include("footer.php");
?>
