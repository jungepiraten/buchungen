<?php
$title = "Buchen";
include("header.php");
?>
<p class="alert alert-success buchen-success"><strong>Gespeichert</strong> Die Buchung wurde gespeichert</p>
<p class="alert alert-danger buchen-error"></p>

<form action="" method="post" class="form-horizontal buchen" role="form">
	<input type="hidden" name="txid" />
	<fieldset>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="beleg">Beleg</label>
			<div class="col-sm-2">
				<input type="text" class="form-control" name="beleg" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="postdate">Datum</label>
			<div class="col-sm-2">
				<input type="date" class="form-control" name="postdate" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="vorgang">Vorgang</label>
			<div class="col-sm-10">
				<input type="text" class="form-control" name="vorgang" />
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Splits</legend>
		<div class="row">
			<div class="col-xs-8"></div>
			<div class="col-xs-2"><strong>Soll</strong></div>
			<div class="col-xs-2"><strong>Haben</strong></div>
		</div>
		<div data-kontoprefix="F" class="splits fibu"></div>
		<div data-kontoprefix="R" class="splits kosten">&nbsp;</div>
		<div data-kontoprefix="D" class="splits debitoren">&nbsp;</div>
		<div data-kontoprefix="K" class="splits kreditoren">&nbsp;</div>
	</fieldset>
	<fieldset>&nbsp;
		<div class="form-actions">
			<button type="submit" name="buchen" class="btn btn-primary" value="1">Buchen</button>
		</div>
	</fieldset>
</form>
<script type="text/javascript">
<!--

function cleanForm() {
	$(".buchen-success").hide();
	$(".buchen-error").hide();
	$("form.buchen").find("input").prop("disabled",false).val("");
	$("form.buchen").find(".splits .row").remove();
	checkBalance("fibu");

	$("input[name=beleg]").focus();
}

var initValue;
var settings = {
	"kosten":	{"konto":"", "label":"Kostenrechnung"},
	"debitoren":	{"konto":"", "label":"Debitoren"},
	"kreditoren":	{"konto":"", "label":"Kreditoren"},
};

function getKontoKategorie(kontoCode) {
	if ($.inArray(kontoCode, ["1340"]) >= 0)
		return "kreditoren";
	if ($.inArray(kontoCode, ["0650", "0655"]) >= 0)
		return "debitoren";
	if ($.inArray(kontoCode.substring(0,1), ["2","3","4","6","8"]) >= 0)
		return "kosten";
	return null;
}

function checkBalance(bal) {
	var remaining = 0;
	var remainingRow = null;
	if (bal == "fibu") {
		initValue = {"kosten":0, "debitoren":0, "kreditoren":0}
	} else {
		if (initValue[bal] == 0) {
			$("."+bal+" .row").remove();
			$("."+bal).hide();
			return;
		}
		if ($("."+bal).find(".row").length == 0) {
			addBalanceLine(bal, initValue[bal]);
			$("."+bal).find(".row:first").find("input").prop("disabled",true);
			$("."+bal).find(".row:first").find(".konto")
				.val(settings[bal]["konto"])
				.hide()
				.after($("<strong>").text(settings[bal]["label"]));
		}
		$("."+bal).find(".row:first").data("new",0);
		$("."+bal).find(".row:first").find(".soll").val(initValue[bal] < 0 ? formatCurrency((-1)*initValue[bal]) : "");
		$("."+bal).find(".row:first").find(".haben").val(initValue[bal] > 0 ? formatCurrency(initValue[bal]) : "");
		$("."+bal).show();
	}
	$("."+bal+" .row").each(function (i, elem) {
		if ($(elem).data("new") == "1") {
			remainingRow = $(elem);
		} else {
			if (bal == "fibu") {
				var kategorie = getKontoKategorie($(elem).find(".konto").val());
				if (kategorie != null) {
					initValue[kategorie] -= ($(elem).find(".haben").val() - $(elem).find(".soll").val()) * 100;
				}
			}
			remaining -= ($(elem).find(".haben").val() - $(elem).find(".soll").val()) * 100;
		}
	});

	if (bal == "fibu") {
		for (i in initValue) {
			checkBalance(i);
		}
	}

	if (remainingRow != null) {
		remainingRow.find(".soll").val(remaining < 0 ? formatCurrency((-1)*remaining) : "");
		remainingRow.find(".haben").val(remaining > 0 ? formatCurrency(remaining) : "");
	} else {
		addBalanceLine(bal, remaining);
	}
}

