<?php
	require_once('../include/config.inc.php');
	require_once('../include/database.inc.php');
	require_once('../include/functions.inc.php');
	require_once('../include/header.php');

	$dblink = dbinit();

	log_adminaction($dblink,'VCPHome','Accessed VCP Homepage.');
?>
<div class="container">

<?php
	require_once('../include/pageheader.php');
	
	$algos[0] = 'SHA256';
	$algos[1] = 'Scrypt';
	$algos[2] = 'Myr-Groestl';
	$algos[3] = 'Skein';
	$algos[4] = 'Qubit';
	
?>

	<ul class="nav nav-tabs">
		<li><a href="/home">Pool</a></li>
		<li class="active"><a href="/vcp">Control Panel Home</a></li>
		<li><a href="/vcp/jobs.php">Job Management</a></li>
		<li><a href="/vcp/alerts.php">Alerts</a></li>
		<li><a href="/vcp/miners.php">Miner Management</a></li>
		<li><a href="/vcp/blocks.php">Recent Blocks</a></li>
		<li><a href="/vcp/dashboard.php">Dashboard</a></li>
	</ul>

	<p></p>
	<p>System Time: <?php echo gmdate('M d H:i:s e'); ?></p>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">My Nodes</h3>
		</div>
		<div class="panel-body">
			<table class="table table-condensed table-striped">
				<thead>
					<tr>
						<th>Node</th>
						<th>Algorithm</th>
						<th>Fee</th>
						<th>Uptime</th>
						<th>Miners</th>
						<th>Hashrate</th>
						<th>Non-Stale Hashrate</th>
						<th>Prop. of Network</th>
					</tr>
				</thead>
				<tbody>
<?php

	$globalstats = array();
	$payouts = array();

	$query = "SELECT node,stat,data FROM tblp2poolinfo WHERE stat='global_stats' OR stat='current_payouts'";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		switch ($row[0])
		{
			case 1:
				$network = 4;
				break;
			case 3:
				$network = 3;
				break;
			case 4:
				$network = 2;
				break;
			case 12:
				$network = 0;
				break;
			case 13:
				$network = 1;
				break;
		}
		
		if($row[1]=='global_stats')
		{
			$globalstats[$network] = json_decode($row[2]);
		}
		else
		{
			$payouts[$network] = json_decode($row[2]);
		}
	}

	$query = "SELECT * FROM tblnodes WHERE id=1 OR id=3 OR id=4 OR id=12 OR id=13 OR id=8 ORDER BY network,abbr";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		print '<tr>';
		print "<td>$row[2]</td>";
		print '<td>' . $algos["$row[21]"] . '</td>';
		print "<td>$row[13]%</td>";
		print '<td>' . seconds2time($row[10]) . '</td>';
		print "<td>$row[6]</td>";
		print '<td>' . scale_rate($row[7]) . '</td>';
		print '<td>' . scale_rate($row[9]) . '</td>';
		print '<td>' . sprintf("%02.01f%%",round($row[9]/$globalstats["$row[21]"]->pool_nonstale_hash_rate*100,1)) . '</td>';
		print '</tr>';
	}
?>
				</tbody>
			</table>
		</div>
	</div>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Estimated Block Earnings</h3>
		</div>
		<div class="panel-body">
			<table class="table table-condensed table-striped">
				<thead>
					<tr>
						<th>Algorithm</th>
						<th>Per Block Payout</th>
						<th>Blocks per Day</th>
						<th>Payout per Day MYR</th>
						<th>Payout per Day BTC</th>
						<th>Payout per Day NZD</th>
					</tr>
				</thead>
				<tbody>
<?php
	$myrbtc = get_xrate($dblink,'MYR','BTC');
	$btcnzd = get_xrate($dblink,'BTC','NZD');
	
	$myrday = 0;
	for($n = 0; $n<=4; $n++)
	{
		$address = 'MNHfstDeiZfaysrheCf8uPx8D2fdNkueje';
		print '<tr>';
		print '<td>' . $algos[$n] . '</td>';
		if(isset($payouts[$n]->$address))
		{
			$perblock = $payouts[$n]->$address;
		}
		else
		{
			$perblock = 0;
		}
		print '<td>' . sprintf("%02.08f MYR",$perblock) . '</td>';
		$blocksperday = (3600*24) / calculate_esttime($dblink,'MYR',$n);
		print '<td>' . sprintf("%02.01f",round($blocksperday,1)) . '</td>';
		print '<td>' . sprintf("%02.08f MYR",round($blocksperday * $perblock,8)) . '</td><td>' . sprintf("%02.08f BTC",round($blocksperday * $perblock * $myrbtc,8)) . '</td>';
		print '<td>' . sprintf("$%02.03f NZD",round($blocksperday * $perblock * $myrbtc * $btcnzd,3)) . '</td>';
		print '</tr>';
		
		$myrday += round($blocksperday * $perblock,8);
	}
	
	print '<tr><td><strong>Total</td><td>&nbsp;</td><td>&nbsp;</td><td><strong>' . sprintf("%02.08f MYR",$myrday) . '</strong></td><td><strong>' . sprintf("%02.08f BTC",round($myrday * $myrbtc,8)) . '</strong></td>';
	print '<td><strong>' . sprintf("$%02.03f NZD",round($myrday * $myrbtc * $btcnzd,3)) . '</strong></td>';
	print '</tr>';
	
?>

				</tbody>
			</table>
		</div>
	</div>



<?php
	require_once('../include/footer.php');
?>
