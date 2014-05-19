<?php
$title = "Kostenstellen";
include("header.php");
?>
<p class="alert alert-success kostenstellen-success"><strong>Gespeichert</strong> Die Kostenstelle wurde gespeichert</p>
<p class="alert alert-danger kostenstellen-error"></p>

<form action="" method="post" class="form-horizontal buchen" role="form">
	<fieldset>&nbsp;
		<div class="form-actions">
			<button type="submit" name="add" class="btn btn-primary" value="1">Hinzufügen</button>
		</div>
	</fieldset>
</form>
<script type="text/javascript">
<!--

function loadData(data) {
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
	$(".kostenstellen-success").hide();
	$(".kostenstellen-error").hide();
}

function get32bitRandom() {
	return Math.floor((1+Math.random()) * 0x100000000).toString(16).substring(1);
}
function get128bitRandom() {
	return get32bitRandom()+get32bitRandom()+get32bitRandom()+get32bitRandom();
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
	var kostenstelle = {};

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
				$(".kostenstellen-success").show().delay(3000).slideUp();
			} else {
				$(".kostenstellen-error").text(data["message"]).show();
			}
		}, "json");
	}
});

//-->
</script>
<?php
include("footer.php");
?>
