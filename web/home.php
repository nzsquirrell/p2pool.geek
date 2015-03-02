<?php
	require_once('include/config.inc.php');
	require_once('include/header.php');
?>


<div class="container">
<?php
	require_once('include/pageheader.php');
?>

	<div id="alerts"></div>

	<div class="row">
		<div class="col-md-6"><!-- start of column -->

			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">SHA256 P2Pool Network</h3>
				</div>
				<div class="panel-body">
					<table width="100%">
						<tr><td class="name">Total Hashrate</td><td id="pool4_hashrate"></td></tr>
						<tr><td class="name">Non-Stale Hashrate</td><td id="pool4_nonstale"></td></tr>
						<tr><td class="name" title="Shares are found every 15 seconds - the difficulty ensures this time.">Minimum Share Difficulty</td><td id="pool4_sharediff"></td></tr>
						<tr><td class="name">Time since last block</td><td id="pool4_timesince"></td></tr>
						<tr><td class="name" title="Calculated from current pool hashrate and network difficulty.">Est. Time to block</td><td id="pool4_timetoblock"></td></tr>
						<tr><td class="name" title="Assuming the above time per block, this is the number of blocks per 24 hours.">Est. Blocks / Day</td><td id="pool4_blocksperday"></td></tr>
					</table>
				</div>
			</div>

			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Scrypt P2Pool Network</h3>
				</div>
				<div class="panel-body">
					<table width="100%">
						<tr><td class="name">Total Hashrate</td><td id="pool5_hashrate"></td></tr>
						<tr><td class="name">Non-Stale Hashrate</td><td id="pool5_nonstale"></td></tr>
						<tr><td class="name" title="Shares are found every 15 seconds - the difficulty ensures this time.">Minimum Share Difficulty</td><td id="pool5_sharediff"></td></tr>
						<tr><td class="name">Time since last block</td><td id="pool5_timesince"></td></tr>
						<tr><td class="name" title="Calculated from current pool hashrate and network difficulty.">Est. Time to block</td><td id="pool5_timetoblock"></td></tr>
						<tr><td class="name" title="Assuming the above time per block, this is the number of blocks per 24 hours.">Est. Blocks / Day</td><td id="pool5_blocksperday"></td></tr>
					</table>
				</div>
			</div>

			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Myr-Groestl P2Pool Network</h3>
				</div>
				<div class="panel-body">
					<table width="100%">
						<tr><td class="name">Total Hashrate</td><td id="pool1_hashrate"></td></tr>
						<tr><td class="name">Non-Stale Hashrate</td><td id="pool1_nonstale"></td></tr>
						<tr><td class="name" title="Shares are found every 15 seconds - the difficulty ensures this time.">Minimum Share Difficulty</td><td id="pool1_sharediff"></td></tr>
						<tr><td class="name">Time since last block</td><td id="pool1_timesince"></td></tr>
						<tr><td class="name" title="Calculated from current pool hashrate and network difficulty.">Est. Time to block</td><td id="pool1_timetoblock"></td></tr>
						<tr><td class="name" title="Assuming the above time per block, this is the number of blocks per 24 hours.">Est. Blocks / Day</td><td id="pool1_blocksperday"></td></tr>
					</table>
				</div>
			</div>

			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Skein P2Pool Network</h3>
				</div>
				<div class="panel-body">
					<table width="100%">
						<tr><td class="name">Total Hashrate</td><td id="pool2_hashrate"></td></tr>
						<tr><td class="name">Non-Stale Hashrate</td><td id="pool2_nonstale"></td></tr>
						<tr><td class="name" title="Shares are found every 15 seconds - the difficulty ensures this time.">Minimum Share Difficulty</td><td id="pool2_sharediff"></td></tr>
						<tr><td class="name">Time since last block</td><td id="pool2_timesince"></td></tr>
						<tr><td class="name" title="Calculated from current pool hashrate and network difficulty.">Est. Time to block</td><td id="pool2_timetoblock"></td></tr>
						<tr><td class="name" title="Assuming the above time per block, this is the number of blocks per 24 hours.">Est. Blocks / Day</td><td id="pool2_blocksperday"></td></tr>
					</table>
				</div>
			</div>

			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Qubit P2Pool Network</h3>
				</div>
				<div class="panel-body">
					<table width="100%">
						<tr><td class="name">Total Hashrate</td><td id="pool3_hashrate"></td></tr>
						<tr><td class="name">Non-Stale Hashrate</td><td id="pool3_nonstale"></td></tr>
						<tr><td class="name" title="Shares are found every 15 seconds - the difficulty ensures this time.">Minimum Share Difficulty</td><td id="pool3_sharediff"></td></tr>
						<tr><td class="name">Time since last block</td><td id="pool3_timesince"></td></tr>
						<tr><td class="name" title="Calculated from current pool hashrate and network difficulty.">Est. Time to block</td><td id="pool3_timetoblock"></td></tr>
						<tr><td class="name" title="Assuming the above time per block, this is the number of blocks per 24 hours.">Est. Blocks / Day</td><td id="pool3_blocksperday"></td></tr>
					</table>
				</div>
			</div>

			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Myriadcoin Network</h3>
				</div>
				<div class="panel-body">
					<table width="100%">
						<tr><td class="name">Current Block</td><td id="net_block"></td></tr>
						<tr><td class="name">Block Value</td><td id="net_value"></td></tr>
						<tr><td class="name">Blocks Till Halving</td><td id="net_blockstillhalf"></td></tr>
						<tr><td class="name">Est. Date of Halving</td><td id="net_halfdate"></td></tr>
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
				</div>
			</div>

		</div> <!-- end of column -->
		<div class="col-md-6"> <!-- start of column -->

			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Connection Info</h3>
				</div>
				<div class="panel-body table-responsive">
					<p>To start mining configure your miner to connect to your closest node</p>
					<table class="table table-condensed">
						<thead>
							<tr><th>Location</th><th>Algorithm</th><th>Node</th></tr>
						</thead>
						<tbody>
							<tr><td>United Kingdom</td><td>SHA256d</td><td>stratum+tcp://uk.p2pool.geek.nz:5578</td></tr>
							<tr><td>United Kingdom</td><td>Scrypt</td><td>stratum+tcp://uk.p2pool.geek.nz:5556</td></tr>
							<tr><td>United Kingdom</td><td>Myr-Groestl</td><td>stratum+tcp://uk.p2pool.geek.nz:3333</td></tr>
							<tr><td>United Kingdom</td><td>Skein</td><td>stratum+tcp://uk.p2pool.geek.nz:5589</td></tr>
							<tr><td>United Kingdom</td><td>Qubit</td><td>stratum+tcp://uk.p2pool.geek.nz:5567</td></tr>
							<tr><td>USA East Coast</td><td>SHA256d</td><td>stratum+tcp://birdspool.no-ip.org:5578</td></tr>
							<tr><td>USA East Coast</td><td>Scrypt</td><td>stratum+tcp://birdspool.no-ip.org:5556</td></tr>
							<tr><td>USA East Coast</td><td>Myr-Groestl</td><td>stratum+tcp://birdspool.no-ip.org:3333</td></tr>
							<tr><td>USA East Coast</td><td>Skein</td><td>stratum+tcp://birdspool.no-ip.org:5589</td></tr>
							<tr><td>USA East Coast</td><td>Qubit</td><td>stratum+tcp://birdspool.no-ip.org:5567</td></tr>
						<tbody>
					</table>
					<p>&nbsp;</p>
					<p>Username: Your MYR Address</p>
					<p>Password: x</p>
