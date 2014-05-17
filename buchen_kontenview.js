function KontenView(settings) {
	var _view = this;
	this._forceLine = ("forceLine" in settings) ? settings["forceLine"] : null;
	this._updateCallback = ("updateCallback" in settings) ? settings["updateCallback"] : function() {};
	this._panel = $('<div class="splits">');
	this._currentValue = 0;

	this.getPanel = function() {
		return this._panel;
	}
	this.eachEntry = function(callback) {
		this._panel.find(".row").each(function (i,elem) {
			callback(
				$(elem).find(".konto").val(),
				($(elem).find(".haben").val() - $(elem).find(".soll").val()) * 100,
				$(elem).find(".konto")
			);
		});
	}
	this.getSplits = function(settings) {
		var kontoprefix = "kontoprefix" in settings ? settings["kontoprefix"] : "";
		var errorHandler = settings["errorHandler"];
		var splits = [];
		this.eachEntry(function (konto, value, kontoField) {
			if (value == 0 && konto != "") {
				errorHandler({"field":kontoField.attr("name"), "description":"Nullbuchung"});
			} else if (value != 0 && konto == "" && ! kontoField.is(":hidden")) {
				errorHandler({"field":kontoField.attr("name"), "description":"Buchung ohne Konto"});
			} else if (value != 0) {
				splits.push({"konto": kontoprefix + konto, "value": value});
			}
		});
		return splits;
	}
	this.updateView = function(value) {
		this._currentValue = value;
		if (this._forceLine != null) {
			if (value == 0) {
				this._panel.find(".row").remove();
				this._panel.hide();
				return;
			} else {
				if (this._panel.find(".row").length == 0) {
					this._addLine(value, this._forceLine["konto"]);
					this._panel.find(".row:first").find("input").prop("disabled",true);
					this._panel.find(".row:first").find(".konto")
						.hide()
						.after($("<strong>").text(this._forceLine["label"]))
					this._panel.show();
				} else {
					this._panel.find(".row:first").find(".soll").val(value < 0 ? formatCurrency((-1)*value) : "");
					this._panel.find(".row:first").find(".haben").val(value > 0 ? formatCurrency(value) : "");
				}
			}
		}

		var remaining = 0;
		var remainingRow = null;
		this._panel.find(".row").each(function (i,elem) {
			if ($(elem).data("new") == "1") {
				remainingRow = $(elem);
			} else {
				remaining -= ($(elem).find(".haben").val() - $(elem).find(".soll").val()) * 100;
			}
		});

		if (remainingRow != null) {
			remainingRow.find(".soll").val(remaining < 0 ? formatCurrency((-1)*remaining) : "");
			remainingRow.find(".haben").val(remaining > 0 ? formatCurrency(remaining) : "");
		} else {
			this._addLine(remaining);
		}

		this._updateCallback();
	}
	this._addLine = function(remaining, konto) {
		function _update() {
			$(this).parents(".row").data("new",0);
			_view.updateView(_view._currentValue);
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
		this._panel.append($("<div>").addClass("row").data("new",konto != undefined ? "0" : "1")
			.append($("<div>").addClass("col-xs-8")
				.append($("<input>").on("input",_update).addClass("konto").attr("name","splits["+splitId+"][konto]").addClass("form-control").val(konto ? konto : "")) )
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

	this.updateView();
}
