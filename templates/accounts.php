<?php
include(dirname(__FILE__) . "/header.php");
?>

<?php
$accountsHier = array(); $rootAccounts = array();
foreach ($accounts as $account) {
	if (isAllowedAccount($account)) {
		$account["childs"] = array();
		$accountsHier[$account["guid"]] = $account;
		if (isset($accountsHier[$account["parent_guid"]])) {
			$accountsHier[$account["parent_guid"]]["childs"][] = $account["guid"];
		} else {
			$rootAccounts[] = $account["guid"];
		}
	}
}

function outputAccountHier($hier, $guid) {
	foreach ($hier[$guid]["childs"] as $accountid) {
		$account = $hier[$accountid];
?>
	<li>
		<?php if(!empty($account["childs"])) { ?>
			<input type="checkbox" id="account-<?php print($account["guid"]) ?>" checked="checked" />
			<label for="account-<?php print($account["guid"]) ?>">
		<?php } ?>
		<a class="account" data-account='<?php print(addcslashes(json_encode($account),"'")) ?>'>
			<i class="icon-<?php print($account["placeholder"] == 0 ? "briefcase" : "book") ?>"></i> <?php print($account["code"]) ?> <?php print($account["label"]) ?>
		</a>
		<?php if(!empty($account["childs"])) { ?>
			</label>
		<?php } ?>
		<?php if(!empty($account["childs"])) { ?><ul><?php outputAccountHier($hier, $account["guid"]) ?></ul><?php } ?>
	</li>
<?php
	}
}
?>
<div class="css-treeview">
	<ul>
<?php
foreach ($rootAccounts as $guid) {
	outputAccountHier($accountsHier, $guid);
}
?>
	</ul>
</div>

<div class="modal hide accountModal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Details <span class="accountLabel"></span> (<span class="accountCode"></span>)</h3>
	</div>
	<div class="modal-body">
		<p class="description"></p>
		<div class="notificationsPanel hide">
			Muh
		</div>
	</div>
	<div class="modal-footer">
		<a class="btn btn-info showTransactions">Bewegungen anzeigen</a>
		<a class="btn btn-warning manageNotifications">Benachrichtigungen</a>
	</div>
</div>
<script type="text/javascript">

$(".account").click(function() {
	var data = $(this).data("account");
	$(".accountModal").find('.accountLabel').text(data.label);
	$(".accountModal").find('.accountCode').text(data.code);
	$(".accountModal").find('.description').text(data.description);

	$(".accountModal").find('.showTransactions').attr("href", "transactions.php?account_guid=" + data.guid);

	$(".accountModal").find('.notificationsPanel').hide();
	$(".accountModal").find('.manageNotifications')
		.addClass("btn-warning").removeClass("btn-success")
		.text("Benachrichtigungen")
		.unbind("click").click(function () {
			if (! $(".accountModal").find('.notificationsPanel').is(":visible")) {
				$(".accountModal").find('.notificationsPanel').fadeIn();
				$(this).removeClass("btn-warning").addClass("btn-success").text("Speichern");
			} else {
				
			}
		});

	$(".accountModal").modal("show");
	return false;
});

</script>
<?php
include(dirname(__FILE__) . "/footer.php");
?>