<!--					<p><strong>Registration:</strong> To ensure you receive your MON &amp; PLX payments, please <a href="/register">Register with your VTC, MON &amp; PLX addresses.</a>
					Failure to do so will result in you donating your share of MON and PLX.</p>
					<p>Fees are between 0 and 0.5% for VTC depending on the node, the mining fees for MON &amp; PLX are 0%, with no transaction fees.
					MON & PLX payments are made once the blocks have matured (120 confirmations for MON, 320 confirmations for PLX).</p>
					<p>Your MON and PLX payouts are proportional to your valid P2Pool shares earned in the previous 24 hours.
					If you cannot see your MON or PLX addresses please contact us.</p>-->
					<p>Contact us via <a href="mailto:<?php echo $config['site_email']; ?>"><?php echo $config['site_email']; ?></a>
					or <a href="http://www.reddit.com/message/compose/?to=" target="_blank">via Reddit</a>.</p>
				</div>
			</div>

			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Exchange Rates</h3>
				</div>
				<div class="panel-body">
					<table class="table table-condensed table-hover">
						<thead>
							<tr>
								<th>Exchange</th>
								<th>Value</th>
								<th>Source</th>
								<th>24 Hour Change</th>
							</tr>
						</thead>
						<tbody id="xrates">
						</tbody>
					</table>
				</div>
			</div>


		</div> <!-- end of column -->

	</div>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Merged Coins</h3>
		</div>
		<div class="panel-body table-responsive">
			<table class="table table-condensed table-hover">
			<thead>
				<tr>
					<th>Symbol</th>
					<th>Name</th>
					<th>Algorithm</th>
					<th>Difficulty</th>
					<th>Network Hashrate</th>
					<th>Est. Time to Block</th>
					<th>Current Block</th>
					<th>Block Value</th>
				</tr>
			</thead>
			<tbody id="mergedcoins">
			</tbody>
			</table>
		</div>
	</div>

<script type="text/javascript" src="js/home.js"></script>


<?php
	require_once('include/footer.php');
?>