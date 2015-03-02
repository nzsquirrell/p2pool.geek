<?php
	require_once('include/config.inc.php');
	require_once('include/database.inc.php');
	require_once('include/header.php');
?>


<div class="container">

<?php
	require_once('include/pageheader.php');
?>

	<div class="panel panel-info">
		<div class="panel-body">
			<table class="table table-condensed">
				<tr>
					<td>Algorithm</td>
					<td>
						<div class="row">
							<div class="col-xs-4">
								<select class="form-control" name="algorithm" id="algorithm" onChange="changeAlgo();">
									<option value='0'>SHA256d</option>
									<option value='1'>Scrypt</option>
									<option value='2'>Myr-Groestl</option>
									<option value='3'>Skein</option>
									<option value='4'>Qubit</option>
								</select>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td id="algo">SHA256d Hashrate</td>
					<td>
						<div class="row">
							<div class="col-xs-2">
								<input class="form-control" name="hashrate" id="hashrate" value="3000">
							</div>
							<div class="col-xs-2">
								<select class="form-control" name="hashunit" id="hashunit" onChange="changeHashUnit();">
									<option value="1">kH/s</option>
									<option value="1000">MH/s</option>
									<option value="1000000">GH/s</option>
									<option value="1000000000">TH/s</option>
								</select>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td>Power Usage</td>
					<td>
						<div class="row">
							<div class="col-xs-2">
								<input class="form-control" name="power" id="power" value="250">
							</div>
							<div class="col-xs-2">
								<select class="form-control" name="unit" id="unit" onChange="changeUnit();">
									<option value="1000">Watt</option>
									<option value="1">Kilowatt</option>
								</select>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td>Power Cost (per kWhr)</td>
					<td>
						<div class="row">
							<div class="col-xs-2">
								<input class="form-control" name="powerprice" id="powerprice" value="0.10">
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td>Currency</td>
					<td>
						<div class="row">
							<div class="col-xs-4">
								<select class="form-control" name="currency" id="currency">
									<option value='USD'>USD - United States Dollar</option>
									<option value='GBP'>GBP - Pound Sterling</option>
									<option value='EUR'>EUR - Euro</option>
									<option value='NZD'>NZD - New Zealand Dollar</option>
								</select>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center">
						<button type="button" class="btn btn-info" onClick="calculate();">Calculate</button>
					</td>
				</tr>
			</table>
			<p></p>
			<div id="rates"></div>
			<table class="table table-condensed table-striped small">
				<thead>
					<tr>
						<th>Currency</th>
						<th>Earnings Per Day</th>
						<th>Earnings Per Week</th>
						<th>Earnings Per Month</th>
						<th>Earnings Per Year</th>
						<th>Solo Mining Block Time</th>
					</tr>
				</thead>
				<tbody id="earnings">
				</tbody>
			</table>
		</div>
	</div>

	<script type="text/javascript" src="js/calculator.js"></script>
<?php



	require_once('include/footer.php');
?>
