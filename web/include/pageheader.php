	<div class="page-header no-border">
		<div class="row">
			<div class="col-md-2">
				<img src="/images/myriad_logo.png" width="100" height="100" />
			</div>
			<div class="col-md-10">
				 <h3><?php echo $config['site_header']; ?></h3>
				 <h5>Myriadcoin P2Pool Merged Mining</h5>
			</div>
		</div>
	</div>

<?php

	$path = pathinfo($_SERVER['SCRIPT_FILENAME'],PATHINFO_FILENAME);

	print '<ul class="nav nav-tabs">';
	$class = $path=='home' ? 'active' : '';
	print "<li class=\"$class\"><a href=\"/home\">Pool</a></li>";
	$class = $path=='nodes' ? 'active' : '';
	print "<li class=\"$class\"><a href=\"/nodes\">Nodes</a></li>";
	$class = ($path=='miners' || $path=='minerdetail' || $path=='settings') ? 'active' : '';
	print "<li class=\"$class\"><a href=\"/miners\">Miners</a></li>";
	$class = $path=='stats' ? 'active' : '';
	print "<li class=\"$class\"><a href=\"/stats\">Statistics</a></li>";
	$class = $path=='donations' ? 'active' : '';
	print "<li class=\"$class\"><a href=\"/donations\">Donations</a></li>";
	$class = $path=='blockchain' ? 'active' : '';
	print "<li class=\"$class\"><a href=\"/blockchain\">Blockchain</a></li>";
	$class = $path=='calculator' ? 'active' : '';
	print "<li class=\"$class\"><a href=\"/calculator\" class=\"hidden-xs\">Mining Calculator</a></li>";
	$class = $path=='weekly' ? 'active' : '';
	print "<li class=\"$class\"><a href=\"/weekly\">Weekly Report</a></li>";
	$class = $path=='merged' ? 'active' : '';
	print "<li class=\"$class\"><a href=\"/merge\">Merged Mining</a></li>";
	$class = $path=='help' ? 'active' : '';
	print "<li class=\"$class\"><a href=\"/help\">Getting Started</a></li>";

	print '</ul><p></p>';

?>