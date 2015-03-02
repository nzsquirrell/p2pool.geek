<?php
	require_once('include/config.inc.php');
	require_once('include/header.php');
?>


<div class="container">

<?php
	require_once('include/pageheader.php');
?>

	<div class="row">
		<div class="col-md-6"><!-- start of column -->
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Myriadcoin Network Status</h3>
				</div>
				<div class="panel-body">
					<table class="table table-condensed">
						<tr><td class="name">Last Updated</td><td id="lastupdate"></td></tr>
						<tr><td class="name">Local Wallet Version</td><td id="wallet_version"></td></tr>
						<tr><td class="name">Wallet Peer Count</td><td id="wallet_peercount"></td></tr>
						<tr><td class="name">Current Block</td><td id="net_block"></td></tr>
						<tr><td class="name">Block Value</td><td id="net_value"></td></tr>
						<tr><td class="name">Blocks Till Halving</td><td id="net_blockstillhalf"></td></tr>
						<tr><td class="name">Est. Date of Halving</td><td id="net_halfdate"></td></tr>
						<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
						<tr><td class="name">SHA256 Hashrate</td><td id="sha256_hashrate"></td></tr>
						<tr><td class="name">SHA256 Difficulty</td><td id="sha256_difficulty"></td></tr>
						<tr><td class="name">Scrypt Hashrate</td><td id="scrypt_hashrate"></td></tr>
						<tr><td class="name">Scrypt Difficulty</td><td id="scrypt_difficulty"></td></tr>
						<tr><td class="name">Myr-Groestl Hashrate</td><td id="groestl_hashrate"></td></tr>
						<tr><td class="name">Myr-Groestl Difficulty</td><td id="groestl_difficulty"></td></tr>
						<tr><td class="name">Skein Hashrate</td><td id="skein_hashrate"></td></tr>
						<tr><td class="name">Skein Difficulty</td><td id="skein_difficulty"></td></tr>
						<tr><td class="name">Qubit Hashrate</td><td id="qubit_hashrate"></td></tr>
						<tr><td class="name">Qubit Difficulty</td><td id="qubit_difficulty"></td></tr>
					</table>
					<p><em>Please Note: Hash rates are calculated from current algorithm difficulties</em></p>
				</div>
			</div>

		</div> <!-- end of column -->
		<div class="col-md-6"><!-- start of column -->

			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Wallet Peers</h3>
				</div>
				<div class="panel-body table-responsive">
					<table class="table table-condensed">
						<thead>
							<tr>
								<th>Address</th>
								<th>Direction</th>
								<th>Version</th>
							</tr>
						</thead>
						<tbody id="peers">
						</tbody>
					</table>
				</div>
			</div>


		</div> <!-- end of column -->
	</div> <!-- end of row -->

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Block Statistics</h3>
		</div>
		<div class="panel-body table-responsive">
			<table class="table table-condensed small">
				<thead>
					<tr>
						<th rowspan="2">Period</th>
						<th rowspan="2">Total Blocks</th>
						<th colspan="3">SHA256d</th>
						<th colspan="3">Scrypt</th>
						<th colspan="3">Myr-Groestl</th>
						<th colspan="3">Skein</th>
						<th colspan="3">Qubit</th>
					</tr>
					<tr>
						<th>Blocks</th>
						<th>Avg. Difficulty</th>
						<th>Avg. Hashrate</th>
						<th>Blocks</th>
						<th>Avg. Difficulty</th>
						<th>Avg. Hashrate</th>
						<th>Blocks</th>
						<th>Avg. Difficulty</th>
						<th>Avg. Hashrate</th>
						<th>Blocks</th>
						<th>Avg. Difficulty</th>
						<th>Avg. Hashrate</th>
						<th>Blocks</th>
						<th>Avg. Difficulty</th>
						<th>Avg. Hashrate</th>
					</tr>
				</thead>
				<tbody id="block_stats">
				</tbody>
			</table>
		</div>
	</div>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Recent Blocks</h3>
		</div>
		<div class="panel-body table-responsive">
			<table class="table table-condensed">
				<thead>
					<tr>
						<th>Height</th>
						<th>Time</th>
						<th>Algorithm</th>
						<th>Difficulty</th>
						<th>Interval - Blocks</th>
						<th>Interval - Algorithm</th>
						<th>Hash</th>
					</tr>
				</thead>
				<tbody id="recent_blocks">
				</tbody>
			</table>
		</div>
	</div>

<script type="text/javascript" src="js/myrnet.js"></script>


<?php
	require_once('include/footer.php');
?>