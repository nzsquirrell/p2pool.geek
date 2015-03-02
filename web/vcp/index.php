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
			<h3 class="panel-title">System Job Status</h3>
		</div>
		<div class="panel-body">
			<table class="table table-condensed table-striped">
				<thead>
					<tr>
						<th>Name</th>
						<th>Description</th>
						<th>Enabled</th>
						<th>Interval</th>
						<th>Last Run</th>
						<th>Active</th>
					</tr>
				</thead>
				<tbody>
<?php
	$query = "SELECT * FROM tbljobs ORDER BY name";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		print '<tr>';
		$d = gmdate('M d H:i:s e',strtotime($row[4]));
		print "<td>$row[0]</td><td>$row[1]</td><td>$row[2]</td><td>$row[3]</td><td>$d</td>";
		if($row[6]==1)
		{
			print '<td><span class="label label-danger">Yes</label></td>';
		}
		else
		{
			print '<td><span class="label label-primary">No</span></td>';
		}

		print '</tr>';
	}
?>
				</tbody>
			</table>
		</div>
	</div>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Nodes</h3>
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
						<th>Hashrate (stale)</th>
						<th>Non Stale Rate</th>
						<th>Uptime</th>
						<th>Availability</th>
						<th>RPC Status</th>
						<th>Merged</th>
					</tr>
				</thead>
				<tbody>
<?php
	$algos[0] = 'SHA256';
	$algos[1] = 'Scrypt';
	$algos[2] = 'Myr-Groestl';
	$algos[3] = 'Skein';
	$algos[4] = 'Qubit';


	$query = "SELECT * FROM tblnodes ORDER BY abbr";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$mcount = 0;
	$htotal = 0;
	$sptotal = 0;
	$nhrtotal = 0;

	while($row = $result->fetch_row())
	{
		$d = gmdate('M d H:i:s e',strtotime($row[5]));
		$h = sprintf("%01.03f MH/s",round($row[7]/1e6,3));
		if($row[7]>0)
		{
			$sp = sprintf("%01.01f%%",round(100*$row[8]/$row[7],1));
		}
		else
		{
			$sp = sprintf("%01.01f%%",0);
		}
		$nhr = sprintf("%01.03f MH/s",round($row[9]/1e6,3));
		$u = seconds2time($row[10]);
		print '<tr>';
		if($row[17]=='Online')
		{
			$status = '<span class="label label-success">Online</span>';
		}
		elseif($row[17]=='Unknown')
		{
			$status = '<span class="label label-warning">Unknown</span>';
		}
		else
		{
			$status = '<span class="label label-danger">Offline</span>';
		}
		print "<td>$row[1]</td><td>$row[2]</td><td>" . $algos["$row[21]"] . "</td><td>$status</td><td>$d</td><td>$row[6]</td><td>$h ($sp)</td><td>$nhr</td><td>$u</td>";

		$query2 = "SELECT AVG(state) FROM tblnodeavailability WHERE moment>=DATE_ADD(NOW(),INTERVAL -7 DAY) AND node=$row[0]";
		$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
		$row2 = $result2->fetch_row();

		print '<td>' . sprintf("%01.02f%%",round(100*$row2[0],2)) . '</td>';

		/*
		if($row[22]!='')
		{
			print '<td>';
			$query2 = "SELECT packets FROM tblrpcmonitor WHERE source='$row[22]' AND coin='MON' ORDER BY moment DESC LIMIT 0,1";
			$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
			$row2 = $result2->fetch_row();
			if($row2[0]>0)
			{
				print '<span class="label label-success">MON</span> ';
			}
			else
			{
				print '<span class="label label-danger">MON</span> ';
			}
			$query2 = "SELECT packets FROM tblrpcmonitor WHERE source='$row[22]' AND coin='PLX' ORDER BY moment DESC LIMIT 0,1";
			$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
			$row2 = $result2->fetch_row();
			if($row2[0]>0)
			{
				print '<span class="label label-success">PLX</span> ';
			}
			else
			{
				print '<span class="label label-danger">PLX</span> ';
			}
		}
		else
		{
			print '<td><span class="label label-warning">MON</span> <span class="label label-warning">PLX</span></td>';
		}*/
		print '<td>N/A</td>';

		if($row[23]==1)
		{
			print '<td><span class="label label-success">YES</span></td>';
		}
		else
		{
			print '<td><span class="label label-danger">NO</span></td>';
		}
		print '</tr>';
		$mcount += $row[6];
		$htotal += $row[7];
		$sptotal += $row[8];
		$nhrtotal += $row[9];
	}
	$h = sprintf("%01.03f MH/s",round($htotal/1e6,3));
	$sp = sprintf("%01.01f%%",round(100*$sptotal/$htotal,1));
	$nhr = sprintf("%01.03f MH/s",round($nhrtotal/1e6,3));
	print '<tr class="info">';
	print "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td><strong>$mcount</strong></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
	print '</tr>';
