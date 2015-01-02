<?php header("Content-Type: application/javascript"); include("sql.inc.php"); ?>
var kontenview_konten = [];
$.get("/accounts.json.php", {year: "<?php print($year); ?>"}, function (data) {kontenview_konten = data; }, "json");

function getKontoInput(prefix, maxlength) {
	function _suggestions(q) {
		return kontenview_konten
			.filter(function (k) {return k.code.indexOf(prefix) === 0 && (maxlength === undefined || maxlength == 0 || k.code.length == prefix.length + maxlength) && (k.code.indexOf(q) >= 0 || k.label.toLowerCase().indexOf(q.toLowerCase()) >= 0); })
			.map(function (k) {return k.code.substr(prefix.length) + " " + k.label;});
	}
	return getTypeAheadInput(_suggestions);
}

function getTypeAheadInput(suggestions) {
	var _input = $("<input>").addClass("form-control");
	new InputTypeAhead(_input, suggestions);
	return _input;
}

function InputTypeAhead(inputq, suggestions) {
	this.suggestions = suggestions;
	this.inputq = inputq;
	this.overlay = $("<div>");
	this.list = $("<ul>").css({background: "white", listStyle: "none", padding: "0px"}).appendTo(this.overlay);
	this.data = [];
	this.current = -1;
	this.active = false;
	this.ignoreKey = false;
	this.interval = null;

	this.inputq
		.keydown(this.keyDown.bind(this))
		.blur(this.onBlur.bind(this))
		.focus(this.onFocus.bind(this))
		.keyup(this.onChange.bind(this));
	$("body").append(this.overlay);
}

InputTypeAhead.prototype = {
	keyDown: function(e) {
		if(!this.active) return;
		this.ignoreKey = true;
		switch(e.keyCode) {
			case 40: //down
				e.preventDefault();
				this._next();
				break;
			case 38: //down
				e.preventDefault();
				this._prev();
				break;
			case 13: //enter
				if (this.active && this.current >= 0 && this.current < this.data.length) {
					this.inputq.val(this.data[this.current]);
					this._close();
					e.preventDefault();
				}
				break;
			case 27: //esc
				this._close();
				break;
			default:
				this.ignoreKey = false;
		}
	},
	onBlur: function() {
		this._close();
	},

	onChange: function() {
		if(this.ignoreKey) {
			this.ignoreKey= false;
			return;
		}
		var q = this.inputq.val();
		if(q.trim() == "") {
			this._close();
		} else {
			this.search(q);
		}
	},
	
	onFocus: function() {
		this.onChange();
	},
	search: function(q) {
		this._open(this.suggestions(q));
	},
	
	_renderData: function(data) {
		this.overlay.css({
			position: "absolute",
			left: (this.inputq.offset().left) + "px",
			top: (this.inputq.offset().top + this.inputq.outerHeight()) + "px",
			width: (this.inputq.outerWidth()) + "px",
			});
		this.list.empty();
		this.data = data;
		this.list.append(data.map(function (_val) {return $("<li>").text(_val); }));
	},

	_select: function(i) {
		if(!this.active) return;
		if(i < 0 || i >= this.data.length) return;
		var lis = this.list.children("li");
		lis.css("backgroundColor", "white");
		$(lis[i]).css("backgroundColor", "#ccc");
		this.current = i;
	},
	_next: function() {
		this._select(this.current+1);
	},
	_prev: function() {
		this._select(this.current-1);
	},
	_open: function(data) {
		if(data.length == 0) {
			this._close();
			return;
		}
		this._renderData(data);
		this._select(0);
		this.overlay.show();
		this.active = true;
	},
	_close: function() {
		this.overlay.hide();	
		this.active = false;
	}
}
