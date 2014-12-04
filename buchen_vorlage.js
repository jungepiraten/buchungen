function VorlageBuchung(vorlagen, extra_fields) {
	this._vorlagen = vorlagen;

	this.getPanel = function () {
		return this._panel;
	}
	this.load = function(data) {
		this._panel.find("input[name=beleg]").val(data["num"]);
		this._panel.find("input[name=postdate]").val(data["postdate"]);
		this._panel.find("input[name=value]").val(formatCurrency(data["value"]));
		for (field in extra_fields) {
			extra_fields[field].find("*[name]").each(function (index, element) {
				if (data[$(element).attr("name")] !== undefined) {
					$(element).val(data[$(element).attr("name")]);
				}
			});
		}
		this._panel.find("select[name=vorlage]").focus();
	}
	this.clean = function() {
		this._panel.find("input").val(function () {return $(this).data("init-value");});
		this._panel.find("input[name=beleg]").focus();
		this.kv.updateView();
	}
	this.evaluate = function(errorHandler) {
		var txid = get128bitRandom();
		var beleg = this._panel.find("input[name=beleg]").val();
		var postdate = this._panel.find("input[name=postdate]").val();
		var value = this._panel.find("input[name=value]").val() * 100;
		var vorlage = this._vorlagen[this._panel.find("select[name=vorlage]").val()];

		if (beleg == "") {
			errorHandler({"field":"beleg", "description":"Kein Beleg angegeben"});
		}
		if (postdate == "") {
			errorHandler({"field":"postdate", "description":"Kein Buchungsdatum angegeben"});
		}
		if (isNaN(value)) {
			errorHandler({"field":"value", "description":"Ungültiger Betrag"});
		}

		var vorgang, vorlage_anteile;
		if (vorlage.evaluate !== undefined) {
			var result = vorlage.evaluate(txid, beleg, postdate, value);
			vorgang = result.vorgang;
			vorlage_anteile = result.anteile;
		} else {
			vorgang = vorlage.vorgang(txid, beleg, postdate, value);
			vorlage_anteile = vorlage.anteile(txid, beleg, postdate, value);
		}

		var anteile = [];
		for (i in vorlage_anteile) {
			anteile[vorlage_anteile[i]["konto"]] = parseInt(vorlage_anteile[i]["anteil"] * value);
		}

		var splits = this.kv.getSplits(errorHandler);
		for (i in splits) {
			if (!(splits[i].konto in anteile)) {
				anteile[splits[i].konto] = 0;
			}
			anteile[splits[i].konto] += splits[i].value;
		}

		splits = [];
		for (konto in anteile) {
			splits.push({"konto":konto,"value":anteile[konto]});
		}

		return {"guid": txid, "beleg": beleg, "postdate": postdate, "description": vorgang, "splits": splits, "buchen": true};
	}

	this.kv = new KontenView();

	var v = [];
	for (i in this._vorlagen) {
		v[i] = this._vorlagen[i]["label"];
	}

	this._panel = $("<fieldset>")
		.append(getInputField({"name":"beleg", "size":2, "label":"Beleg"}))
		.append(getInputField({"name":"postdate", "size":2, "label":"Datum", "type":"date"}))
		.append(getInputField({"name":"value", "size":2, "label":"Betrag", "type":"currency"}))
		.append(getInputField({"name":"vorlage", "size":2, "label":"Vorlage", "type":"select", "data":v}))
		.append(extra_fields)
		.append(this.kv.getPanel())
}
