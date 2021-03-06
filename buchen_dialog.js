function DialogBuchen(mainKontoPrefix, settings) {
	var _buchen = this;
	this._mainKontoPrefix = mainKontoPrefix;
	this._settings = settings;
	this._kontenViews = [];

	this.getPanel = function() {
		return this._panel;
	}
	this.load = function(data) {
		this._panel.find("input[name=beleg]").val(data["num"]);
		this._panel.find("input[name=postdate]").val(data["postdate"]);
		if ("vorgang" in data) {
			this._panel.find("input[name=vorgang]").val(data["vorgang"]);
		}

		// Main-KontenView-Splits have to be done before dealing with minors (to activate minors)
		data["splits"].sort(function (a,b) {
			return b["konto"].charCodeAt(0) + a["konto"].charCodeAt(0) - 2 * this._kontenViews[0]["prefix"].charCodeAt(0);
		}.bind(this));
		for (i in data["splits"]) {
			var _kontenView = this._kontenViews.filter(function (_kw) {return data["splits"][i]["konto"].indexOf(_kw["prefix"]) === 0; })[0];
			if (data["splits"][i]["konto"] !== _kontenView["prefix"]) {
				_kontenView["kw"]._addLine(data["splits"][i]["value"], data["splits"][i]["konto"].substring(_kontenView["prefix"].length));
				_kontenView["kw"].updateView();
			}
		}
		this._kontenViews[0]["kw"].updateView();
		this._panel.find("input[name=vorgang]").val(data["vorgang"]).focus();
	}
	this.clean = function() {
		this._panel.find("input").val(function () {return $(this).data("init-value");});
		this._panel.find(".splits .row").remove();
		this._kontenViews[0]["kw"].updateView();
		this._panel.find("input[name=beleg]").focus();
	}
	this.evaluate = function(errorHandler) {
		var txid = get128bitRandom();
		var beleg = this._panel.find("input[name=beleg]").val();
		var postdate = this._panel.find("input[name=postdate]").val();
		var vorgang = this._panel.find("input[name=vorgang]").val();
		var splits = [];

		if (beleg == "") {
			errorHandler({"field":"beleg", "description":"Kein Beleg angegeben"});
		}
		if (postdate == "") {
			errorHandler({"field":"postdate", "description":"Kein Buchungsdatum angegeben"});
		}
		if (vorgang == "") {
			errorHandler({"field":"vorgang", "description":"Keinen Vorgang definiert"});
		}

		for (i in this._kontenViews) {
			Array.prototype.push.apply(splits, this._kontenViews[i].kw.getSplits(errorHandler));
		}

		if (splits.length == 0) {
			errorHandler({"field":$(".splits .row:first").find(".konto").attr("name"), "description":"Leere Buchung"});
		}

		return {"guid": txid, "beleg": beleg, "postdate": postdate, "description": vorgang, "splits": splits, "buchen": true};
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
		"kontoprefix": this._mainKontoPrefix,
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
				"kontoprefix": this._settings[i]["kontoprefix"],
				"forceLine": {
					"label": this._settings[i]["label"],
					"konto": this._settings[i]["konto"],
				}
			})
		});
	}

	this._panel = $('<div>')
		.append($('<fieldset>')
			.append(getInputField({"name":"beleg", "size":2, "label":"Beleg"}))
			.append(getInputField({"name":"postdate", "size":2, "label":"Datum", "type":"date"}))
			.append(getInputField({"name":"vorgang", "label":"Vorgang"}))
		)
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
