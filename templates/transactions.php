<?php
if (isset($account)) {
	$title = "Kontenansicht " . $account["code"] . " " . $account["label"];
}
include(dirname(__FILE__) . "/header.php");
?>
<?php if (loginHasFacility("simpleTransactions")) { ?>
	<ul class="nav nav-tabs kontoPrefixSelect">
		<li data-prefix="23" data-toggle="button"><a>Übertrag</a></li>
		<li data-prefix="3" data-toggle="button"><a>Einnahmen</a></li>
		<li data-prefix="4" data-toggle="button"><a>Ausgaben</a></li>
	</ul>
<?php } else { ?>
	<div class="btn-toolbar">
<?php if (!isset($account)) { ?>
		<div class="btn-group">
			<a href="#" class="dropdown-toggle btn btn-default" data-toggle="dropdown">Konten <b class="caret"></b></a>
			<ul class="dropdown-menu kontenSelect">
<?php
	$accountSpaces = array();
	foreach ($accounts as $_account) {
		$space = (isset($accountSpaces[$_account["parent_guid"]]) ? $accountSpaces[$_account["parent_guid"]] . "<i class=\"icon-empty\"></i>" : "");
		$accountSpaces[$_account["guid"]] = $space;
		if (isAllowedAccount($_account)) {
?>
				<li class="account-<?php print($_account["guid"]) ?>"><a data-konto="<?php print($_account["guid"]) ?>" data-kontocode="<?php print($_account["code"]) ?>"><?php print($space) ?><i class="icon-<?php print($_account["placeholder"] == 0 ? "briefcase" : "book") ?>"></i> <?php print($_account["code"]) ?> <?php print($_account["label"]) ?></a></li>
<?php } } ?>
			</ul>
		</div>
<?php } ?>

		<div class="btn-group">
			<a href="#" class="dropdown-toggle btn btn-default" data-toggle="dropdown">Monat <b class="caret"></b></a>
			<ul class="dropdown-menu monthSelect">
				<li><a data-start="<?php print(mktime(0,0,0, 1,1,$year)) ?>" data-end="<?php print(mktime(0,0,0, 2,1,$year)-1) ?>">Januar</a></li>
				<li><a data-start="<?php print(mktime(0,0,0, 2,1,$year)) ?>" data-end="<?php print(mktime(0,0,0, 3,1,$year)-1) ?>">Februar</a></li>
				<li><a data-start="<?php print(mktime(0,0,0, 3,1,$year)) ?>" data-end="<?php print(mktime(0,0,0, 4,1,$year)-1) ?>">März</a></li>
				<li><a data-start="<?php print(mktime(0,0,0, 4,1,$year)) ?>" data-end="<?php print(mktime(0,0,0, 5,1,$year)-1) ?>">April</a></li>
				<li><a data-start="<?php print(mktime(0,0,0, 5,1,$year)) ?>" data-end="<?php print(mktime(0,0,0, 6,1,$year)-1) ?>">Mai</a></li>
				<li><a data-start="<?php print(mktime(0,0,0, 6,1,$year)) ?>" data-end="<?php print(mktime(0,0,0, 7,1,$year)-1) ?>">Juni</a></li>
				<li><a data-start="<?php print(mktime(0,0,0, 7,1,$year)) ?>" data-end="<?php print(mktime(0,0,0, 8,1,$year)-1) ?>">Juli</a></li>
				<li><a data-start="<?php print(mktime(0,0,0, 8,1,$year)) ?>" data-end="<?php print(mktime(0,0,0, 9,1,$year)-1) ?>">August</a></li>
				<li><a data-start="<?php print(mktime(0,0,0, 9,1,$year)) ?>" data-end="<?php print(mktime(0,0,0,10,1,$year)-1) ?>">September</a></li>
				<li><a data-start="<?php print(mktime(0,0,0,10,1,$year)) ?>" data-end="<?php print(mktime(0,0,0,11,1,$year)-1) ?>">Oktober</a></li>
				<li><a data-start="<?php print(mktime(0,0,0,11,1,$year)) ?>" data-end="<?php print(mktime(0,0,0,12,1,$year)-1) ?>">November</a></li>
				<li><a data-start="<?php print(mktime(0,0,0,12,1,$year)) ?>" data-end="<?php print(mktime(0,0,0,13,1,$year)-1) ?>">Dezember</a></li>
			</ul>
		</div>

		<div class="btn-group">
			<a class="btn btn-default filterButton" data-filter="num" data-toggle="button">Belegt</a>
			<a class="btn btn-default filterButton" data-filter="not-num" data-toggle="button">Nicht belegt</a>
			<a class="btn btn-default filterButton" data-filter="numStartsWith" data-options='{"prefix":"*"}' data-toggle="button">Beleg Unvollständig</a>
		</div>
		<div class="btn-group">
			<a class="btn btn-default filterButton" data-filter="not-descStartsWith" data-options='{"prefix":"Mitgliedsbeitrag"}' data-toggle="button">Mitgliedsbeiträge verstecken</a>
		</div>
		<div class="btn-group">
			<a class="btn btn-default filterButton" data-filter="verifiedAbove" data-options='{"count":0}' data-toggle="button">Verifiziert</a>
			<a class="btn btn-default filterButton" data-filter="not-verifiedAbove" data-options='{"count":0}' data-toggle="button">Nicht verifiziert</a>
			<a class="btn btn-default filterButton" data-filter="failedVerificationsAbove" data-options='{"count":0}' data-toggle="button">Fehlerhafte Verifikation</a>
		</div>

		<input type="text" class="pull-right span1 belegFilter" placeholder="Beleg" />
	</div>
<?php } ?>
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="sorting-post_date"><i class="icon-arrow-down sortingIcon"></i> Datum</th>
				<th class="sorting-num"><i class="icon-arrow-down sortingIcon"></i> Beleg</th>
				<th>Vorgang</th>
				<th class="hide transactionSoll">Soll</th>
				<th class="hide transactionHaben">Haben</th>
			</tr>
		</thead>
		<tbody class="transactions">
		</tbody>
		<tfoot>
			<tr class="transactions-loading hide">
				<td class="fullColspan"><img src="loading.gif" /> Bitte warten, weitere Transaktionen werden geladen ...</td>
			</tr>
			<tr class="transactions-empty">
				<td class="fullColspan">Keine Transaktionen gefunden</td>
			</tr>
			<tr class="hide habenSollSum">
				<td colspan="3" rowspan="2">&nbsp;</td>
				<th class="sumSoll">-</th>
				<th class="sumHaben">-</th>
			</tr>
			<tr class="hide habenSollSum">
				<th colspan="2" style="text-align:center;" class="sum">-</th>
			</tr>
		</tfoot>
	</table>
	<div class="modal transactionDetailsModal">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<div class="validations pull-right"></div>
					<h3>Details <span class="transactionId"></span></h3>
				</div>
				<div class="modal-body">
					<p class="description"></p>
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Konto</th>
								<th>Vermerk</th>
								<th>Soll</th>
								<th>Haben</th>
							</tr>
						</thead>
						<tbody class="splits">
						</tbody>
					</table>
					<div class="verifyOptions">
						<label class="checkbox">
							<input type="checkbox" />
							Beleg vorhanden
						</label>
						<label class="checkbox">
							<input type="checkbox" />
							Buchungsdatum stimmt mit Beleg überein
						</label>
						<label class="checkbox">
							<input type="checkbox" />
							Betrag stimmt mit Beleg überein
						</label>
						<label class="checkbox">
							<input type="checkbox" />
							Buchungskonten stimmen mit Beleg überein
						</label>
						<label class="checkbox">
							<input type="checkbox" />
							Beschluss nicht nötig oder korrekt eingetragen
						</label>
						<label class="checkbox">
							<input type="checkbox" />
							Angegebene Anlagen sind vorhanden und Angaben stimmen
						</label>
						<label class="checkbox">
							<input type="checkbox" />
							Buchungskonten sind sinnvoll gewählt und sofern nötig dokumentiert
						</label>
						<label class="checkbox">
							<input type="checkbox" />
							Entweder kein Mitgliedsbeitrag oder Beitrag, Mitgliedsname, -nummer und Bundesland sind mit der Mitgliederverwaltung abgeglichen
						</label>
					</div>
				</div>
				<div class="modal-footer">
					<a class="btn btn-danger revokeValidation">Freigeben</a>
					<a class="btn btn-success createValidation">Validieren</a>
					<a class="btn btn-danger revokeTransaction">Stornieren</a>
				</div>
			</div>
		</div>
	</div>
