<?php
$title = "Buchen";
include("header.php");
?>
<p class="alert alert-success buchen-success"><strong>Gespeichert</strong> Die Buchung wurde gespeichert</p>
<p class="alert alert-warning buchen-warning"></p>
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
	this.load = function () {
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

var panels = {};
var currentPanel = null;

function selectPanel(id) {
	currentPanel = panels[id];

	$("#txTemplateTabs").children().removeClass("active");
	$("#txTemplateTabs").find(".txtemplate-" + id).addClass("active");

	$("#buchungPanel").empty().append(currentPanel.getPanel());
	cleanForm();
}

function addTxTemplate(id, name, buchen) {
	$("#txTemplateTabs").append($("<li>").addClass("txtemplate-" + id)
		.append($("<a>")
			.click(function () {
				selectPanel(id);
			})
			.attr("href","#" + btoa(id + "#"))
			.text(name) ));
	panels[id] = buchen;
}

var lots = [];
$.get("/lots.json.php", {year: "<?php print($year); ?>"}, function (data) {lots = data; }, "json");
function createLotTypeAheadCallback(prefix, fieldname) {
	return function(q) {
		var partner = prefix + $("input[name=" + fieldname + "]").val().split(" ")[0];
		if (lots[partner] === undefined) {
			return [];
		}
		return lots[partner]["lots"].filter(function (lot) {return lot.toLowerCase().indexOf(q.toLowerCase()) >= 0; });
	};
}

addTxTemplate("dialog", "Dialogbuchen", new DialogBuchen("F", {
	"kosten":	{"kontoprefix":"R", "konto":"", "ausloeser":["2","3","4","6","8"], "label":"Kostenrechnung"},
	"debitoren":	{"kontoprefix":"D", "konto":"", "ausloeser":["0650", "0655"], "label":"Debitoren"},
	"kreditoren":	{"kontoprefix":"K", "konto":"", "ausloeser":["0555", "0630", "1340", "1360", "1390"], "label":"Kreditoren"},
}));

var konten = {
	"0951":"Geschäftskonto",
	"0921":"Barkasse Poststelle",
	"0922":"Barkasse Schatzmeister*in",
	"0931":"Kasse Veranstaltung 1",
	"0932":"Kasse Veranstaltung 2",
	"0933":"Kasse Veranstaltung 3",
	"0981":"Kreditkarte 6134",
	"0982":"Kreditkarte 6142",
};

var _options={};
var vorlagen=[{
	"label": "Automatisch inklusive VPanel-verbuchung",
	"evaluate": function (txid, beleg, postdate, value) {
		var ident = "";
		$.ajax({
			type: "GET",
			url: "/vpanel-mitgliedident.php",
			data: {"mitglied": $("input[name=mitglied]").val(), "year": $("input[name=jahr]").val(), "value": value/100, "timestamp": postdate},
			dataType: "json",
			async: false,
			success: function (data) {
				ident = data.land + "-" + data.type;
				if (!data.gebucht) {
					$(".buchen-warning").text("Automatisches Verbuchen in VPanel nicht erfolgreich, bitte von Hand nachtragen!").show().delay(15000).slideUp();
				}
			},
		});

		if (_options[ident] === undefined) {
			$(".buchen-error").text("Automatische zuordnung fehlgeschlagen, bitte von Hand auswählen!").show().delay(15000).slideUp();
			return null;
		}
		var anteile = _options[ident].slice(0);
		anteile.push({"konto":"F" + (value > 25600 ? "2120" : "2110"),"anteil":1});
		anteile.push({"konto":"F" + $("select[name=konto]").val(),"anteil":-1});
		return {"anteile": anteile, "vorgang": "Mitgliedsbeitrag "+$("input[name=jahr]").val()+" "+ident+"#"+$("input[name=mitglied]").val()};
	},
}];
var _bls = ["BB", "BE", "BW", "BY", "HB", "HE", "HH", "MV", "NI", "NW", "RP", "SH", "SL", "SN", "ST", "TH"]
Object.keys(_bls).forEach(function (bl, i) {
	var tts = {"O":1,"F":2};
	for (var t in tts) {
		var tt = tts[t];
		var lvKst = "R0101"+("0"+(tt+2*(i+1))).slice(-2);
		var anteile = [{"konto":"R","anteil":-1}, {"konto":"R01010"+tt, "anteil":0.7}, {"konto":lvKst, "anteil":0.3}];
		vorlagen.push({
			"label": _bls[bl]+"-"+t,
			"_anteile": anteile,
			"anteile": function(txid, beleg, postdate, value) {
				var anteile = this._anteile.slice(0);
				anteile.push({"konto":"F" + (value > 25600 ? "2120" : "2110"),"anteil":1});
				anteile.push({"konto":"F" + $("select[name=konto]").val(),"anteil":-1});
				return anteile;
			},
			"vorgang": function() {
				return "Mitgliedsbeitrag "+$("input[name=jahr]").val()+" "+this.label+"#"+$("input[name=mitglied]").val();
			}
		});
		_options[bl + "-" + t] = anteile;
	}
});
addTxTemplate("mb", "Mitgliedsbeiträge", new VorlageBuchung(vorlagen, [
	getInputField({"name":"konto","size":2,"label":"Konto","type":"select","data":konten}),
	getInputField({"name":"jahr","size":2, "label":"Jahr (bei Monatsbeitrag YYYY/MM)", "value":new Date().getFullYear()}),
	getInputField({"name":"mitglied","size":2, "label":"Mitgliedsnummer"}),
]));

function _spende(konto1, konto2, label) {
	return {
		"label": label,
		"anteile": function () {return [
			{"konto": $("input[name=bescheinigung]").prop("checked") ? konto1 : konto2, "anteil": 1},
			{"konto": "F" + $("select[name=konto]").val(),"anteil":-1},
			{"konto": "R", "anteil": -1},
			{"konto": "R" + $("input[name=kostenstelle]").val().split(" ")[0], "anteil": 1},
		]; },
		"vorgang": function () {return "Spende ["+$("input[name=spender]").val()+"]"; },
	};
}
addTxTemplate("spende", "Spende", new VorlageBuchung([
		_spende("F3221", "F3223", "Geldspende"),
		_spende("F3225", "F3227", "Sachspende"),
		_spende("F3230", "F3232", "Aufwandspende"),
	], [
		getInputField({"name":"spender","size":6,"label":"Spender*in"}),
		getInputField({"name":"bescheinigung","size":1,"type":"checkbox","label":"Bescheinigung"}),
		getInputField({"name":"konto","size":2,"label":"Konto","type":"select","data":konten}),
		getInputField({"name":"kostenstelle","size":2,"type":"konto","prefix":"R","label":"Kostenstelle"}),
	]
));

addTxTemplate("kontofuehrung", "Kontoführungsgebühr", new VorlageBuchung(
	[
		{
			"label": "Geschäftskonto",
			"anteile": function () {
				return [
					{"konto": "F0951", "anteil":1},
					{"konto": "F2900", "anteil":-1},
					{"konto": "R", "anteil":1},
					{"konto": "R013101", "anteil":-1},
				];
			},
			"vorgang": function () {return "Kontoführungsgebühr"; },
		}
	], []
));

function _reisekosten(konto, label) {
	return {
		"label": label,
		"anteile": function () {
			return [
				{"konto": "F" + konto, "anteil": -1},
				{"konto": "F1340", "anteil": 1},
				{"konto": "K"+$("select[name=kreditor]").val(), "anteil": 1},
				{"konto": "K", "anteil": -1},
				{"konto": "R"+$("input[name=kostenstelle]").val().split(" ")[0], "anteil": -1},
				{"konto": "R", "anteil": 1},
			];
		},
		"vorgang": function () {return "Reisekosten [" + $("input[name=person]").val() + " "+$("input[name=aktion]").val()+"]"+($("input[name=iban]").val() != "" ? " {+INHABER:"+$("input[name=person]").val()+" +IBAN:"+$("input[name=iban]").val()+" +BIC:"+$("input[name=bic]").val()+"}" : ""); },
	};
}
var _kreditoren = {};
for (var i = 1; i <= 26; i++) {
	_kreditoren[30000 + i] = "Erstattungen " + String.fromCharCode(64+i);
}
addTxTemplate("reisekosten", "Reisekostenerstattung", new VorlageBuchung([
		_reisekosten("2560", "Ideeller Bereich"),
		_reisekosten("6810", "Zweckbetrieb USt.-Frei"),
		_reisekosten("8330", "Sonstige Geschäftsbetriebe 1"),
	], [
		getInputField({"name":"kreditor","size":2,"label":"Kreditor","type":"select","data":_kreditoren}),
		getInputField({"name":"kostenstelle","size":2,"label":"Kostenstellennummer","type":"konto","prefix":"R"}),
		getInputField({"name":"person","size":5,"label":"Person"}),
		getInputField({"name":"aktion","size":5,"label":"Aktion"}),
		getInputField({"name":"iban","label":"IBAN","type":"iban"}),
		getInputField({"name":"bic","label":"BIC","type":"bic"}),
	]
));

function _re_bezahlt(prefix, label, konto, f) {
	return {
		"label": label,
		"anteile" : function () {
			return [
				{"konto":"F"+konto, "anteil":-1*f},
				{"konto":"F"+$("select[name=konto]").val(), "anteil":1*f},
				{"konto":"K", "anteil":1*f},
				{"konto":"K"+$("input[name=kreditor]").val().split(" ")[0], "anteil":-1*f},
			];
		},
		"vorgang": function () {
			var lot = "";
			if (prefix == "Zahlung" && lots["K"+$("input[name=kreditor]").val().split(" ")[0]] !== undefined) {
				lot = " " + lots["K"+$("input[name=kreditor]").val().split(" ")[0]]["label"];
			}
			return prefix + lot + " [" + $("input[name=nummer]").val() + "]";
		}
	};
}
addTxTemplate("re_bezahlt", "Rechnung bezahlt", new VorlageBuchung([
		_re_bezahlt("Zahlung", "Rechnung bereits erhalten", "1340", 1),
		_re_bezahlt("Zahlung", "Rechnung folgt (Anzahlung)", "0630", 1),
		_re_bezahlt("Gutschrift", "Gutschrift", "0650", -1),
		_re_bezahlt("Kaution", "Kaution zurückerhalten", "0555", -1),
		_re_bezahlt("Reisekosten", "Reisekosten", "1340", 1),
		_re_bezahlt("Erstattung", "Erstattung", "1340", 1),
	], [
		getInputField({"name":"konto","size":2,"label":"Konto","type":"select","data":konten}),
		getInputField({"name":"kreditor","size":2,"label":"Kreditorennummer","type":"konto","prefix":"K"}),
		getInputField({"name":"nummer","size":2,"label":"Rechnungsnummer","type":"typeahead","callback":createLotTypeAheadCallback("K", "kreditor")}),
	]
));

function _re_erhalten(label, konto, f) {
	return {
		"label": label,
		"vorgang": function() {
			var lot = "";
			if (lots["K"+$("input[name=kreditor]").val().split(" ")[0]] !== undefined) {
				lot = lots["K"+$("input[name=kreditor]").val().split(" ")[0]]["label"];
			}
			return "RE " + lot + " [" + $("input[name=nummer]").val() + "]" + ($("input[name=iban]").val() != "" ? " {+INHABER:"+$("input[name=kreditor]").val().substring($("input[name=kreditor]").val().indexOf(" ") + 1)+" +IBAN:"+$("input[name=iban]").val().replace(" ","")+" +BIC:"+$("input[name=bic]").val()+"}" : "");
		},
		"anteile": function () {
			return [
				{"konto":"F"+konto, "anteil":1*f},
				{"konto":"F"+$("input[name=sachkonto]").val().split(" ")[0], "anteil":-1*f},
				{"konto":"K", "anteil":-1*f},
				{"konto":"K"+$("input[name=kreditor]").val().split(" ")[0], "anteil":1*f},
				{"konto":"R", "anteil":1*f},
				{"konto":"R"+$("input[name=kostenstelle]").val().split(" ")[0], "anteil":-1*f},
			];
		}
	};
}
addTxTemplate("re_erhalten", "Rechnung erhalten", new VorlageBuchung([
		_re_erhalten("Bezahlung folgt", "1340", 1),
		_re_erhalten("Gutschriftsrechnung", "0650", -1),
		_re_erhalten("Bereits per Anzahlung bezahlt", "0630", 1),
	], [
		getInputField({"name":"kreditor","size":2,"label":"Kreditorennummer","type":"konto","prefix":"K"}),
		getInputField({"name":"nummer","size":2,"label":"Rechnungsnummer","type":"typeahead","callback":createLotTypeAheadCallback("K", "kreditor")}),
		getInputField({"name":"sachkonto","size":2,"label":"Sachkonto","type":"konto","prefix":"F"}),
		getInputField({"name":"kostenstelle","size":2,"label":"Kostenstellennummer","type":"konto","prefix":"R"}),
		getInputField({"name":"iban","type":"iban","label":"IBAN"}),
		getInputField({"name":"bic","type":"bic","label":"BIC"}),
	]
));

function _dre_bezahlt(prefix, label, konto, f) {
	return {
		"label": label,
		"anteile" : function () {
			return [
				{"konto":"F"+konto, "anteil":1*f},
				{"konto":"F"+$("select[name=konto]").val(), "anteil":-1*f},
				{"konto":"D", "anteil":-1*f},
				{"konto":"D"+$("input[name=debitor]").val().split(" ")[0], "anteil":1*f},
			];
		},
		"vorgang": function () {
			var lot = "";
			if (lots["D"+$("input[name=debitor]").val().split(" ")[0]] !== undefined) {
				lot = " " + lots["D"+$("input[name=debitor]").val().split(" ")[0]]["label"];
			}
			return prefix + lot + " [" + $("input[name=nummer]").val() + "]";
		}
	};
}
addTxTemplate("dre_bezahlt", "Debitorenrechnung bezahlt", new VorlageBuchung([
		_dre_bezahlt("Zahlung", "Zahlung", "0650", 1),
		_dre_bezahlt("Gutschrift", "Gutschrift", "0650", 1),
	], [
		getInputField({"name":"konto","size":2,"label":"Konto","type":"select","data":konten}),
		getInputField({"name":"debitor","size":2,"label":"Debitorennummer","type":"konto","prefix":"D"}),
		getInputField({"name":"nummer","size":2,"label":"Rechnungsnummer","type":"typeahead","callback":createLotTypeAheadCallback("D", "debitor")}),
	]
));

function _re_ausgestellt(label, konto, f) {
	return {
		"label": label,
		"vorgang": function() {
			var lot = "";
			if (lots["D"+$("input[name=debitor]").val().split(" ")[0]] !== undefined) {
				lot = lots["D"+$("input[name=debitor]").val().split(" ")[0]]["label"];
			}
			return "RA " + lot + " [" + $("input[name=nummer]").val() + "]" + ($("input[name=iban]").val() != "" ? " {+INHABER:"+$("input[name=debitor]").val().substring($("input[name=kreditor]").val().indexOf(" ") + 1)+" +IBAN:"+$("input[name=iban]").val().replace(" ","")+" +BIC:"+$("input[name=bic]").val()+" +MANDAT:"+$("input[name=mandatsreferenz]").val()+"}" : "");
		},
		"anteile": function () {
			return [
				{"konto":"F"+konto, "anteil":-1*f},
				{"konto":"F"+$("input[name=sachkonto]").val().split(" ")[0], "anteil":1*f},
				{"konto":"D", "anteil":1*f},
				{"konto":"D"+$("input[name=debitor]").val().split(" ")[0], "anteil":-1*f},
				{"konto":"R", "anteil":-1*f},
				{"konto":"R"+$("input[name=kostenstelle]").val().split(" ")[0], "anteil":1*f},
			];
		}
	};
}
addTxTemplate("re_ausgestellt", "Rechnung ausgestellt", new VorlageBuchung([
		_re_ausgestellt("Bezahlung folgt", "0650", 1),
		_re_ausgestellt("Gutschriftsrechnung", "0650", -1),
	], [
		getInputField({"name":"debitor","size":2,"label":"Debitorennummer","type":"konto","prefix":"D"}),
		getInputField({"name":"nummer","size":2,"label":"Rechnungsnummer","type":"typeahead","callback":createLotTypeAheadCallback("D", "debitor")}),
		getInputField({"name":"sachkonto","size":2,"label":"Sachkonto","type":"konto","prefix":"F"}),
		getInputField({"name":"kostenstelle","size":2,"label":"Kostenstellennummer","type":"konto","prefix":"R"}),
		getInputField({"name":"iban","type":"iban","label":"IBAN"}),
		getInputField({"name":"bic","type":"bic","label":"BIC"}),
		getInputField({"name":"mandatsreferenz","size":5,"label":"Mandatsreferenz"}),
	]
));

addTxTemplate("transit", "Geldtransit", new VorlageBuchung([
		{
			"label": "Geldtransit",
			"anteile": function () {return [{"konto":"F"+$("select[name=konto1]").val(), "anteil": 1}, {"konto":"F0705","anteil": -1}]; },
			"vorgang": function (txid, beleg, postdate, value) {
				var kto_soll = value > 0 ? $("select[name=konto1]").val() : $("select[name=konto2]").val();
				var kto_haben = value < 0 ? $("select[name=konto1]").val() : $("select[name=konto2]").val();
				console.log("Geldtransit " + konten[kto_soll] + " an " + konten[kto_haben]);
				return "Geldtransit [" + konten[kto_soll] + " an " + konten[kto_haben] + "]";
			},
		}
	], [
		getInputField({"name":"konto1","size":2,"label":"Konto 1 (Aktion)","type":"select","data":konten}),
		getInputField({"name":"konto2","size":2,"label":"Konto 2 (Gegen)","type":"select","data":konten}),
	]
));

function _durchlauf(konto, label) {
	return {
		"label": label,
		"anteile": function (txid, beleg, postdate, value) {
			return [
				{"konto": "F"+konto, "anteil":-1},
				{"konto": "F"+$("select[name=konto]").val(), "anteil":1},
			];
		},
		"vorgang": function () {
			return "Durchlaufender Posten: " + $("input[name=beschreibung]").val();
		}
	};
}
addTxTemplate("durchlauf", "Durchlaufende Posten", new VorlageBuchung([
		_durchlauf("0870", "Einnahmen (z.B. überbezahlte Mitgliedsbeiträge)"),
		_durchlauf("0875", "Ausgaben (z.B. Unberechtigte Lastschriften)"),
	], [
		getInputField({"name":"konto","size":2,"label":"Finanzkonto","type":"select","data":konten}),
		getInputField({"name":"beschreibung","size":6,"label":"Beschreibung"}),
	]
));

$(function() {
	cleanForm();
	if (location.hash.substring(1).length > 5) {
		var data = atob(location.hash.substring(1));
		var parameters = atob(location.hash.substring(1)).split("#",2);
		selectPanel(data.substring(0, data.indexOf("#")));
		if (data.length > data.indexOf("#") + 1) {
			currentPanel.load(JSON.parse(data.substring(data.indexOf("#") + 1)));
		}
	} else {
		$("#txTemplateTabs").children(":first").find("a").click();
	}
});

function cleanForm() {
	$(".buchen-success").hide();
	$(".buchen-warning").hide();
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

function getInputField(s) {
	var name = s["name"];
	var size = "size" in s ? s["size"] : 10;
	var type = "type" in s ? s["type"] : "text";
	var label = s["label"];
	var value = "value" in s ? s["value"] : "";

	var input;
	switch(type) {
	case "currency":
		input = $("<div>").addClass("input-group")
			.append($('<input class="form-control">').attr("type","text").attr("name",name).css("text-align","right").val(value).data("init-value",value))
			.append($("<span>").addClass("input-group-addon").text("€"));
		break;
	case "typeahead":
		input = getTypeAheadInput(s["callback"]).attr("name",name);
		break;
	case "konto":
		input = getKontoInput(s["prefix"], s["maxlength"]).attr("name",name);
		break;
	case "custom":
		input = s["input"];
		break;
	case "select":
		input = $('<select class="form-control">').attr("name",name).data("init-value",value);
		for (i in s["data"]) {
			input.append($("<option>").attr("value",i).prop("selected",i == value).text(s["data"][i]));
		}
		break;
	case "iban":
		input = $('<input class="form-control">').attr("type","text").attr("name",name).val(value).data("init-value",value).blur(function() {checkIBAN(this);});
		size = 5;
		break;
	case "bic":
		input = $('<input class="form-control">').attr("type","text").attr("name",name).val(value).data("init-value",value);
		size = 2;
		break;
	default:
		input = $('<input class="form-control">').attr("type",type).attr("name",name).val(value).data("init-value",value);
		break;
	}

	return $('<div class="form-group">')
		.append($('<label class="col-sm-2 control-label">').attr("for",name).text(label))
		.append($('<div>').addClass("col-sm-"+size)
			.append(input) );
}

// Modulo 97 for huge numbers given as digit strings.
// JS converts huge numbers into floating points, so modulo-arthmetics will fail.
function mod97(digit_string) {
	var m = 0;
	for (var i = 0; i < digit_string.length; ++i)
		m = (m * 10 + parseInt(digit_string.charAt(i))) % 97;
	return m;
}

function iban2ibancheck(check) {
	check = check.substring(4) + check.substring(0,4);
	check = check.replace("A","10");
	check = check.replace("B","11");
	check = check.replace("C","12");
	check = check.replace("D","13");
	check = check.replace("E","14");
	check = check.replace("F","15");
	check = check.replace("G","16");
	check = check.replace("H","17");
	check = check.replace("I","18");
	check = check.replace("J","19");
	check = check.replace("K","20");
	check = check.replace("L","21");
	check = check.replace("M","22");
	check = check.replace("N","23");
	check = check.replace("O","24");
	check = check.replace("P","25");
	check = check.replace("Q","26");
	check = check.replace("R","27");
	check = check.replace("S","28");
	check = check.replace("T","29");
	check = check.replace("U","30");
	check = check.replace("V","31");
	check = check.replace("W","32");
	check = check.replace("X","33");
	check = check.replace("Y","34");
	check = check.replace("Z","35");
	return check;
}

function checkIBAN(field) {
	$($(field).parents(".form-group")[0]).removeClass("has-error").find(".help-inline").remove();
	field.value = field.value.replace(/\s/g, "").toUpperCase();
	if (field.value != "") {
		if (mod97(iban2ibancheck(field.value)) != 1) {
			$($(field).parents(".form-group")[0]).addClass("has-error");
		}
	}
}

function formatCurrency(value) {
	if (value == 0)
		return "";
	return (value/100).toFixed(2);
}

function showConfirm(buchung, callback) {
	var modal = $("<div>").addClass("modal").append(
		$("<div>").addClass("modal-dialog").append(
			$("<div>").addClass("modal-content").append([
				$("<div>").addClass("modal-header").append(
					$("<h4>").addClass("modal-title").text(buchung["beleg"] + ": " + buchung["description"] + " (" + buchung["postdate"] + ")")
					),
				$("<div>").addClass("modal-body").append(
					$("<table>").addClass("table table-striped").append([
						$("<thead>")
							.append($("<tr>")
									.append($("<th>").text("Konto"))
									.append($("<th>").text("Soll"))
									.append($("<th>").text("Haben"))
								),
						$("<tbody>")
							.append(buchung["splits"].map(function (split) {
								var label = "";
								var konto = kontenview_konten.filter(function(k) {return k.code === split["konto"];});
								if (konto.length == 1) label = konto.pop().label;
								return $("<tr>")
									.append($("<td>").text(split["konto"] + " - " + label))
									.append($("<td>").text(split["value"] < 0 ? formatCurrency(split["value"] * (-1)) : ""))
									.append($("<td>").text(split["value"] > 0 ? formatCurrency(split["value"]) : ""));
								})),
						])),
				$("<div>").addClass("modal-footer").append([
					$("<button>").addClass("btn btn-default").text("Zurück").click(function () {
						modal.modal("hide");
						}),
					$("<button>").addClass("btn btn-primary").text("Speichern").click(function () {
						callback();
						modal.modal("hide");
						}),
					])
				])
			)
		);
	$("body").append(modal);
	modal.modal();
}

$("form").submit(function (event) {
	event.preventDefault();
	$(".buchen-error").hide();

	var errors = [];
	var buchung = currentPanel.evaluate(function (error) {
		errors.push(error);
	});
	buchung["year"] = "<?php print($year) ?>";

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
		showConfirm(buchung, function () {
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
		});
	}
});

//-->
</script>
<?php
include("footer.php");
?>
