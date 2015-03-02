<?php
	require_once('include/config.inc.php');
	require_once('include/header.php');
?>
<script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyAlG0rZxxwYWKVJIhFU7AodEILbJ0p_nK4&sensor=false"></script>

<div class="container">

<?php
	require_once('include/pageheader.php');
?>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">P2Pool Node Map</h3>
		</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-xs-6">
					<p>Network to display:

						<label for="net0"><input type="radio" name="network" id="net0" value="0" checked onChange="changeNet();"/> SHA256d</label>
						<label for="net1"><input type="radio" name="network" id="net1" value="1" onChange="changeNet();"/> Scrypt</label>
						<label for="net2"><input type="radio" name="network" id="net2" value="2" onChange="changeNet();"/> Myr-Groestl</label>
						<label for="net3"><input type="radio" name="network" id="net3" value="3" onChange="changeNet();"/> Skein</label>
						<label for="net4"><input type="radio" name="network" id="net4" value="4" onChange="changeNet();"/> Qubit</label>
					</p>
				</div>
			</div>
			<div id="googleMap" style="width:1100px; height:500px;" class="center-block"></div>
			<p>&nbsp</p>
			<p id="updated"></p>
		</div>
	</div>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">About The Node Map</h3>
		</div>
		<div class="panel-body">
		<p>
			Twice every hour we map out each of the Myriad P2Pool networks to find what nodes are participating, and which ones are publically available. We
			then take a guess at the geographical location by using the GeoIP service at (<a href="http://freegeoip.net/" target="_blank">freegeoip.net</a>).
		</p>
		<p>
			If you are a node operator and would like to update any of the information (hostname / city / state / latitude &amp; longitude)
			about your node, please <a href="mailto:<?php echo $config['site_email']; ?>">contact us</a> and we will
			sort this out for you. Thanks!
		</p>
		</div>
	</div>
	<script type="text/javascript" src="js/network.js"></script>
<?php



	require_once('include/footer.php');
?>
