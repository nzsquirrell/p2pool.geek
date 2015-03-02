<?php
	require_once('include/config.inc.php');
	require_once('include/database.inc.php');

	$dblink = dbinit();

	$valid = FALSE;
	if(isset($_GET['address']))
	{
		$address = $dblink->real_escape_string($_GET['address']);
		$query = "SELECT address_vtc FROM tblminers WHERE address_vtc='$address'";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		if($result->num_rows>0)
		{
			$valid = TRUE;
		}
	}
	if(isset($_GET['network']))
	{
		$network = $dblink->real_escape_string($_GET['network']);

	}
	else
	{
		$query = "SELECT network FROM tblnodeminers WHERE address='$address' ORDER BY lastseen LIMIT 0,1";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();
		$network = $row[0];
	}

	if(!$valid)
	{
		$header = 'Location: http://' . $_SERVER['HTTP_HOST'] . '/miners';
		header($header);
		exit;
	}

	require_once('include/header.php');
?>


<div class="container">

<?php
	require_once('include/pageheader.php');
?>

	<div id="alerts"></div>

	<span id="network" class="hidden"><?php echo $network; ?></span>

	<div class="row">
		<div class="col-md-6">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Miner Information</h3>
				</div>
				<div class="panel-body table-responsive">
					<table class="table table-condensed table-hover">
						<tr><td class="name">MYR Address</td><td id="address"><?php echo $address; ?></td></tr>
						<tr><td class="name">Node</td><td id="node"></td></tr>
						<tr><td class="name">Algorithm</td><td id="algorithm"></td></tr>
						<tr><td class="name">Hashrate</td><td id="hashrate"></td></tr>
						<tr><td class="name">Non-Stale Rate</td><td id="nonstalerate"></td></tr>
					</table>
				</div>
			</div>

			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Estimated Earnings</h3>
				</div>
				<div class="panel-body table-responsive">
					<table class="table table-condensed table-hover">
						<tbody id="earnings">
						</tbody>
					</table>
				</div>
			</div>

			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Merge Mining Earnings to date</h3>
				</div>
				<div class="panel-body table-responsive">
					<table class="table table-condensed table-hover">
						<thead>
							<tr>
								<th>Coin</th>
								<th>Payments</th>
								<th>Fees</th>
								<th>Donations</th>
								<th>Payouts</th>
							</tr>
						</thead>
						<tbody id="earnings2date">
						</tbody>
					</table>
				</div>
			</div>

		</div>

		<div class="col-md-6">

			<div class="panel panel-primary" id="mergedaccounts">
				<div class="panel-heading">
					<h3 class="panel-title">Merged Coin Account Settings</h3>
				</div>
				<div class="panel-body table-responsive">
					<p>To change any of the account settings below, please click <button class="btn btn-primary btn-xs" onClick="window.location='/settings/<?php echo $network; ?>/<?php echo $address; ?>';">Change Account Settings</button></p>
					<table class="table table-condensed table-hover">
						<tbody id="merged">
						</tbody>
					</table>
					<p>&nbsp;</p>
					<p><em>Note: If a merged coin payout address is empty, all earnings for that coin will be donated to p2pool.geek.nz.</em></p>
				</div>
			</div>

		</div>
	</div>

	<div class="row">
		<div class="col-md-7">
			<div class="panel panel-primary" id="mergedtx">
				<div class="panel-heading">
					<h3 class="panel-title">Transactions</h3>
				</div>
				<div class="panel-body table-responsive">
					<table class="table table-condensed table-hover">
						<thead>
							<tr>
								<th>Date</th>
								<th>Coin</th>
								<th>Type</th>
								<th>Amount</th>
								<th>Block</th>
							</tr>
						</thead>
						<tbody id="transactions">
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<div class="col-md-5">
			<div class="panel panel-primary" id="mergedhistory">
				<div class="panel-heading">
					<h3 class="panel-title">Account History</h3>
				</div>
				<div class="panel-body table-responsive">
					<table class="table table-condensed table-hover">
						<thead>
							<tr>
								<th>Date</th>
								<th>Event</th>
							</tr>
						</thead>
						<tbody id="history">
						</tbody>
					</table>
				</div>
			</div>
		</div>

	</div>

<script type="text/javascript" src="/js/minerdetail.js"></script>


<?php
	require_once('include/footer.php');
?>