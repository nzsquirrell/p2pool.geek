<?php
	require_once('../include/config.inc.php');
	require_once('../include/database.inc.php');
	require_once('../include/functions.inc.php');
	require_once('../include/header.php');

	$dblink = dbinit();
	log_adminaction($dblink,'VCPBlocks','Accessed VCP Payouts.');
?>
<div class="container">

<?php
	require_once('../include/pageheader.php');
?>

	<ul class="nav nav-tabs">
		<li><a href="/home">Pool</a></li>
		<li><a href="/vcp">Control Panel Home</a></li>
		<li><a href="/vcp/jobs.php">Job Management</a></li>
		<li><a href="/vcp/alerts.php">Alerts</a></li>
		<li><a href="/vcp/miners.php">Miner Management</a></li>
		<li class="active"><a href="/vcp/blocks.php">Recent Blocks</a></li>
		<li><a href="/vcp/dashboard.php">Dashboard</a></li>
	</ul>

	<p></p>
	<p>System Time: <?php echo gmdate('M d H:i:s e'); ?></p>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Previous 200 Merged Coin Blocks</h3>
		</div>
		<div class="panel-body">
			<table class="table table-condensed table-striped">
				<thead>
					<tr>
						<th>Date & Time</th>
						<th>Coin</th>
						<th>Algorithm</th>
						<th>Block Height</th>
						<th>State</th>
						<th>Value</th>
						<th># of Miners Paid</th>
						<th>Total Paid</th>
					</tr>
				</thead>
				<tbody>
<?php

	$algos[0] = 'SHA256';
	$algos[1] = 'Scrypt';
	$algos[2] = 'Myr-Groestl';
	$algos[3] = 'Skein';
	$algos[4] = 'Qubit';

	$confirms = array();

	$query = "SELECT symbol,mature FROM tblmergedcoins";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		$coin = $row[0];
		$confirms[$coin] = $row[1];
	}

	$query = "SELECT height,coin,moment,status,confirmations,txid,paid,value,network FROM tblblocks WHERE coin!='MYR' ORDER BY moment DESC LIMIT 0,200";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		$d = gmdate('M d H:i:s e',strtotime($row[2]));
		$paid = 'N/A';
		$miners = 'N/A';

		print '<tr>';
		print "<td>$d</td>";
		print "<td>$row[1]</td>";
		print '<td>' . $algos["$row[8]"] . '</td>';
		print "<td>$row[0]</td>";

		$conblock = $confirms["$row[1]"];

		if($row[3]=='immature')
		{
			$state = "Immature, $row[4]/$conblock";
		}
		else if($row[3]=='generate' && $row[6]==1)
		{
			//$query2 = "SELECT COUNT(id),SUM(amount),moment FROM tblearnings WHERE block='$row[0]' AND coin='$row[1]'";
			$query2 = "SELECT COUNT(id),SUM(amount),moment FROM tblpayments WHERE block='$row[0]' AND coin='$row[1]' AND message='Payment'";
			$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
			if($result2->num_rows==1)
			{
				$row2 = $result2->fetch_row();
				$paid = "$row2[1] $row[1]";
				$miners = $row2[0];
				$state = "Paid out @ " . gmdate('H:i e',strtotime($row2[2])) . ", $row[4]/$conblock";
			}
			else
			{
				$state = "Payment Pending, $row[4]/$conblock";
			}
		}
		else if($row[3]=='generate' && $row[6]==0)
		{
			$state = "Unpaid, $row[4]/$conblock";
		}
		else if($row[3]=='orphan')
		{
			$state = 'Orphan';
		}
		else
		{
			$state = 'ERROR';
		}

		print "<td>$state</td>";
		print "<td>$row[7] $row[1]</td>";
		print "<td>$miners</td>";
		print "<td>$paid</td>";
		print '</tr>';
	}


?>
				</tbody>
			</table>
		</div>
	</div>


<?php
	require_once('../include/footer.php');
?>