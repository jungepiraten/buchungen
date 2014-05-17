function DialogBuchen(mainKontoPrefix, settings) {
	var _buchen = this;
	this._initValue;
	this._mainKontoPrefix = mainKontoPrefix;
	this._settings = settings;
	this._kontenViews = [];

	this.getPanel = function() {
		return this._panel;
	}
	this.load = function(data) {
		this._panel.find("input[name=beleg]").val(data["num"]);
		this._panel.find("input[name=postdate]").val(data["postdate"]);
		for (i in data["splits"]) {
			this._kontenViews[0]["kw"]._addLine(data["splits"][i]["value"], data["splits"][i]["konto"]);
		}
	}
	this.clean = function() {
		this._panel.find("input").val("");
		this._panel.find(".splits .row").remove();
		this._kontenViews[0]["kw"].updateView();
		this._panel.find("input[name=beleg]").focus();
	}
	this.evaluate = function() {
		var errors = [];

		var txid = get128bitRandom();
		var beleg = this._panel.find("input[name=beleg]").val();
		var postdate = this._panel.find("input[name=postdate]").val();
		var vorgang = this._panel.find("input[name=vorgang]").val();
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

		for (i in this._kontenViews) {
			Array.prototype.push.apply(splits, this._kontenViews[i]["kw"].getSplits({
				"kontoprefix": this._kontenViews[i]["prefix"],
				"errorHandler": function(error) {
					errors.push(error);
				}
			}) );
		}

		if (splits.length == 0) {
			errors.push({"field":$(".splits .row:first").find(".konto").attr("name"), "description":"Leere Buchung"});
		}

		return {
			"errors" : errors,
			"buchung": {"guid": txid, "beleg": beleg, "postdate": postdate, "description": vorgang, "splits": splits, "buchen": true}
		};
	}

	this._getKontoKategorie = function(kontoCode) {
		for (var i in this._kontenViews) {
			for (k in this._kontenViews[i]["ausloeser"]) {
				var ausloeser = this._kontenViews[i]["ausloeser"][k];
				if (kontoCode.substring(0,ausloeser.length) == ausloeser) {
					return i;
				}
			}
		}
		return null;
	}

	this._kontenViews.push({"prefix":this._mainKontoPrefix, "kw":new KontenView({
		"updateCallback": function(entries) {
			var values = {};
			this.eachEntry(function (konto, value) {
				var kategorie = _buchen._getKontoKategorie(konto);
				if (kategorie != null) {
					if (!(kategorie in values)) {
						values[kategorie] = 0;
					}
					values[kategorie] -= value;
				}
			});
			for (i in _buchen._kontenViews) {
				if (i != 0) {
					_buchen._kontenViews[i]["kw"].updateView(i in values ? values[i] : 0);
				}
			}
		}
	}) });
	for (i in this._settings) {
		this._kontenViews.push({
			"ausloeser": this._settings[i]["ausloeser"],
			"prefix": this._settings[i]["kontoprefix"],
			"kw": new KontenView({
				"forceLine": {
					"label": this._settings[i]["label"],
					"konto": this._settings[i]["konto"],
				}
			})
		});
	}

	this._panel = $('<div>')
		.append('<fieldset>' +
				'<div class="form-group">' +
					'<label class="col-sm-2 control-label" for="beleg">Beleg</label>' +
					'<div class="col-sm-2"><input type="text" class="form-control" name="beleg" /></div>' +
				'</div>' +
				'<div class="form-group">' +
					'<label class="col-sm-2 control-label" for="postdate">Datum</label>' +
					'<div class="col-sm-2"><input type="date" class="form-control" name="postdate" /></div>' +
				'</div>' +
				'<div class="form-group">' +
					'<label class="col-sm-2 control-label" for="vorgang">Vorgang</label>' +
					'<div class="col-sm-10"><input type="text" class="form-control" name="vorgang" /></div>' +
				'</div>' +
			'</fieldset>')
		.append($('<fieldset>')
			.append($('<legend>').text("Splits"))
			.append($('<div class="row">')
				.append('<div class="col-xs-8"></div>')
				.append('<div class="col-xs-2"><strong>Soll</strong></div>')
				.append('<div class="col-xs-2"><strong>Haben</strong></div>') )
			.append(function (){
				var a=[];
				for (b in _buchen._kontenViews) {
					if (b != 0) {
						a.push("&nbsp;");
					}
					a.push(_buchen._kontenViews[b]["kw"].getPanel());
				}
				return a;
			}()) );
}
