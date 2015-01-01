<?php
$title = "Kreditoren";
include("header.php");
?>
<p class="alert alert-success kreditoren-success"><strong>Gespeichert</strong> Der*die Kreditor*in wurde als <span class="code"></span> gespeichert</p>
<p class="alert alert-danger kreditoren-error"></p>

<form action="" method="post" class="form-horizontal kreditoren" role="form">
	<fieldset>
		<div class="form-group">
			<label for="offset" class="col-sm-2 control-label">Kategorie</label>
			<div class="col-sm-4">
				<select name="offset" class="form-control">
					<option value="10001">Firmen</option>
					<option value="30001">Erstattungen</option>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label for="name" class="col-sm-2 control-label">Name</label>
			<div class="col-sm-10">
				<input type="text" name="name" class="form-control" placeholder="Name" />
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

$(function() {
	cleanForm();
	if (location.hash.substring(1).length > 5) {
		var data = atob(location.hash.substring(1));
		if (data.length > 0) {
			loadData(JSON.parse(data));
		}
	}
});

function cleanForm() {
	$(".kreditoren-success").hide();
	$(".kreditoren-error").hide();
	$("form.kreditoren").find("input").val("");
}

function formatCurrency(value) {
	if (value == 0)
		return "";
	return (value/100).toFixed(2);
}

$("form").submit(function (event) {
	event.preventDefault();
	$(".kreditoren-error").hide();

	var errors = [];
	var kreditor = getInputs(["offset","name"]);
	kreditor["add"] = 1;

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
		$.post("kreditoren.php", kreditor, function(data) {
			$(self).find("input").prop("disabled",false);
			if (data["status"] == "ok") {
				cleanForm();
				$(".kreditoren-success").find(".code").text(data["code"]);
				$(".kreditoren-success").show().delay(3000).slideUp();
			} else {
				$(".kreditoren-error").text(data["message"]).show();
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
	return $("input[name="+i+"],select[name="+i+"]").val();
}

//-->
</script>
<?php
include("footer.php");
?>
