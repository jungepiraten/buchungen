function DialogBuchen(mainKontoPrefix, settings) {
	var _buchen = this;
	this._initValue;
	this._mainKontoPrefix = mainKontoPrefix;
	this._settings = settings;

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
			.append('<legend>Splits</legend>')
			.append($('<div class="row">')
				.append('<div class="col-xs-8"></div>')
				.append('<div class="col-xs-2"><strong>Soll</strong></div>')
				.append('<div class="col-xs-2"><strong>Haben</strong></div>') )
			.append($('<div class="splits splits-main">').data("kontoprefix", this._mainKontoPrefix))
			.append(function (){
				var a=[];
				for (b in _buchen._settings) {
					a.push($('<div class="splits">').addClass("splits-" + b).data("kontoprefix", _buchen._settings[b]["kontoprefix"]).html("&nbsp;"));
				}
				return a;
			}()) );

	this.getPanel = function() {
		return this._panel;
	}
	this.clean = function() {
		this._panel.find("input").val("");
		this._panel.find(".splits .row").remove();
		this._checkBalance("main");
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

		this._panel.find(".splits .row").each(function (i,row) {
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

		return {
			"errors" : errors,
			"buchung": {"guid": txid, "beleg": beleg, "postdate": postdate, "description": vorgang, "splits": splits, "buchen": true}
		};
	}

	this._getKontoKategorie = function(kontoCode) {
		for (i in this._settings) {
			for (k in this._settings[i]["ausloeser"]) {
				var ausloeser = this._settings[i]["ausloeser"][k];
				if (kontoCode.substring(0,ausloeser.length) == ausloeser) {
					return i;
				}
			}
		}
		return null;
	}
	this._checkBalance = function(bal) {
		var remaining = 0;
		var remainingRow = null;
		if (bal == "main") {
			this._initValue = {}
			for (i in this._settings) {
				this._initValue[i] = 0;
			}
		} else {
			if (this._initValue[bal] == 0) {
				this._panel.find(".splits-"+bal).find(".row").remove();
				this._panel.find(".splits-"+bal).hide();
				return;
			}
			if (this._panel.find(".splits-"+bal).find(".row").length == 0) {
				this._addBalanceLine(bal, this._initValue[bal]);
				this._panel.find(".splits-"+bal).find(".row:first").find("input").prop("disabled",true);
				this._panel.find(".splits-"+bal).find(".row:first").find(".konto")
					.val(this._settings[bal]["konto"])
					.hide()
					.after($("<strong>").text(this._settings[bal]["label"]));
			}
			this._panel.find(".splits-"+bal).find(".row:first").data("new",0);
			this._panel.find(".splits-"+bal).find(".row:first").find(".soll").val(this._initValue[bal] < 0 ? formatCurrency((-1) * this._initValue[bal]) : "");
			this._panel.find(".splits-"+bal).find(".row:first").find(".haben").val(this._initValue[bal] > 0 ? formatCurrency(this._initValue[bal]) : "");
			this._panel.find(".splits-"+bal).show();
		}
		this._panel.find(".splits-"+bal+" .row").each(function (i, elem) {
			if ($(elem).data("new") == "1") {
				remainingRow = $(elem);
			} else {
				if (bal == "main") {
					var kategorie = _buchen._getKontoKategorie($(elem).find(".konto").val());
					if (kategorie != null) {
						_buchen._initValue[kategorie] -= ($(elem).find(".haben").val() - $(elem).find(".soll").val()) * 100;
					}
				}
				remaining -= ($(elem).find(".haben").val() - $(elem).find(".soll").val()) * 100;
			}
		});

		if (bal == "main") {
			for (i in this._initValue) {
				this._checkBalance(i);
			}
		}

		if (remainingRow != null) {
			remainingRow.find(".soll").val(remaining < 0 ? formatCurrency((-1)*remaining) : "");
			remainingRow.find(".haben").val(remaining > 0 ? formatCurrency(remaining) : "");
		} else {
			this._addBalanceLine(bal, remaining);
		}
	}
	this._addBalanceLine = function(bal, remaining) {
		function _update() {
			var row = $(this).parents(".row");
			if (row.data("new") == "1") {
				row.data("new", "0");
			}
			_buchen._checkBalance(bal);
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

		var splitId = get32bitRandom();
		$(".splits-" + bal).append($("<div>").addClass("row").data("new","1")
			.append($("<div>").addClass("col-xs-8")
				.append($("<input>").on("input",_update).addClass("konto").attr("name","splits["+splitId+"][konto]").addClass("form-control")) )
			.append($("<div>").addClass("col-xs-2")
				.append($("<div>").addClass("input-group")
					.append($("<input>").change(formatCurrencyField).on("input",_update).addClass("soll").attr("name","splits["+splitId+"][soll]").css("text-align","right").addClass("form-control").val(remaining < 0 ? formatCurrency((-1)*remaining) : ""))
					.append($("<span>").addClass("input-group-addon").text("€")) ))
			.append($("<div>").addClass("col-xs-2")
				.append($("<div>").addClass("input-group")
					.append($("<input>").change(formatCurrencyField).on("input",_update).addClass("haben").attr("name","splits["+splitId+"][haben]").css("text-align","right").addClass("form-control").val(remaining > 0 ? formatCurrency(remaining) : ""))
					.append($("<span>").addClass("input-group-addon").text("€")) ))
		);
	}
}
