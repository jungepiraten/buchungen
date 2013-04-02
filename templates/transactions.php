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
				<li><a data-month="<?php print($year) ?>01">Januar</a></li>
				<li><a data-month="<?php print($year) ?>02">Februar</a></li>
				<li><a data-month="<?php print($year) ?>03">März</a></li>
				<li><a data-month="<?php print($year) ?>04">April</a></li>
				<li><a data-month="<?php print($year) ?>05">Mai</a></li>
				<li><a data-month="<?php print($year) ?>06">Juni</a></li>
				<li><a data-month="<?php print($year) ?>07">Juli</a></li>
				<li><a data-month="<?php print($year) ?>08">August</a></li>
				<li><a data-month="<?php print($year) ?>09">September</a></li>
				<li><a data-month="<?php print($year) ?>10">Oktober</a></li>
				<li><a data-month="<?php print($year) ?>11">November</a></li>
				<li><a data-month="<?php print($year) ?>12">Dezember</a></li>
			</ul>
		</div>

		<a class="btn btn-info filterButton" data-filter="num" data-toggle="button">Belegt</a>
		<a class="btn btn-danger filterButton" data-filter="not-num" data-toggle="button">Nicht belegt</a>

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
			<tr class="transactions-loading">
				<td colspan="3"><img src="loading.gif" /> Bitte warten, weitere Transaktionen werden geladen ...</td>
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

$(function () {
	chargeTransactions();
	$(window).scroll(function() {
		if (($("body").height() - $(this).scrollTop()) < ($(this).height() * 1.5)) {
			chargeTransactions();
		}
	});
});

var nextOffset = 0;
var chargingTransactions = false;
var currentFilter = {};

function generateTransactionLine(transaction) {
	return $("<tr>").addClass("transaction-" + transaction.guid)
		.data("transaction", transaction)
		.click(function () {
			showTransaction($(this).data("transaction"));
		})
		.append($("<td>")
			.text(new Date(transaction.date * 1000)) )
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
					.addClass("badge validValidationCount")
					.toggleClass("badge-success", transaction.validValidations >= 2)
					.text(transaction.validValidations))
				));
}

function chargeTransactions() {
	if (!chargingTransactions) {
		$(".transactions-loading").show();
		chargingTransactions = true;
		$.post("transactions.json.php", {offset: nextOffset, filter: currentFilter}, function (data) {
			for (var i in data.transactions) {
				$(".transactions").append(generateTransactionLine(data.transactions[i]))
			}
			nextOffset = data.nextOffset;
			chargingTransactions = false;
			$(".transactions-loading").hide();
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

	$(".transactionDetailsModal").find(".createValidation").toggle(!validated).unbind("click").click(function () {
		$.post("transaction.validate.php", { guid: $(".transactionDetailsModal").data("guid") }, function (data) {
			$(".transactions").find(".transaction-" + $(".transactionDetailsModal").data("guid")).replaceWith(generateTransactionLine(data.transaction));
			$(".transactionDetailsModal").modal("hide");
		});
	});
	$(".transactionDetailsModal").find(".revokeValidation").toggle(validated).unbind("click").click(function () {
		$.post("transaction.revokeValidation.php", { guid: $(".transactionDetailsModal").data("guid") }, function (data) {
			$(".transactions").find(".transaction-" + $(".transactionDetailsModal").data("guid")).replaceWith(generateTransactionLine(data.transaction));
			$(".transactionDetailsModal").modal("hide");
		});
	});
	// createNum-String
	// Server = CE(S)T, Client = UTC - 5 Hours offset might be not the best way, but it works
	var date = new Date(data.date * 1000 + 5*60*60*1000);
	var belegData = {
		buchungsDatum: date.getFullYear() + "-" + (date.getMonth() < 9 ? "0" : "") + (date.getMonth()+1) + "-" + (date.getDate() < 10 ? "0" : "") + date.getDate(),
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
			conds.push({type: $(button).data("filter")});
		});
		flagsFilter = {type: "or", conds: conds};
	}

	var belegFilter = {type: "true"};
	if ($(".belegFilter").val() != "") {
		belegFilter = {type: "beleg", value: $(".belegFilter").val()};
	}

	var kontenFilter = {type: "true"};
	if ($(".kontenSelect li.active").length > 0) {
		var conds = [];
		$(".kontenSelect li.active").each(function(i, item) {
			conds.push({type: "konten", konten: $(item).children("a").data("konto")});
		});
		kontenFilter = {type: "or", conds: conds};
	}

	var monthFilter = {type: "true"};
	if ($(".monthSelect li.active").length > 0) {
		var conds = [];
		$(".monthSelect li.active").each(function(i, item) {
			conds.push({type: "month", month: $(item).children("a").data("month")});
		});
		monthFilter = {type: "or", conds: conds};
	}

	currentFilter = {type: "and", conds: [ flagsFilter, belegFilter, kontenFilter, monthFilter ]};
	nextOffset = 0;
	$(".transactions").empty();
	chargeTransactions();
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
