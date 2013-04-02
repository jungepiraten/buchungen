<?php
$title = "Einstellungen";
include("header.php");
?>
<h2>Passwort ändern</h2>

<form action="" method="post" class="form-horizontal">
	<fieldset>
		<div class="control-group">
			<label for="changePwOld" class="control-label">Altes Passwort</label>
			<div class="controls">
				<input type="password" name="changePwOld" required />
			</div>
		</div>
		<div class="control-group">
			<label for="changePwNew" class="control-label">Neues Passwort</label>
			<div class="controls">
				<input type="password" name="changePwNew" required />
			</div>
		</div>
		<div class="control-group">
			<label for="changePwConfirm" class="control-label">bestätigen</label>
			<div class="controls">
				<input type="password" name="changePwConfirm" required />
			</div>
		</div>
		<div class="form-actions">
			<button type="submit" class="btn btn-primary" name="changePw" value="1">Passwort ändern</button>
		</div>
	</fieldset>
</form>
<?php
if ($auth["grant"]) {
?>
<h2>Rechteverwaltung</h2>

<div class="btn-toolbar">
	<a class="btn btn-success addUser"><i class="icon-plus icon-white"></i> Neu</a>
</div>
<table class="table table-striped">
	<thead>
		<tr>
			<th>Benutzer</th>
			<th>Konten</th>
			<th>Rechte</th>
		</tr>
	</thead>
	<tbody>
<?php
foreach ($users as $user) {
	$perms = array();
	if ($user["grant"])
		$perms[] = "Grant";
	if ($user["database"])
		$perms[] = "Datenbank";
	if ($user["belege"])
		$perms[] = "Belege";
	if ($user["verifyTransaction"])
		$perms[] = "Verifizieren";
?>
		<tr class="userRow" data-username="<?php print(htmlentities($user["username"])) ?>" data-accountprefixes="<?php print(implode(",", $user["accountPrefixes"])) ?>" data-grant="<?php print($user["grant"]) ?>" data-database="<?php print($user["database"]) ?>" data-belege="<?php print($user["belege"]) ?>" data-verifyTransaction="<?php print($user["verifyTransaction"]) ?>">
			<td><?php print(htmlentities($user["username"])) ?></td>
			<td><?php if (count($user["accountPrefixes"]) == 0) { ?>(keine)<?php } else { print(implode("*, ", $user["accountPrefixes"])."*"); } ?></td>
			<td><?php print(implode(", ", $perms)) ?></td>
		</tr>
<?php } ?>
	</tbody>
</table>
<form action="" method="post" class="form-horizontal">
	<fieldset>
		<div class="modal hide userModal">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Benutzer</h3>
			</div>
			<div class="modal-body">
				<input type="hidden" name="username" value="" />
				<div class="control-group">
					<label for="username" class="control-label">Benutzername</label>
					<div class="controls">
						<input type="text" name="username" value="" />
					</div>
				</div>
				<div class="control-group">
					<label for="username" class="control-label">Passwort</label>
					<div class="controls">
						<input type="password" name="password" value="" />
					</div>
				</div>
				<div class="control-group">
					<label for="accountPrefixes" class="control-label">Konten</label>
					<div class="controls">
						<input type="text" name="accountPrefixes" value="" />
					</div>
				</div>
				<div class="control-group">
					<label for="grant" class="control-label">Grant</label>
					<div class="controls">
						<input type="checkbox" name="grant" value="1" />
					</div>
				</div>
				<div class="control-group">
					<label for="database" class="control-label">Datenbank</label>
					<div class="controls">
						<input type="checkbox" name="database" value="1" />
					</div>
				</div>
				<div class="control-group">
					<label for="belege" class="control-label">Belege</label>
					<div class="controls">
						<input type="checkbox" name="belege" value="1" />
					</div>
				</div>
				<div class="verifyTransaction-group">
					<label for="verifyTransaction" class="control-label">Verifizieren</label>
					<div class="controls">
						<input type="checkbox" name="verifyTransaction" value="1" />
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary saveButton" value="1">Speichern</button>
				<button type="submit" class="btn btn-danger" name="removeUser" value="1">Löschen</button>
			</div>
		</fieldset>
	</form>
</div>
<script type="text/javascript">

$(".addUser").click(function () {
	$(".userModal").find("input[name=deleteUser]").hide();
	$(".userModal").find("input[name=username][type=hidden]").prop("disabled",true);
	$(".userModal").find("input[name=username][type=text]").prop("disabled",false);
	$(".userModal").find("input[name=username]").val("");
	$(".userModal").find("input[name=password]").val("");
	$(".userModal").find("input[name=accountPrefixes]").val("");
	$(".userModal").find("input[name=grant]").prop("checked",false);
	$(".userModal").find("input[name=database]").prop("checked",false);
	$(".userModal").find("input[name=belege]").prop("checked",false);
	$(".userModal").find("input[name=verifyTransaction]").prop("checked",false);
	$(".userModal").find(".saveButton").attr("name","createUser");
	$(".userModal").modal("show");
});

$(".userRow").click(function () {
	$(".userModal").find("input[name=deleteUser]").show();
	$(".userModal").find("input[name=username][type=hidden]").prop("disabled",false);
	$(".userModal").find("input[name=username][type=text]").prop("disabled",true);
	$(".userModal").find("input[name=username]").val($(this).data("username"));
	$(".userModal").find("input[name=password]").val("");
	$(".userModal").find("input[name=accountPrefixes]").val($(this).data("accountprefixes"));
	$(".userModal").find("input[name=grant]").prop("checked",$(this).data("grant")==1);
	$(".userModal").find("input[name=database]").prop("checked",$(this).data("database")==1);
	$(".userModal").find("input[name=belege]").prop("checked",$(this).data("belege")==1);
	$(".userModal").find("input[name=verifyTransaction]").prop("checked",$(this).data("verifyTransaction")==1);
	$(".userModal").find(".saveButton").attr("name","modifyUser");
	$(".userModal").modal("show");
});

</script>
<?php
}

include("footer.php");
?>
