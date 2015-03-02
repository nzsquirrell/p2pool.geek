<?php
	require_once('include/config.inc.php');
	require_once('include/header.php');
?>


<div class="container">

<?php
	require_once('include/pageheader.php');
?>

	<div id="alerts"></div>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Miners</h3>
		</div>
		<div class="panel-body table-responsive">
			<form class="form-inline">
<!--			Address &amp; Payout Display:
				<label for="payout_display_drk" class="radio-inline">
					<input type="radio" name="payout_display" id="payout_display_drk" value="drk" checked onChange="$(document).trigger('update_miners');" /> Darkcoin</label>
				<label for="payout_display_udrk" class="radio-inline">
					<input type="radio" name="payout_display" id="payout_display_udrk" value="udrk" onChange="$(document).trigger('update_miners');" /> Umbrella-DRK</label>-->
				<label for="node_display" class="radio-inline"> Display miners on node:
					<select name="node_display" id="node_display" class="form-control" onChange="$(document).trigger('update_miners');" >
						<option value="0">All</option>
					</select>
				</label>
			</form>
			<p>&nbsp;</p>
			<table class="table table-condensed table-hover">
				<thead>
					<tr>
						<th id="header_address">MYR Address</th>
						<th>Hash Rate</th>
						<th>Stale</th>
						<th>Node</th>
						<th>Algorithm</th>
						<th>Expected Block Payouts</th>
						<th>Earnings Per Day</th>
						<th>&nbsp;</th>
					</tr>
				</thead>
				<tbody id="active_miners">
				</tbody>
			</table>
			<p>&nbsp;</p>
			<!--<p>Are your MON or PLX addresses not showing? <a href="http://www.reddit.com/message/compose/?to=nzsquirrell" target="_blank">Register Here</a></p>-->
			<!--<p><span class="glyphicon glyphicon-star-empty"></span> Denotes this miner is donating some of their MON and/or PLX to the vert.geek.nz network. Thanks!</p>-->
		</div>
	</div>









<script type="text/javascript" src="js/miners.js"></script>


<?php
	require_once('include/footer.php');
?>