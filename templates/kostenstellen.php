<?php
$title = "Kostenstellen";
include("header.php");
?>
<p class="alert alert-success kostenstellen-success"><strong>Gespeichert</strong> Die Kostenstelle wurde als <span class="code"></span> gespeichert</p>
<p class="alert alert-danger kostenstellen-error"></p>

<form action="" method="post" class="form-horizontal kostenstellen" role="form">
	<fieldset>
		<div class="form-group">
			<label for="name" class="col-sm-2 control-label">Name</label>
			<div class="col-sm-10">
				<input type="text" name="name" class="form-control" placeholder="Name" />
			</div>
		</div>
		<div class="form-group">
			<label for="ticket" class="col-sm-2 control-label">Ticket</label>
			<div class="col-sm-10">
				<input type="text" name="ticket" class="form-control" placeholder="Ticket" />
			</div>
		</div>
		<div class="form-group">
			<label for="responsible" class="col-sm-2 control-label">Verantwortliche*r</label>
			<div class="col-sm-1">
				<input type="text" name="vid" class="form-control" placeholder="Mitgliedsnummer" />
			</div>
			<div class="col-sm-3">
				<input type="text" name="vname" class="form-control" placeholder="Name" />
			</div>
			<div class="col-sm-6">
				<input type="text" name="vmail" class="form-control" placeholder="Mailadresse" />
			</div>
		</div>
		<div class="form-group">
			<label for="" class="col-sm-2 control-label">Bereich</label>
			<div class="col-sm-5 gliederung-input">
				<input type="text" name="gliederung" class="form-control" placeholder="01 Bundesverband" />
			</div>
			<div class="col-sm-5 budget-input">
				<input type="text" name="budget" class="form-control" placeholder="11 Mitgliederversammlungen" />
			</div>
		</div>
		<div class="form-group">
			<label for="legitimation" class="col-sm-2 control-label">Legitimation</label>
			<div class="col-sm-10">
				<input type="text" name="legitimation" class="form-control" placeholder="Link zur Legitimation" />
			</div>
		</div>
		<div class="form-group">
			<label for="betrag" class="col-sm-2 control-label">Betrag</label>
			<div class="col-sm-2">
				<div class="input-group">
					<input type="text" name="betrag" class="form-control" placeholder="0.00" style="text-align:right;" />
					<span class="input-group-addon">€</span>
				</div>
			</div>
		</div>
		<div class="form-actions">
			<button type="submit" name="add" class="btn btn-primary" value="1">Hinzufügen</button>
		</div>
	</fieldset>
</form>
<script type="text/javascript">
<!--

function loadData(data) {
	for (i in data) {
		$("input[name=" + i + "]").val(data[i]);
	}
}

function updateBudgetInput() {
	$(".budget-input").empty().append(getKontoInput("R" + getInput("gliederung").substring(0,2), 2).attr("name","budget"));
}

$(function() {
	$(".gliederung-input").empty().append(getKontoInput("R", 2).attr("name","gliederung").keyup(updateBudgetInput));
	updateBudgetInput();
	cleanForm();
	if (location.hash.substring(1).length > 5) {
		var data = atob(location.hash.substring(1));
		if (data.length > 0) {
			loadData(JSON.parse(data));
		}
	}
});

function cleanForm() {
	$(".kostenstellen-success").hide();
	$(".kostenstellen-error").hide();
	$("form.kostenstellen").find("input").val("");
}

function formatCurrency(value) {
	if (value == 0)
		return "";
	return (value/100).toFixed(2);
}

$("form").submit(function (event) {
	event.preventDefault();
	$(".kostenstellen-error").hide();

	var errors = [];
	var kostenstelle = getInputs(["name","ticket","vname","vid","vmail","legitimation","betrag"]);
	kostenstelle["parents"] = [];
	kostenstelle["parents"].push({"code":"R" + getInput("gliederung").substring(0,2), "name": getInput("gliederung").substring(2)});
	kostenstelle["parents"].push({"code":"R" + getInput("gliederung").substring(0,2) + getInput("budget").substring(0,2), "name": getInput("budget").substring(2)});
	kostenstelle["add"] = 1;

	if (errors.length > 0) {
		errors.forEach(function (error) {
			$("input[name='" + error.field + "']")
				.attr("title",error.description)
				.tooltip("show")
				.keyup(function () {$(this).tooltip("destroy").parent().removeClass("has-error");})
				.parent().addClass("has-error");
		});
		$("input[name='" + errors[0].field + "']").focus();
	} else {
		var self = this;
		$(self).find("input").prop("disabled",true);
		$.post("kostenstellen.php", kostenstelle, function(data) {
			$(self).find("input").prop("disabled",false);
			if (data["status"] == "ok") {
				cleanForm();
				$(".kostenstellen-success").find(".code").text(data["code"]);
				$(".kostenstellen-success").show().delay(3000).slideUp();
			} else {
				$(".kostenstellen-error").text(data["message"]).show();
			}
		}, "json");
	}
});

function getInputs(inputs) {
	var a = {};
	for (i in inputs) {
		a[inputs[i]] = getInput(inputs[i]);
	}
	return a;
}

function getInput(i) {
	return $("input[name="+i+"]").val();
}

//-->
</script>
<?php
include("footer.php");
?>