function get32bitRandom() {
	return Math.floor((1+Math.random()) * 0x100000000).toString(16).substring(1);
}
function get128bitRandom() {
	return get32bitRandom()+get32bitRandom()+get32bitRandom()+get32bitRandom();
}

function addBalanceLine(bal, remaining) {
	function _update() {
		var row = $(this).parents(".row");
		if (row.data("new") == "1") {
			row.data("new", "0");
		}
		checkBalance(bal);
	}

	var splitId = get32bitRandom();
	$("." + bal).append($("<div>").addClass("row").data("splitId", splitId).data("new","1")
		.append($("<div>").addClass("col-xs-8")
			.append($("<input>").on("input",_update).addClass("konto").attr("name",bal+"["+splitId+"][konto]").addClass("form-control")) )
		.append($("<div>").addClass("col-xs-2")
			.append($("<div>").addClass("input-group")
				.append($("<input>").change(formatCurrencyField).on("input",_update).addClass("soll").attr("name",bal+"["+splitId+"][soll]").css("text-align","right").addClass("form-control").val(remaining < 0 ? formatCurrency((-1)*remaining) : ""))
				.append($("<span>").addClass("input-group-addon").text("€")) ))
		.append($("<div>").addClass("col-xs-2")
			.append($("<div>").addClass("input-group")
				.append($("<input>").change(formatCurrencyField).on("input",_update).addClass("haben").attr("name",bal+"["+splitId+"][haben]").css("text-align","right").addClass("form-control").val(remaining > 0 ? formatCurrency(remaining) : ""))
				.append($("<span>").addClass("input-group-addon").text("€")) ))
	);
}

function formatCurrencyField() {
	if ($(this).parents(".row").find(".soll").is(":focus") || $(this).parents(".row").find(".haben").is(":focus")) {
		var value = $(this).parents(".row").find(".haben") - $(this).parents(".row").find(".soll");
		$(this).parents(".row").find(".soll").val(value < 0 ? formatCurrency((-1)*value) : "")
		$(this).parents(".row").find(".haben").val(value > 0 ? formatCurrency(value) : "");
	} else {
		$(this).val(formatCurrency($(this).val()*100));
	}
}

function formatCurrency(value) {
	if (value == 0)
		return "";
	return (value/100).toFixed(2);
}

$("form").submit(function (event) {
	event.preventDefault();
	$(".buchen-error").hide();
	var errors = [];

	var txid = get128bitRandom();
	var beleg = $(this).find("input[name=beleg]").val();
	var postdate = $(this).find("input[name=postdate]").val();
	var vorgang = $(this).find("input[name=vorgang]").val();
	var splits = [];

	if (beleg == "") {
		errors.push({"field":"beleg", "description":"Kein Beleg angegeben"});
	}
	if (postdate == "") {
		errors.push({"field":"postdate", "description":"Kein Buchungsdatum angegeben"});
	}
	if (vorgang == "") {
		errors.push({"field":"vorgang", "description":"Keinen Vorgang definiert"});
	}

	$(this).find("input[name=txid]").val(txid);
	$(this).find(".splits .row").each(function (i,row) {
		var kontoprefix = $(row).parents(".splits").data("kontoprefix");
		var konto = $(row).find(".konto").val();
		var soll = $(row).find(".soll").val() * 100;
		var haben = $(row).find(".haben").val() * 100;

		var value = soll-haben;
		if (value == 0 && konto != "") {
			errors.push({"field":$(row).find(".konto").attr("name"), "description":"Nullbuchung"});
		} else if (value != 0 && konto == "" && ! $(row).find(".konto").is(":hidden")) {
			errors.push({"field":$(row).find(".konto").attr("name"), "description":"Buchung ohne Konto"});
		} else if (value != 0) {
			splits.push({"konto": kontoprefix + konto, "value": value});
		}
	});

	if (splits.length == 0) {
		errors.push({"field":$(".splits .row:first").find(".konto").attr("name"), "description":"Leere Buchung"});
	}

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
		$.post("buchen.php", {"guid": txid, "beleg": beleg, "postdate": postdate, "description": vorgang, "splits": splits, "buchen": true}, function(data) {
			console.log(data);
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
