<?php
include(dirname(__FILE__) . "/header.php");
?>
	<div class="btn-toolbar">
		<div class="btn-group">
			<a href="#" class="dropdown-toggle btn" data-toggle="dropdown">Konten <b class="caret"></b></a>
			<ul class="dropdown-menu kontenSelect">
<?php
$accountSpaces = array();
foreach ($accounts as $account) {
	$space = (isset($accountSpaces[$account["parent_guid"]]) ? $accountSpaces[$account["parent_guid"]] . "<i class=\"icon-empty\"></i>" : "");
	$accountSpaces[$account["guid"]] = $space;
	if (isAllowedAccount($account)) {
?>
				<li class="account-<?php print($account["guid"]) ?>"><a data-konto="<?php print($account["guid"]) ?>"><?php print($space) ?><i class="icon-<?php print($account["placeholder"] == 0 ? "briefcase" : "book") ?>"></i> <?php print($account["code"]) ?> <?php print($account["label"]) ?></a></li>
<?php } } ?>
			</ul>
		</div>

		<div class="btn-group">
			<a href="#" class="dropdown-toggle btn" data-toggle="dropdown">Monat <b class="caret"></b></a>
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

		<a class="btn btn-info filterButton" data-filter="num" data-toggle="button">Belegt</a>
		<a class="btn btn-danger filterButton" data-filter="not-num" data-toggle="button">Nicht belegt</a>
		<a class="btn filterButton" data-filter="verifiedAbove" data-options='{"count":1}' data-toggle="button">Verifiziert</a>
		<a class="btn filterButton" data-filter="not-verifiedAbove" data-options='{"count":1}' data-toggle="button">Nicht verifiziert</a>

		<input type="text" class="pull-right span1 belegFilter" placeholder="Beleg" />
	</div>
	<table class="table table-striped">
		<thead>
			<tr>
				<th>Datum</th>
				<th>Beleg</th>
				<th>Vorgang</th>
			</tr>
		</thead>
		<tbody class="transactions">
		</tbody>
		<tfoot>
			<tr class="transactions-loading hide">
				<td colspan="3"><img src="loading.gif" /> Bitte warten, weitere Transaktionen werden geladen ...</td>
			</tr>
			<tr class="transactions-empty">
				<td colspan="3">Keine Transaktionen gefunden</td>
			</tr>
		</tfoot>
	</table>
	<div class="modal hide transactionDetailsModal">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<div class="validations pull-right"></div>
			<h3>Details <span class="transactionId"></span></h3>
		</div>
		<div class="modal-body">
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
<!-- Verifikationsdialog mit Checkliste (Beleg vorhanden; Buchungsdatum, Betrag, Konten stimmen; Beschluss existiert und passt; Anlagen sind vorhanden und stimmen; Buchungskonten sinnvoll gewählt und dokumentiert; Bei Mitgliedsbeiträgen: Stimmen Beitrag, Mitgliedsname, Mitgliedsnummer, Bundesland) -->
				<label class="checkbox">
					<input type="checkbox" />
					Beleg vorhanden
				</label>
				<label class="checkbox">
					<input type="checkbox" />
					Buchungsdatum korrekt
				</label>
				<label class="checkbox">
					<input type="checkbox" />
					Betrag korrekt
				</label>
				<label class="checkbox">
					<input type="checkbox" />
					Buchungsdatum korrekt
				</label>
			</div>
		</div>
		<div class="modal-footer">
			<a class="btn btn-danger revokeValidation">Freigeben</a>
			<a class="btn btn-success createValidation">Validieren</a>
			<a class="btn btn-info createNum">Beleg erstellen</a>
		</div>
	</div>
<script type="text/javascript">

