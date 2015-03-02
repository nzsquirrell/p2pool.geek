<?php
	require_once('include/config.inc.php');
	require_once('include/database.inc.php');
	require_once('include/header.php');
	require_once('include/functions.inc.php');
?>


<div class="container">

<?php
	require_once('include/pageheader.php');

	$dblink = dbinit();

	$query = "SELECT value FROM tbldonationsettings WHERE name='address'";
 	$result = $dblink->query($query) OR dberror($dblink,$query);
 	$row = $result->fetch_row();
	$address = $row[0];
	$query = "SELECT value FROM tbldonationsettings WHERE name='minpayout'";
 	$result = $dblink->query($query) OR dberror($dblink,$query);
 	$row = $result->fetch_row();
	$minpayout = $row[0];
	$query = "SELECT value FROM tbldonationsettings WHERE name='lastpayout'";
 	$result = $dblink->query($query) OR dberror($dblink,$query);
 	$row = $result->fetch_row();
	$lastpayout = $row[0];
	$query = "SELECT balance FROM tbldonationbalance ORDER BY moment DESC LIMIT 0,1";
 	$result = $dblink->query($query) OR dberror($dblink,$query);
 	$row = $result->fetch_row();
	$balance = $row[0];
	$query = "SELECT SUM(amount) FROM tbldonationpayouts WHERE complete=1";
 	$result = $dblink->query($query) OR dberror($dblink,$query);
 	$row = $result->fetch_row();
	$total = $row[0];



?>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Myriad P2Pool Donations</h3>
		</div>
		<div class="panel-body">
			<p>
			This is an automated donation system that encourages miners to use P2Pool. Mining on P2Pool helps to decentralise the network, ensuring that
			traditional pools do not gain a large slice of the hashrate and potentially impact the security of the network.
			</p>
			<p>
			Donations received at the donation address (see below) are saved up until they exceed the payout threshold. The donation is then split up
			amongst the 5 Myriadcoin P2Pool networks to both reward those already mining and to incentivise P2Pool mining on all 5 algorithms. Firstly, each network receives
			5% of the donation amount. Then the remaining 75% of the donation is distributed to each network on a proportional basis,
			determined by its time to block relative to the other networks.
			This results in those networks with a long block time (and hence less P2Pool mining) receiving a greater share
			of the donations, to act as an incentive to increase P2Pool mining on that algorithm. Each miner on each network receives an amount based on their expected block earnings.
			</p>
			<ul>
				<li>Donation Address: <strong><?php echo $address; ?></strong></li>
				<li>Payout Threshold: <strong><?php echo sprintf("%02.08f MYR",$minpayout); ?></strong></li>
				<li>Donations Balance: <strong><?php echo sprintf("%02.08f MYR",$balance); ?></strong></li>
				<li>Total Paid Out: <strong><?php echo sprintf("%02.08f MYR",$total); ?></strong></li>
				<li>Last Paid Out: <strong><?php echo gmdate('g:i a l jS F (e)',strtotime($lastpayout)); ?></strong></li>
			</ul>
		</div>
	</div>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Myriad P2Pool Donations Stats &amp; Estimates</h3>
		</div>
		<div class="panel-body table-responsive">

			<table class="table table-hover">
				<thead>
					<tr>
						<th>Algorithm</th>
						<th>Avg. Difficulty (60 mins)</th>
						<th>Avg. P2Pool Hashrate (60 mins)</th>
						<th>Time to Block</th>
						<th>Donation Weight</th>
						<th>Donations To Date</th>
					</tr>
				</thead>
				<tbody>
<?php


// 	$query = "SELECT moment,title,body FROM tblnews WHERE display='Y' ORDER BY moment DESC";
// 	$result = $dblink->query($query) OR dberror($dblink,$query);
// 	while($row = $result->fetch_row())
//	$result->close();

	$algos[0] = 'SHA256';
	$algos[1] = 'Scrypt';
	$algos[2] = 'Myr-Groestl';
	$algos[3] = 'Skein';
	$algos[4] = 'Qubit';

	$query = "SELECT * FROM tbldonationest ORDER BY network";
 	$result = $dblink->query($query) OR dberror($dblink,$query);
 	while($row = $result->fetch_row())
	{
		print "<tr>";

		print '<td class="active">' . $algos["$row[0]"] . '</td>';
		print '<td>' . round($row[1],6) . '</td>';
		print '<td>' . scale_rate($row[2]) . '</td>';
		print '<td>' . seconds2time($row[3]) . '</td>';
		print '<td>' . sprintf("%02.02f%%",round(100*$row[4],2)) . '</td>';

		$query2 = "SELECT SUM(amount) FROM tbldonationpayouts WHERE network=$row[0] AND complete=1";
		$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
		$row2 = $result2->fetch_row();
		print '<td>' . sprintf("%02.08f MYR",round($row2[0],8)) . '</td>';
		print "</tr>";
	}

?>

				</tbody>
			</table>
		</div>
	</div>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Myriad P2Pool Donations To Date By Miner</h3>
		</div>
		<div class="panel-body table-responsive">
			<table class="table table-hover table-condensed">
				<thead>
					<tr>
						<th>Miner Address</th>
						<th>Number of Donations Received</th>
						<th>Amount</th>
					</tr>
				</thead>
				<tbody>
<?php

	$query = "SELECT address,COUNT(complete),SUM(amount) FROM tbldonationpayouts WHERE complete=1 GROUP BY address ORDER BY address";
 	$result = $dblink->query($query) OR dberror($dblink,$query);
 	while($row = $result->fetch_row())
	{
		print "<tr>";
		print "<td>$row[0]</td><td>$row[1]</td>";
		print '<td>' . sprintf("%02.08f MYR",round($row[2],8)) . '</td>';
		print "</tr>";
	}

?>
				</tbody>
			</table>
		</div>
	</div>

<?php
	require_once('include/footer.php');
?>
