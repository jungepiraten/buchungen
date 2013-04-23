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
<li><input type="checkbox" id="account-<?php print($account["guid"]) ?>" checked="checked" /><label for="account-<?php print($account["guid"]) ?>"><i class="icon-<?php print($account["placeholder"] == 0 ? "briefcase" : "book") ?>"></i> <?php print($account["code"]) ?> <?php print($account["label"]) ?></label><?php if(!empty($account["childs"])) { ?><ul><?php outputAccountHier($hier, $account["guid"]) ?></ul><?php } ?></li>
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
<?php
include(dirname(__FILE__) . "/footer.php");
?>