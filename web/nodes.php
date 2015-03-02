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
		<div class="col-md-6">

			<!-- Start of Node 10 - United Kingdom SHA256 -->
			<div class="panel panel-primary" id="panel-node-10">
				<div class="panel-heading">
					<h3 class="panel-title"><span id="node10_name"></span></h3>
				</div>
				<div class="panel-body">
					<table width="100%">
						<tr><td class="name">Address</td><td id="node10_address"></td></tr>
						<tr><td class="name">Algorithm</td><td id="node10_algo"></td></tr>
						<tr><td class="name">Status</td><td id="node10_status"></td></tr>
						<tr><td class="name">Local Time</td><td id="node10_time"></td></tr>
						<tr><td class="name" title="Time since this node was restarted.">Uptime</td><td id="node10_uptime"></td></tr>
						<tr><td class="name" title="Connections to and from other P2Pool nodes.">Peers</td><td id="node10_peers"></td></tr>
						<tr><td class="name" title="A small fee that goes towards the running and development of this node.">Fee</td><td id="node10_fee"></td></tr>
						<tr><td class="name" title="Software version of the P2Pool code.">Version</td><td id="node10_version"></td></tr>
						<tr><td class="name">Efficiency</td><td id="node10_efficiency"></td></tr>
						<tr><td class="name">Active Miners</td><td id="node10_miners"></td></tr>
						<tr><td class="name">Node Hashrate</td><td id="node10_hashrate"></td></tr>
						<tr><td class="name">Non-Stale Rate</td><td id="node10_nonstale"></td></tr>
						<tr><td class="name">Merged Mining</td><td id="node10_merged"></td></tr>
					</table>
				</div>
			</div>
			<!-- End of Node -->

			<!-- Start of Node 16 - United Kingdom Scrypt -->
			<div class="panel panel-primary" id="panel-node-16">
				<div class="panel-heading">
					<h3 class="panel-title"><span id="node16_name"></span></h3>
				</div>
				<div class="panel-body">
					<table width="100%">
						<tr><td class="name">Address</td><td id="node16_address"></td></tr>
						<tr><td class="name">Algorithm</td><td id="node16_algo"></td></tr>
						<tr><td class="name">Status</td><td id="node16_status"></td></tr>
						<tr><td class="name">Local Time</td><td id="node16_time"></td></tr>
						<tr><td class="name" title="Time since this node was restarted.">Uptime</td><td id="node16_uptime"></td></tr>
						<tr><td class="name" title="Connections to and from other P2Pool nodes.">Peers</td><td id="node16_peers"></td></tr>
						<tr><td class="name" title="A small fee that goes towards the running and development of this node.">Fee</td><td id="node16_fee"></td></tr>
						<tr><td class="name" title="Software version of the P2Pool code.">Version</td><td id="node16_version"></td></tr>
						<tr><td class="name">Efficiency</td><td id="node16_efficiency"></td></tr>
						<tr><td class="name">Active Miners</td><td id="node16_miners"></td></tr>
						<tr><td class="name">Node Hashrate</td><td id="node16_hashrate"></td></tr>
						<tr><td class="name">Non-Stale Rate</td><td id="node16_nonstale"></td></tr>
						<tr><td class="name">Merged Mining</td><td id="node16_merged"></td></tr>
					</table>
				</div>
			</div>
			<!-- End of Node -->

			<!-- Start of Node 11 - United Kingdom Groestl -->
			<div class="panel panel-primary" id="panel-node-11">
				<div class="panel-heading">
					<h3 class="panel-title"><span id="node11_name"></span></h3>
				</div>
				<div class="panel-body">
					<table width="100%">
						<tr><td class="name">Address</td><td id="node11_address"></td></tr>
						<tr><td class="name">Algorithm</td><td id="node11_algo"></td></tr>
						<tr><td class="name">Status</td><td id="node11_status"></td></tr>
						<tr><td class="name">Local Time</td><td id="node11_time"></td></tr>
						<tr><td class="name" title="Time since this node was restarted.">Uptime</td><td id="node11_uptime"></td></tr>
						<tr><td class="name" title="Connections to and from other P2Pool nodes.">Peers</td><td id="node11_peers"></td></tr>
						<tr><td class="name" title="A small fee that goes towards the running and development of this node.">Fee</td><td id="node11_fee"></td></tr>
						<tr><td class="name" title="Software version of the P2Pool code.">Version</td><td id="node11_version"></td></tr>
						<tr><td class="name">Efficiency</td><td id="node11_efficiency"></td></tr>
						<tr><td class="name">Active Miners</td><td id="node11_miners"></td></tr>
						<tr><td class="name">Node Hashrate</td><td id="node11_hashrate"></td></tr>
						<tr><td class="name">Non-Stale Rate</td><td id="node11_nonstale"></td></tr>
						<tr><td class="name">Merged Mining</td><td id="node11_merged"></td></tr>
					</table>
				</div>
			</div>
			<!-- End of Node -->

			<!-- Start of Node 20 - United Kingdom Skein -->
			<div class="panel panel-primary" id="panel-node-20">
				<div class="panel-heading">
					<h3 class="panel-title"><span id="node20_name"></span></h3>
				</div>
				<div class="panel-body">
					<table width="100%">
						<tr><td class="name">Address</td><td id="node20_address"></td></tr>
						<tr><td class="name">Algorithm</td><td id="node20_algo"></td></tr>
						<tr><td class="name">Status</td><td id="node20_status"></td></tr>
						<tr><td class="name">Local Time</td><td id="node20_time"></td></tr>
						<tr><td class="name" title="Time since this node was restarted.">Uptime</td><td id="node20_uptime"></td></tr>
						<tr><td class="name" title="Connections to and from other P2Pool nodes.">Peers</td><td id="node20_peers"></td></tr>
						<tr><td class="name" title="A small fee that goes towards the running and development of this node.">Fee</td><td id="node20_fee"></td></tr>
						<tr><td class="name" title="Software version of the P2Pool code.">Version</td><td id="node20_version"></td></tr>
						<tr><td class="name">Efficiency</td><td id="node20_efficiency"></td></tr>
						<tr><td class="name">Active Miners</td><td id="node20_miners"></td></tr>
						<tr><td class="name">Node Hashrate</td><td id="node20_hashrate"></td></tr>
						<tr><td class="name">Non-Stale Rate</td><td id="node20_nonstale"></td></tr>
						<tr><td class="name">Merged Mining</td><td id="node20_merged"></td></tr>
					</table>
				</div>
			</div>
			<!-- End of Node -->

			<!-- Start of Node 2 - United Kingdom Qubit -->
			<div class="panel panel-primary" id="panel-node-2">
				<div class="panel-heading">
					<h3 class="panel-title"><span id="node2_name"></span></h3>
				</div>
				<div class="panel-body">
					<table width="100%">
						<tr><td class="name">Address</td><td id="node2_address"></td></tr>
						<tr><td class="name">Algorithm</td><td id="node2_algo"></td></tr>
						<tr><td class="name">Status</td><td id="node2_status"></td></tr>
						<tr><td class="name">Local Time</td><td id="node2_time"></td></tr>
						<tr><td class="name" title="Time since this node was restarted.">Uptime</td><td id="node2_uptime"></td></tr>
						<tr><td class="name" title="Connections to and from other P2Pool nodes.">Peers</td><td id="node2_peers"></td></tr>
						<tr><td class="name" title="A small fee that goes towards the running and development of this node.">Fee</td><td id="node2_fee"></td></tr>
						<tr><td class="name" title="Software version of the P2Pool code.">Version</td><td id="node2_version"></td></tr>
						<tr><td class="name">Efficiency</td><td id="node2_efficiency"></td></tr>
						<tr><td class="name">Active Miners</td><td id="node2_miners"></td></tr>
						<tr><td class="name">Node Hashrate</td><td id="node2_hashrate"></td></tr>
						<tr><td class="name">Non-Stale Rate</td><td id="node2_nonstale"></td></tr>
						<tr><td class="name">Merged Mining</td><td id="node2_merged"></td></tr>
					</table>
				</div>
			</div>
			<!-- End of Node -->

		</div>
		<div class="col-md-6">

			<!-- Start of Node 14 - US East SHA256 -->
			<div class="panel panel-primary" id="panel-node-14">
				<div class="panel-heading">
					<h3 class="panel-title"><span id="node14_name"></span></h3>
				</div>
				<div class="panel-body">
					<table width="100%">
						<tr><td class="name">Address</td><td id="node14_address"></td></tr>
						<tr><td class="name">Algorithm</td><td id="node14_algo"></td></tr>
						<tr><td class="name">Status</td><td id="node14_status"></td></tr>
						<tr><td class="name">Local Time</td><td id="node14_time"></td></tr>
						<tr><td class="name" title="Time since this node was restarted.">Uptime</td><td id="node14_uptime"></td></tr>
						<tr><td class="name" title="Connections to and from other P2Pool nodes.">Peers</td><td id="node14_peers"></td></tr>
						<tr><td class="name" title="A small fee that goes towards the running and development of this node.">Fee</td><td id="node14_fee"></td></tr>
						<tr><td class="name" title="Software version of the P2Pool code.">Version</td><td id="node14_version"></td></tr>
						<tr><td class="name">Efficiency</td><td id="node14_efficiency"></td></tr>
						<tr><td class="name">Active Miners</td><td id="node14_miners"></td></tr>
						<tr><td class="name">Node Hashrate</td><td id="node14_hashrate"></td></tr>
						<tr><td class="name">Non-Stale Rate</td><td id="node14_nonstale"></td></tr>
						<tr><td class="name">Merged Mining</td><td id="node14_merged"></td></tr>
					</table>
				</div>
			</div>
			<!-- End of Node -->

			<!-- Start of Node 15 - US East Scrypt -->
			<div class="panel panel-primary" id="panel-node-15">
				<div class="panel-heading">
					<h3 class="panel-title"><span id="node15_name"></span></h3>
				</div>
				<div class="panel-body">
					<table width="100%">
						<tr><td class="name">Address</td><td id="node15_address"></td></tr>
						<tr><td class="name">Algorithm</td><td id="node15_algo"></td></tr>
						<tr><td class="name">Status</td><td id="node15_status"></td></tr>
						<tr><td class="name">Local Time</td><td id="node15_time"></td></tr>
						<tr><td class="name" title="Time since this node was restarted.">Uptime</td><td id="node15_uptime"></td></tr>
						<tr><td class="name" title="Connections to and from other P2Pool nodes.">Peers</td><td id="node15_peers"></td></tr>
						<tr><td class="name" title="A small fee that goes towards the running and development of this node.">Fee</td><td id="node15_fee"></td></tr>
						<tr><td class="name" title="Software version of the P2Pool code.">Version</td><td id="node15_version"></td></tr>
						<tr><td class="name">Efficiency</td><td id="node15_efficiency"></td></tr>
						<tr><td class="name">Active Miners</td><td id="node15_miners"></td></tr>
						<tr><td class="name">Node Hashrate</td><td id="node15_hashrate"></td></tr>
						<tr><td class="name">Non-Stale Rate</td><td id="node15_nonstale"></td></tr>
						<tr><td class="name">Merged Mining</td><td id="node15_merged"></td></tr>
					</table>
				</div>
			</div>
			<!-- End of Node -->

			<!-- Start of Node 5 - US East Groestl -->
			<div class="panel panel-primary" id="panel-node-5">
				<div class="panel-heading">
					<h3 class="panel-title"><span id="node5_name"></span></h3>
				</div>
				<div class="panel-body">
					<table width="100%">
						<tr><td class="name">Address</td><td id="node5_address"></td></tr>
						<tr><td class="name">Algorithm</td><td id="node5_algo"></td></tr>
						<tr><td class="name">Status</td><td id="node5_status"></td></tr>
						<tr><td class="name">Local Time</td><td id="node5_time"></td></tr>
						<tr><td class="name" title="Time since this node was restarted.">Uptime</td><td id="node5_uptime"></td></tr>
						<tr><td class="name" title="Connections to and from other P2Pool nodes.">Peers</td><td id="node5_peers"></td></tr>
						<tr><td class="name" title="A small fee that goes towards the running and development of this node.">Fee</td><td id="node5_fee"></td></tr>
						<tr><td class="name" title="Software version of the P2Pool code.">Version</td><td id="node5_version"></td></tr>
						<tr><td class="name">Efficiency</td><td id="node5_efficiency"></td></tr>
						<tr><td class="name">Active Miners</td><td id="node5_miners"></td></tr>
						<tr><td class="name">Node Hashrate</td><td id="node5_hashrate"></td></tr>
						<tr><td class="name">Non-Stale Rate</td><td id="node5_nonstale"></td></tr>
						<tr><td class="name">Merged Mining</td><td id="node5_merged"></td></tr>
					</table>
				</div>
			</div>
			<!-- End of Node -->

			<!-- Start of Node 6 - US East Skein -->
			<div class="panel panel-primary" id="panel-node-6">
				<div class="panel-heading">
					<h3 class="panel-title"><span id="node6_name"></span></h3>
				</div>
				<div class="panel-body">
					<table width="100%">
						<tr><td class="name">Address</td><td id="node6_address"></td></tr>
						<tr><td class="name">Algorithm</td><td id="node6_algo"></td></tr>
						<tr><td class="name">Status</td><td id="node6_status"></td></tr>
						<tr><td class="name">Local Time</td><td id="node6_time"></td></tr>
						<tr><td class="name" title="Time since this node was restarted.">Uptime</td><td id="node6_uptime"></td></tr>
						<tr><td class="name" title="Connections to and from other P2Pool nodes.">Peers</td><td id="node6_peers"></td></tr>
						<tr><td class="name" title="A small fee that goes towards the running and development of this node.">Fee</td><td id="node6_fee"></td></tr>
						<tr><td class="name" title="Software version of the P2Pool code.">Version</td><td id="node6_version"></td></tr>
						<tr><td class="name">Efficiency</td><td id="node6_efficiency"></td></tr>
						<tr><td class="name">Active Miners</td><td id="node6_miners"></td></tr>
						<tr><td class="name">Node Hashrate</td><td id="node6_hashrate"></td></tr>
						<tr><td class="name">Non-Stale Rate</td><td id="node6_nonstale"></td></tr>
						<tr><td class="name">Merged Mining</td><td id="node6_merged"></td></tr>
					</table>
				</div>
			</div>
			<!-- End of Node -->

			<!-- Start of Node 7 - US East Qubit -->
			<div class="panel panel-primary" id="panel-node-7">
				<div class="panel-heading">
					<h3 class="panel-title"><span id="node7_name"></span></h3>
				</div>
				<div class="panel-body">
					<table width="100%">
						<tr><td class="name">Address</td><td id="node7_address"></td></tr>
						<tr><td class="name">Algorithm</td><td id="node7_algo"></td></tr>
						<tr><td class="name">Status</td><td id="node7_status"></td></tr>
						<tr><td class="name">Local Time</td><td id="node7_time"></td></tr>
						<tr><td class="name" title="Time since this node was restarted.">Uptime</td><td id="node7_uptime"></td></tr>
						<tr><td class="name" title="Connections to and from other P2Pool nodes.">Peers</td><td id="node7_peers"></td></tr>
						<tr><td class="name" title="A small fee that goes towards the running and development of this node.">Fee</td><td id="node7_fee"></td></tr>
						<tr><td class="name" title="Software version of the P2Pool code.">Version</td><td id="node7_version"></td></tr>
						<tr><td class="name">Efficiency</td><td id="node7_efficiency"></td></tr>
						<tr><td class="name">Active Miners</td><td id="node7_miners"></td></tr>
						<tr><td class="name">Node Hashrate</td><td id="node7_hashrate"></td></tr>
						<tr><td class="name">Non-Stale Rate</td><td id="node7_nonstale"></td></tr>
						<tr><td class="name">Merged Mining</td><td id="node7_merged"></td></tr>
					</table>
				</div>
			</div>
			<!-- End of Node -->


		</div>  <!-- end of column two -->
	</div>



<script type="text/javascript" src="js/nodes.js"></script>


<?php
	require_once('include/footer.php');
?>