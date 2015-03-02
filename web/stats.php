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
			<h3 class="panel-title">Previous 45 Blocks</h3>
		</div>
		<div class="panel-body table-responsive">
			<table class="table table-condensed">
				<thead>
					<tr>
						<th>Coin</th>
						<th>Algorithm</th>
						<th>When</th>
						<th>Date &amp; Time</th>
						<th>Height</th>
						<th>Confirmations</th>
						<th title="Estimated time for a block to be found is calculated from the average pool hashrate and difficulty over the period between this and the previous block.">Estimated Time</th>
						<th>Actual Time</th>
						<th>Luck</th>
					</tr>
				</thead>
				<tbody id="recent_blocks">
				</tbody>
			</table>
		</div>
	</div>

	<div class="panel panel-primary hidden-xs">
		<div class="panel-heading">
			<h3 class="panel-title">Block History - Last 25 Blocks</h3>
		</div>
		<div class="panel-body">
			<p>Network to display:
				<label for="history_display_sha256"><input type="radio" name="history_display" id="history_display_sha256" value="0" checked onChange="blockChart();"/> SHA256d</label>
				<label for="history_display_scrypt"><input type="radio" name="history_display" id="history_display_scrypt" value="1" onChange="blockChart();"/> Scrypt</label>
				<label for="history_display_groestl"><input type="radio" name="history_display" id="history_display_groestl" value="2" onChange="blockChart();"/> Myr-Groestl</label>
				<label for="history_display_skein"><input type="radio" name="history_display" id="history_display_skein" value="3" onChange="blockChart();"/> Skein</label>
				<label for="history_display_qubit"><input type="radio" name="history_display" id="history_display_qubit" value="4" onChange="blockChart();"/> Qubit</label>
			</p>

			<div id="blockchart" style="width: 900px; height:300px;" class="center-block"></div>
		</div>
	</div>

	<div class="panel panel-primary hidden-xs">
		<div class="panel-heading">
			<h3 class="panel-title">Pool Statistics</h3>
		</div>
		<div class="panel-body table-responsive">
			<table class="table table-condensed table-striped">
				<thead>
					<tr>
						<th>Period</th>
						<th>Blocks Estimated</th>
						<th>Blocks Found</th>
						<th>Average Difficulty</th>
						<th>Average Luck</th>
					</tr>
				</thead>
				<tbody id="block_stats">
				</tbody>
			</table>
		</div>
	</div>




<script type="text/javascript" src="js/stats.js"></script>


<?php
	require_once('include/footer.php');
?>