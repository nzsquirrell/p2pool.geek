<?php
	require_once('../include/config.inc.php');
	require_once('../include/database.inc.php');
	require_once('../include/functions.inc.php');
	require_once('../include/header.php');

?>
<div class="container">

<?php
	require_once('../include/pageheader.php');
?>

	<ul class="nav nav-tabs">
		<li><a href="/home">Pool</a></li>
		<li><a href="/vcp">Control Panel Home</a></li>
		<li><a href="/vcp/jobs.php">Job Management</a></li>
		<li><a href="/vcp/alerts.php">Alerts</a></li>
		<li><a href="/vcp/miners.php">Miner Management</a></li>
		<li><a href="/vcp/blocks.php">Recent Blocks</a></li>
		<li class="active"><a href="/vcp/dashboard.php">Dashboard</a></li>
	</ul>

	<p></p>
	<p>System Time: <span id="systemtime"></span></p>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Merged Mining Nodes</h3>
		</div>
		<div class="panel-body">
			<table class="table table-condensed table-striped small">
				<thead>
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>Algorithm</th>
						<th>Status</th>
						<th>Last Update</th>
						<th>Miners</th>
						<th>Non Stale Rate</th>
						<th>RPC</th>
					</tr>
				</thead>
				<tbody id="mergednodes">
				</tbody>
			</table>
		</div>
	</div>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Merged Coins</h3>
		</div>
		<div class="panel-body">
			<table class="table table-condensed table-striped small">
				<thead>
					<tr>
						<th>Symbol</th>
						<th>Name</th>
						<th>Algorithm</th>
						<th>Payout State</th>
						<th>Last Block</th>
						<th>Value</th>
						<th>Blocks Today</th>
						<th>Value</th>
						<th>Payments</th>
						<th>Donations</th>
						<th>Payouts</th>
					</tr>
				</thead>
				<tbody id="mergedcoins">
				</tbody>
			</table>
		</div>
	</div>

	<div class="row">
		<div class="col-md-6">

			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Recent Payments</h3>
				</div>
				<div class="panel-body">
					<table class="table table-condensed table-striped small">
						<thead>
							<tr>
								<th>Coin</th>
								<th>Miner</th>
								<th>Amount</th>
								<th>Time</th>
							</tr>
						</thead>
						<tbody id="payments">
						</tbody>
					</table>
				</div>
			</div>

		</div>
		<div class="col-md-6">

			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Payouts</h3>
				</div>
				<div class="panel-body">
					<table class="table table-condensed table-striped small">
						<thead>
							<tr>
								<th>Coin</th>
								<th>Miner</th>
								<th>Amount</th>
								<th>Time</th>
							</tr>
						</thead>
						<tbody id="payouts">
						</tbody>
					</table>
				</div>
			</div>

		</div>
	</div>

<script type="text/javascript" src="dashboard.js"></script>

<?php

	require_once('../include/footer.php');
?>
