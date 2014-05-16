<?php
$title = "Buchen";
include("header.php");
?>
<p class="alert alert-success buchen-success"><strong>Gespeichert</strong> Die Buchung wurde gespeichert</p>
<p class="alert alert-danger buchen-error"></p>

<form action="" method="post" class="form-horizontal buchen" role="form">
	<ul class="nav nav-tabs" id="txTemplateTabs">
	</ul>
	&nbsp;
	<div id="buchungPanel"></div>
	<fieldset>&nbsp;
		<div class="form-actions">
			<button type="submit" name="buchen" class="btn btn-primary" value="1">Buchen</button>
		</div>
	</fieldset>
</form>
<?php foreach (glob("buchen_*.js") as $jsfile) { ?><script type="text/javascript" src="<?php print($jsfile) ?>"></script><?php } ?>
<script type="text/javascript">
<!--

function TemplateBuchen() {
	this._panel = $("<p>").text("Hallo Welt");

	this.getPanel = function() {
		return this._panel;
	}
	this.clean = function() {
	}
	this.evaluate = function() {
		var txid = get128bitRandom();
		return {
			"errors" : [],
			"buchung": {"guid": txid, "beleg": "-", "postdate": "2014-05-16", "description": "Empty", "splits": [], "buchen": true}
		}
	}
}

var currentPanel = null;

function selectPanel(buchen) {
	currentPanel = buchen;
	$("#buchungPanel").empty().append(currentPanel.getPanel());
	cleanForm();
}

function addTxTemplate(name, buchen) {
	$("#txTemplateTabs").append($("<li>")
		.append($("<a>")
			.click(function () {
				selectPanel(buchen);
				$(this).parents("ul").children().removeClass("active");
				$(this).parents("li").addClass("active");
			})
			.attr("href","#")
			.text(name) ));
}

addTxTemplate("Dialogbuchen", new DialogBuchen("F", {
	"kosten":	{"kontoprefix":"R", "konto":"", "ausloeser":["2","3","4","6","8"], "label":"Kostenrechnung"},
	"debitoren":	{"kontoprefix":"D", "konto":"", "ausloeser":["0650", "0655"], "label":"Debitoren"},
	"kreditoren":	{"kontoprefix":"K", "konto":"", "ausloeser":["1340"], "label":"Kreditoren"},
}));
addTxTemplate("Test", new TemplateBuchen());

$("#txTemplateTabs").children(":first").find("a").click();

function cleanForm() {
	$(".buchen-success").hide();
	$(".buchen-error").hide();
	if (currentPanel != null) {
		currentPanel.clean();
	}
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
	$(".buchen-error").hide();

	var retval = currentPanel.evaluate();
	var errors = retval.errors;
	var buchung = retval.buchung;

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
		$.post("buchen.php", buchung, function(data) {
			$(self).find("input").prop("disabled",false);
			if (data["status"] == "ok") {
				cleanForm();
				$(".buchen-success").show().delay(3000).slideUp();
			} else {
				$(".buchen-error").text(data["message"]).show();
			}
		}, "json");
	}
});

$(function () {
	cleanForm();
});

//-->
</script>
<?php
include("footer.php");
?>