?>

				</tbody>
			</table>
		</div>
	</div>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Merged Coin Balances</h3>
		</div>
		<div class="panel-body">
			<table class="table table-condensed table-striped">
				<thead>
					<tr>
						<th>Coin</th>
						<th>Algorithm</th>
						<th>Miners with Non-Zero Balance</th>
						<th>Average Balance</th>
						<th>Total Balance</th>
						<th>Wallet Balance</th>
					</tr>
				</thead>
				<tbody>
<?php

	$query = "SELECT coin,network,COUNT(address),AVG(balance),SUM(balance) FROM tblminercoins WHERE balance>0 GROUP BY coin,network";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		print '<tr>';
		print "<td>$row[0]</td><td>" . $algos["$row[1]"] . "</td><td>$row[2]</td><td>" . sprintf("%01.08f",$row[3]) . " $row[0]</td><td>" . sprintf("%01.08f",$row[4]) . " $row[0]</td>";

		$query2 = "SELECT balance FROM tblcoininfo WHERE coin='$row[0]' AND network='$row[1]'";
		$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
		$row2 = $result2->fetch_row();
		print "<td>" . sprintf("%01.08f",$row2[0]) . " $row[0]</td>";
		print '</tr>';
	}



?>

				</tbody>
			</table>
		</div>
	</div>



	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Merged Coin Blocks & Payouts</h3>
		</div>
		<div class="panel-body">
			<table class="table table-condensed table-striped">
				<thead>
					<tr>
						<th>Coin</th>
						<th>Algorithm</th>
						<th>Period</th>
						<th>Blocks</th>
						<th>Block Value</th>
						<th>Paid Out</th>
						<th>Fees</th>
						<th>Donations</th>
					</tr>
				</thead>
				<tbody>
<?php

	function print_payouts($dblink,$coin,$network,$days)
	{
		$algos[0] = 'SHA256';
		$algos[1] = 'Scrypt';
		$algos[2] = 'Myr-Groestl';
		$algos[3] = 'Skein';
		$algos[4] = 'Qubit';
	
		$algo = $algos[$network];
		$query = "SELECT COUNT(height),SUM(value) FROM tblblocks WHERE coin='$coin' AND network=$network AND moment>=DATE_ADD(NOW(),INTERVAL -$days DAY) AND status!='orphan'";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();
		$value = $row[1];
		$period = "$days Days";
		if($days==1)
		{
			$period = "24 Hours";
		}
		print "<tr><td>$coin</td><td>$algo</td><td>Last $period</td><td>$row[0]</td><td>" . sprintf("%01.08f $coin",$row[1]) . "</td>";

		$query = "SELECT height FROM tblblocks WHERE coin='$coin' AND network=$network AND moment>=DATE_ADD(NOW(),INTERVAL -$days DAY) ORDER BY height LIMIT 0,1";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		if($row = $result->fetch_row())
		{
			$block = $row[0];

			$query = "SELECT SUM(amount) FROM tblpayments WHERE coin='$coin' AND network=$network AND block>=$block AND message='Payment'";
			$result = $dblink->query($query) OR dberror($dblink,$query);
			$row = $result->fetch_row();
			$payments = $row[0];

			$query = "SELECT SUM(amount) FROM tblpayments WHERE coin='$coin' AND network=$network AND block>=$block AND message='Donation'";
			$result = $dblink->query($query) OR dberror($dblink,$query);
			$row = $result->fetch_row();
			$donations = $row[0];

			$query = "SELECT SUM(amount) FROM tblpayments WHERE coin='$coin' AND network=$network AND block>=$block AND message='Fee'";
			$result = $dblink->query($query) OR dberror($dblink,$query);
			$row = $result->fetch_row();
			$fees = $row[0];
		}
		else
		{
			$payments = 0;
			$donations = 0;
			$fees = 0;
		}
		print '<td>' . sprintf("%01.08f $coin",$payments) . '</td><td>' . sprintf("%01.08f $coin",-1*$fees) . '</td><td>' . sprintf("%01.08f $coin",-1*$donations) . '</td>';

		print '</tr>';
	}


	$query = "SELECT symbol,network FROM tblmergedcoins WHERE enabled=1 ORDER BY symbol";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		print_payouts($dblink,$row[0],$row[1],1);
		print_payouts($dblink,$row[0],$row[1],2);
		print_payouts($dblink,$row[0],$row[1],7);
	 	print_payouts($dblink,$row[0],$row[1],14);
 		print_payouts($dblink,$row[0],$row[1],28);
 		print_payouts($dblink,$row[0],$row[1],180);
		print '<tr><td colspan="8">&nbsp;</td></tr>';
	}

?>
				</tbody>
			</table>
		</div>
	</div>


<?php
	require_once('../include/footer.php');
?>