<script type="text/javascript">

function formatVermerkHTML(vermerk) {
	vermerk = vermerk.replace(/(#)([1-9][0-9]{0,5})([^0-9]|$)/g, '<a href="http://vpanel.intern.junge-piraten.de/members.php?mitgliedersuche=$2">$1$2</a>$3');
	vermerk = vermerk.replace(/(#)([1-9][0-9]{6,})([^0-9]|$)/g, '<a href="http://helpdesk.junge-piraten.de/otrs/index.pl?Action=AgentTicketSearch&Subaction=Search&TicketNumber=$2">$1$2</a>$3');
	return vermerk;
}

function formatTimestamp(timestamp) {
	// Server = CE(S)T, Client = UTC - 5 Hours offset might be not the best way, but it works
	var date = new Date(timestamp * 1000 + 5*60*60*1000);
	return date.getFullYear() + "-" + (date.getMonth() < 9 ? "0" : "") + (date.getMonth()+1) + "-" + (date.getDate() < 10 ? "0" : "") + date.getDate();
}

$(function () {
	refreshFilters();
	$(window).scroll(function() {
		chargeTransactionsIfNeeded();
	});
});

var nextOffset = 0;
var chargingTransactions = null;
var currentAccountCodePrefix = null;
var currentFilter = {};
var currentSorting = {field: "post_date", order: "asc"};
var currentSoll = 0.0;
var currentHaben = 0.0;

function formatCurrency(value) {
	return (value/100).toFixed(2) + " EUR";
}

function addValue(value) {
	if (value == false) {
		currentSoll = 0;
		currentHaben = 0;
	} else {
		if (value > 0)
			currentSoll += value;
		else
			currentHaben -= value;
	}

	$(".sumSoll").text(formatCurrency(currentSoll));
	$(".sumHaben").text(formatCurrency(currentHaben));
	$(".sum").text(formatCurrency(currentSoll - currentHaben));
}

function generateTransactionLine(transaction) {
	var value = null;
	if (currentAccountCodePrefix != null) {
		value = 0;
		for (var i=0; i < transaction.splits.length; i++) {
			var split = transaction.splits[i];
			if (split.account_code.indexOf(currentAccountCodePrefix) == 0) {
				value += parseInt(split.value);
			}
		}
		addValue(value);
	}

	return $("<tr>").addClass("transaction-" + transaction.guid)
		.data("transaction", transaction)
		.toggleClass("success", transaction.validValidations > 0 && transaction.validValidations == transaction.validations.length)
		.toggleClass("error", transaction.validValidations != transaction.validations.length)
		.click(function () {
			showTransaction($(this).data("transaction"));
		})
		.append($("<td>")
			.text(formatTimestamp(transaction.date)) )
		.append($("<td>")	
			.append($("<a>").attr("href","//vpanel.intern.junge-piraten.de/documents.php?dokumentsuche=BGS_F<?php print($year) ?>_" + transaction.num).text(transaction.num)))
		.append($("<td>")
			.append($("<span>").html(formatVermerkHTML(transaction.description || "")))
			.append($("<span>").addClass("pull-right")
				.append($("<span>")
					.addClass("label label-important")
					.toggle(transaction.validValidations != transaction.validations.length)
					.text("Fehler"))
				.append($("<span>")
					.addClass("label label-warning")
					.toggle(transaction.validValidations <= 0)
					.text("Nicht verifiziert"))
				.append($("<span>")
					.addClass("badge validValidationCount")
					.toggleClass("badge-success", transaction.validValidations > 0)
					.text(transaction.validValidations))
				))
		.append($("<td>")
			.toggle(value != null)
			.append(value > 0 ? formatCurrency(value) : "") )
		.append($("<td>")
			.toggle(value != null)
			.append(value < 0 ? formatCurrency(value*(-1)) : "") );
}

function chargeTransactionsIfNeeded() {
	if (($("body").height() - $(window).scrollTop()) < ($(window).height() * 1.5)) {
		chargeTransactions();
	}
}

function chargeTransactions(force) {
	if (nextOffset == null) {
		return;
	}
	if (force == true && chargingTransactions != null) {
		chargingTransactions.abort();
		chargingTransactions = null;
	}
	$(".transactions-empty").hide();
	if (chargingTransactions == null) {
		$(".transactions-loading").show();
		chargingTransactions = $.post("transactions.json.php", {year: "<?php print($year) ?>", offset: nextOffset, filter: currentFilter, sorting: currentSorting}, function (data) {
			for (var i in data.transactions) {
				$(".transactions").append(generateTransactionLine(data.transactions[i]))
			}
			$(".transactions-loading").hide();
			$(".transactions-empty").toggle($(".transactions").children().length == 0);
			nextOffset = data.nextOffset;
			chargingTransactions = null;
			chargeTransactionsIfNeeded();
		});
	}
}

function showTransaction(data) {
	$(".transactionDetailsModal").data("guid", data.guid);
	$(".transactionDetailsModal").find(".transactionId").text(data.guid.substring(0,6));
	$(".transactionDetailsModal").find(".description").html(formatVermerkHTML(data.description || ""));

	$(".transactionDetailsModal").find(".splits").empty();
	var habenKonten = [], sollKonten = [], betrag = 0;
	for (var i in data.splits) {
		if (data.splits[i].value > 0) {
			sollKonten.push(data.splits[i].account_code);
			betrag += parseInt(data.splits[i].value);
		} else {
			habenKonten.push(data.splits[i].account_code);
		}
		$(".transactionDetailsModal").find(".splits").append($("<tr>")
			.data("guid",data.splits[i].guid)
			.append($("<td>")
				.data("guid", data.splits[i].account_guid)
				.click(function () {
					$(".kontenSelect li").removeClass("active");
					$(".kontenSelect li.account-" + $(this).data("guid")).addClass("active");
					refreshFilters();
					$(".transactionDetailsModal").modal("hide");
				})
				.attr("title", data.splits[i].account_label)
				.text(data.splits[i].account_code) )
			.append($("<td>")
				.append(" " + formatVermerkHTML(data.splits[i].memo)) )
			.append($("<td>").text(data.splits[i].value > 0 ? formatCurrency(data.splits[i].value) : ""))
			.append($("<td>").text(data.splits[i].value < 0 ? formatCurrency((-1)*data.splits[i].value) : "")) );
	}
	$(".transactionDetailsModal").find(".validations").empty();
	var validated = false;
	for (var i in data.validations) {
		if (data.validations[i].username == "<?php print($auth["user"]) ?>") {
			validated = true;
		}
		$(".transactionDetailsModal").find(".validations").append($("<span>")
			.addClass("label")
			.css("background-color",d3.scale.category10()(data.validations[i].username))
			.data("valid", data.validations[i].valid)
			.attr("title",(data.validations[i].valid ? "Gültige" : "Ungültige") + " Verifikation durch " + data.validations[i].username)
			.toggleClass("strike-trough", !data.validations[i].valid)
			.text(data.validations[i].username) );
	}

	$(".transactionDetailsModal").find(".verifyOptions").hide();
	$(".transactionDetailsModal").find(".verifyOptions").find("input[type=checkbox]")
		.prop("checked",false)
		.unbind("change").on("change",function () {
			var allChecked = ($(".transactionDetailsModal").find(".verifyOptions").find("input[type=checkbox]").filter(":not(:checked)").filter(":not(.disabled)").length == 0);
			$(".transactionDetailsModal").find(".createValidation").toggleClass("disabled", !allChecked);
		});
	$(".transactionDetailsModal").find(".createValidation")
		.toggle(!validated)
		.removeClass("disabled")
		.unbind("click").click(function () {
			if (! $(".transactionDetailsModal").find(".verifyOptions").is(":visible")) {
				$(".transactionDetailsModal").find(".verifyOptions").fadeIn();
				$(this).addClass("disabled");
			} else {
				if (!$(this).hasClass("disabled")) {
					$.post("transaction.validate.php", { guid: $(".transactionDetailsModal").data("guid") }, function (data) {
						$(".transactions").find(".transaction-" + $(".transactionDetailsModal").data("guid")).replaceWith(generateTransactionLine(data.transaction));
						$(".transactionDetailsModal").modal("hide");
					});
				}
			}
		});
	$(".transactionDetailsModal").find(".revokeValidation")
		.toggle(validated)
		.unbind("click").click(function () {
			$.post("transaction.revokeValidation.php", { guid: $(".transactionDetailsModal").data("guid") }, function (data) {
				$(".transactions").find(".transaction-" + $(".transactionDetailsModal").data("guid")).replaceWith(generateTransactionLine(data.transaction));
				$(".transactionDetailsModal").modal("hide");
			});
		});

	var revokeTransactionData = {
		vorgang: "Storno " + data.description,
		splits: data.splits.map(function (split) {
			return {"value": split["value"]*(-1)*(-1), "konto": split["account_code"]};
		}),
	};
	$(".transactionDetailsModal").find(".revokeTransaction").attr("href","buchen.php#"+btoa("dialog#" + JSON.stringify(revokeTransactionData)));

	$(".transactionDetailsModal").modal('show');
}

function refreshFilters() {
	var flagsFilter = {type: "true"};
	if ($(".filterButton.active").length > 0) {
		var conds = [];
		$(".filterButton.active").each(function (i, button) {
			var options = $(button).data("options") || {};
			if ($(button).data("filter").substring(0,4) == "not-")
				conds.push({type: "not", cond: $.extend({type: $(button).data("filter").substring(4)}, options)});
			else
				conds.push($.extend({type: $(button).data("filter")}, options));
		});
		flagsFilter = {type: "and", conds: conds};
	}

	var belegFilter = {type: "true"};
	if ($(".belegFilter").val() != "") {
		belegFilter = {type: "num", num: $(".belegFilter").val()};
	}

<?php if (isset($account)) { ?>
	currentAccountCodePrefix = "<?php print($account["code"]) ?>";
	kontenFilter = {type: "account", guid: "<?php print($account["guid"]) ?>"};
<?php } else { ?>
	if ($(".kontoPrefixSelect li.active").length == 1) {
		currentAccountCodePrefix = $(".kontoPrefixSelect li.active").data("prefix");
		kontenFilter = {type: "accountCodeStartsWith", prefix: currentAccountCodePrefix};
	} else {
		currentAccountCodePrefix = null;
		kontenFilter = {type: "true"};
	}

	if ($(".kontenSelect li.active").length > 0) {
		var conds = [];
		$(".kontenSelect li.active").each(function(i, item) {
			conds.push({type: "account", guid: $(item).children("a").data("konto"), code: $(item).children("a").data("kontocode")});
		});
		if (conds.length == 1) {
			kontenFilter = conds.pop();
			currentAccountCodePrefix = kontenFilter.code;
		} else {
			kontenFilter = {type: "or", conds: conds};
		}
	}
<?php } ?>

	$(".transactionSoll,.transactionHaben, .habenSollSum").toggle(currentAccountCodePrefix != null);
	$(".fullColspan").attr("colspan", (currentAccountCodePrefix != null ? "5" : "3"));

	var monthFilter = {type: "true"};
	if ($(".monthSelect li.active").length > 0) {
		var conds = [];
		$(".monthSelect li.active").each(function(i, item) {
			conds.push({type: "daterange", start: $(item).children("a").data("start"), end: $(item).children("a").data("end")});
		});
		monthFilter = {type: "or", conds: conds};
	}

	currentFilter = {type: "and", conds: [ flagsFilter, belegFilter, kontenFilter, monthFilter ]};
	refreshView();
}

function refreshSorting() {
	if (currentSorting.order == "asc") {
		currentSorting.order = "desc";
	} else {
		currentSorting.order = "asc";
	}
	$(".sortingIcon").hide();
	$(".sorting-" + currentSorting.field + " i.sortingIcon")
		.toggleClass("icon-arrow-down", currentSorting.order == "asc")
		.toggleClass("icon-arrow-up",   currentSorting.order != "asc")
		.show();
	refreshView();
}

function refreshView() {
	nextOffset = 0;
	addValue(false);
	$(".transactions").empty();
	$(".transactions-empty").show();
	chargeTransactions(true);
}

refreshSorting();

$(".sorting-post_date").click(function () {
	currentSorting.field = "post_date";
	refreshSorting();
});

$(".sorting-num").click(function () {
	currentSorting.field = "num";
	refreshSorting();
});

$(".filterButton").click(function(ev) {
	// Toggle selbst, wird sonst erst nach diesem handler ausgefuehrt
	ev.stopPropagation();
	$(this).button("toggle");
	refreshFilters();
});

$(".kontoPrefixSelect li").click(function(ev) {
	// Toggle selbst, wird sonst erst nach diesem handler ausgefuehrt
	ev.stopPropagation();
	if (! $(this).hasClass("active")) {
		$(this).parent().children("li.active").removeClass("active");
	}
	$(this).toggleClass("active");
	refreshFilters();
});

$(".kontenSelect li, .monthSelect li").click(function(ev) {
	// Toggle selbst, wird sonst erst nach diesem handler ausgefuehrt
	ev.stopPropagation();
	$(this).toggleClass("active");
	refreshFilters();
});

$(".belegFilter").keyup(function () {
	refreshFilters();
});

</script>
<?php
include(dirname(__FILE__) . "/footer.php");
?>