function formatVermerkHTML(vermerk) {
	vermerk = vermerk.replace(/(#)([1-9][0-9]{0,5})([^0-9]|$)/g, '<a href="http://verwaltung.junge-piraten.de/members.php?mitgliedersuche=$2">$1$2</a>$3');
	vermerk = vermerk.replace(/(#)([1-9][0-9]{6,})([^0-9]|$)/g, '<a href="http://helpdesk.junge-piraten.de/otrs/index.pl?Action=AgentTicketSearch&Subaction=Search&TicketNumber=$2">$1$2</a>$3');
	return vermerk;
}

function formatTimestamp(timestamp) {
	// Server = CE(S)T, Client = UTC - 5 Hours offset might be not the best way, but it works
	var date = new Date(timestamp * 1000 + 5*60*60*1000);
	return date.getFullYear() + "-" + (date.getMonth() < 9 ? "0" : "") + (date.getMonth()+1) + "-" + (date.getDate() < 10 ? "0" : "") + date.getDate();
}

$(function () {
	chargeTransactions(true);
	$(window).scroll(function() {
		chargeTransactionsIfNeeded();
	});
});

var nextOffset = 0;
var chargingTransactions = null;
var currentFilter = {};

function generateTransactionLine(transaction) {
	return $("<tr>").addClass("transaction-" + transaction.guid)
		.data("transaction", transaction)
		.click(function () {
			showTransaction($(this).data("transaction"));
		})
		.append($("<td>")
			.text(formatTimestamp(transaction.date)) )
		.append($("<td>")	
			.append($("<a>").attr("href","/documents.php?dokumentsuche=BGS_F<?php print($year) ?>_" + transaction.num).text(transaction.num)))
		.append($("<td>")
			.append($("<span>").html(formatVermerkHTML(transaction.description || "")))
			.append($("<span>").addClass("pull-right")
				.append($("<span>")
					.addClass("label label-important")
					.toggle(transaction.validValidations != transaction.validations.length)
					.text("Fehler"))
				.append($("<span>")
					.addClass("label label-warning")
					.toggle(transaction.validValidations < 2)
					.text("Nicht verifiziert"))
				.append($("<span>")
					.addClass("badge validValidationCount")
					.toggleClass("badge-success", transaction.validValidations >= 2)
					.text(transaction.validValidations))
				));
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
		chargingTransactions = $.post("transactions.json.php", {offset: nextOffset, filter: currentFilter}, function (data) {
			for (var i in data.transactions) {
				$(".transactions").append(generateTransactionLine(data.transactions[i]))
			}
			// .transactions-empty und .transactions-loading befinden sich immer in .transactions
			$(".transactions-loading").hide();
			$(".transactions-empty").toggle($(".transactions").children().length <= 2);
			nextOffset = data.nextOffset;
			chargingTransactions = null;
			chargeTransactionsIfNeeded();
		});
	}
}

function showTransaction(data) {
	$(".transactionDetailsModal").data("guid", data.guid);
	$(".transactionDetailsModal").find(".transactionId").text(data.guid.substring(0,6));

	$(".transactionDetailsModal").find(".splits").empty();
	var habenKonten = [], sollKonten = [], betrag = 0;
	for (var i in data.splits) {
		if (data.splits[i].value > 0) {
			sollKonten.push(data.splits[i].account_code);
			betrag += parseFloat(data.splits[i].value);
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
			.append($("<td>").text(data.splits[i].value > 0 ? parseFloat(data.splits[i].value).toFixed(2) + " EUR" : ""))
			.append($("<td>").text(data.splits[i].value < 0 ? parseFloat((-1)*data.splits[i].value).toFixed(2) + " EUR" : "")) );
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
	$(".transactionDetailsModal").find(".createValidation").toggle(!validated).unbind("click").click(function () {
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
	$(".transactionDetailsModal").find(".revokeValidation").toggle(validated).unbind("click").click(function () {
		$.post("transaction.revokeValidation.php", { guid: $(".transactionDetailsModal").data("guid") }, function (data) {
			$(".transactions").find(".transaction-" + $(".transactionDetailsModal").data("guid")).replaceWith(generateTransactionLine(data.transaction));
			$(".transactionDetailsModal").modal("hide");
		});
	});

	// createNum-String
	var belegData = {
		buchungsDatum: formatTimestamp(data.date),
		sollKonten: sollKonten.join(", "),
		habenKonten: habenKonten.join(", "),
		betrag: betrag,
		anmerkungen: data.description
	};
	$(".transactionDetailsModal").find(".createNum").attr("href","belege.php?_=" + encodeURIComponent(JSON.stringify(belegData)));
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

	var kontenFilter = {type: "true"};
	if ($(".kontenSelect li.active").length > 0) {
		var conds = [];
		$(".kontenSelect li.active").each(function(i, item) {
			conds.push({type: "account", guid: $(item).children("a").data("konto")});
		});
		kontenFilter = {type: "or", conds: conds};
	}

	var monthFilter = {type: "true"};
	if ($(".monthSelect li.active").length > 0) {
		var conds = [];
		$(".monthSelect li.active").each(function(i, item) {
			conds.push({type: "daterange", start: $(item).children("a").data("start"), end: $(item).children("a").data("end")});
		});
		monthFilter = {type: "or", conds: conds};
	}

	currentFilter = {type: "and", conds: [ flagsFilter, belegFilter, kontenFilter, monthFilter ]};
	nextOffset = 0;
	$(".transactions").empty();
	$(".transactions-empty").show();
	chargeTransactions(true);
}

$(".filterButton").click(function(ev) {
	// Toggle selbst, wird sonst erst nach diesem handler ausgefuehrt
	ev.stopPropagation();
	$(this).button("toggle");
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
