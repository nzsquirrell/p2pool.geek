<?php
	require_once('include/config.inc.php');
	require_once('include/database.inc.php');
	require_once('include/header.php');
	require_once('include/functions.inc.php');
?>


<div class="container">

<?php
	require_once('include/pageheader.php');
?>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Myriad P2Pool Weekly Statistics</h3>
		</div>
		<div class="panel-body table-responsive">
			<p>For period <em><?php echo gmdate('D jS M H:i:s',time()-(7*24*3600));?></em> thru <em><?php echo gmdate('D jS M H:i:s e');?></em></p>

			<table class="table table-hover">
				<thead>
					<tr>
						<th>&nbsp</th>
						<th class="active">SHA256d</th>
						<th class="active">Scrypt</th>
						<th class="active">Myr-Groestl</th>
						<th class="active">Skein</th>
						<th class="active">Qubit</th>
					</tr>
				</thead>
				<tbody>
<?php

	$dblink = dbinit();

// 	$query = "SELECT moment,title,body FROM tblnews WHERE display='Y' ORDER BY moment DESC";
// 	$result = $dblink->query($query) OR dberror($dblink,$query);
// 	while($row = $result->fetch_row())
//	$result->close();

	$query = "SELECT AVG(difficulty),MAX(difficulty),MIN(difficulty) FROM `tbldifficulty` WHERE coin='MYR' and moment>=DATE_ADD(NOW(),INTERVAL -7 DAY) GROUP BY network";
	$result = $dblink->query($query) OR dberror($dblink,$query);

	$n = 0;
	while($row = $result->fetch_row())
	{
		$data[$n] = $row;
		$n++;
	}

	$query = "SELECT AVG(hashrate) FROM `tblpoolhashrate` WHERE moment>=DATE_ADD(NOW(),INTERVAL -7 DAY) GROUP BY network";
	$result = $dblink->query($query) OR dberror($dblink,$query);

	$n = 0;
	while($row = $result->fetch_row())
	{
		$hash[$n] = new StdClass;
		$hash[$n]->Network = (pow(2,32) * $data[$n][0])/150;
		$hash[$n]->P2Pool = $row[0];
		$hash[$n]->Prop = 100 * $hash[$n]->P2Pool / $hash[$n]->Network;

		$blocks[$n] = new StdClass;
		$blocks[$n]->EstTotal = round(4032 * $hash[$n]->Prop / 100,1);
		$blocks[$n]->ActualTotal = 0;
		$blocks[$n]->ActualTime = 0;
		$blocks[$n]->EstTime = (3600 * 24 * 7)/$blocks[$n]->EstTotal;
		$blocks[$n]->MYRpGHS = 0;
		$blocks[$n]->BTCpGHS = 0;
		$n++;
	}

	$query = "SELECT value FROM tblxrates WHERE src='MYR' AND dst='BTC'";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();
	$myrbtc = $row[0];

	$query = "SELECT network,COUNT(height),AVG(elapsedtime),AVG(est_time) FROM tblblocks WHERE moment>=DATE_ADD(NOW(),INTERVAL -7 DAY) AND coin='MYR' GROUP BY network";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		$n = $row[0];
		$blocks[$n]->ActualTotal = $row[1];
		$blocks[$n]->ActualTime = $row[2];
		$blocks[$n]->EstTime = $row[3];

		$blocks[$n]->MYRpGHS = (1e9 / $hash[$n]->P2Pool) * (1000 * $blocks[$n]->ActualTotal) / 7;
		$blocks[$n]->BTCpGHS = round($blocks[$n]->MYRpGHS * $myrbtc,8);
	}

	$dblink->close();


	print '<tr><td class="active">Difficulty - Average</td>';
	for($n=0; $n<=4; $n++)
	{
		print '<td>' . sprintf("%08.06f",$data[$n][0]) . '</td>';
	}
	print '</tr>';
	print '<tr><td class="active">Difficulty - Maximum</td>';
	for($n=0; $n<=4; $n++)
	{
		print '<td>' . sprintf("%08.06f",$data[$n][1]) . '</td>';
	}
	print '</tr>';
	print '<tr><td class="active">Difficulty - Minimum</td>';
	for($n=0; $n<=4; $n++)
	{
		print '<td>' . sprintf("%08.06f",$data[$n][2]) . '</td>';
	}
	print '</tr>';
	print '<tr><td class="active">Hashrate - Network - Average</td>';
	for($n=0; $n<=4; $n++)
	{
		print '<td>' . scale_rate($hash[$n]->Network) . '</td>';
	}
	print '</tr>';
	print '<tr><td class="active"><strong>Hashrate - P2Pool - Average</strong></td>';
	for($n=0; $n<=4; $n++)
	{
		print '<td><strong>' . scale_rate($hash[$n]->P2Pool) . ' (' . sprintf("%02.1f%%",$hash[$n]->Prop) . ')</strong></td>';
	}
	print '</tr>';
	print '<tr><td class="active">Blocks Found - Network - Estimated</td>';
	print '<td>4032</td><td>4032</td><td>4032</td><td>4032</td><td>4032</td>';
	print '</tr>';
	print '<tr><td class="active">Blocks Found - P2Pool - Estimated</td>';
	for($n=0; $n<=4; $n++)
	{
		print '<td>' . $blocks[$n]->EstTotal . '</td>';
	}
	print '</tr>';
	print '<tr><td class="active"><strong>Blocks Found - P2Pool - Actual</strong></td>';
	for($n=0; $n<=4; $n++)
	{
		print '<td><strong>' . $blocks[$n]->ActualTotal . '</strong></td>';
	}
	print '</tr>';
	print '<tr><td class="active">Time per Block - P2Pool - Estimated</td>';
	for($n=0; $n<=4; $n++)
	{
		$r = ($blocks[$n]->EstTime>0) ? seconds2time($blocks[$n]->EstTime) : 'N/A';
		print '<td>' . $r . '</td>';
	}
	print '</tr>';
	print '<tr><td class="active"><strong>Time per Block - P2Pool - Actual</strong></td>';
	for($n=0; $n<=4; $n++)
	{
		$r = ($blocks[$n]->ActualTime>0) ? seconds2time($blocks[$n]->ActualTime) : 'N/A';
		print '<td><strong>' . $r . '</strong></td>';
	}
	print '</tr>';
	print '<tr><td class="active">MYR Earnings per GH/s per Day</td>';
	for($n=0; $n<=4; $n++)
	{
		print '<td>' . sprintf("%08.08f MYR",$blocks[$n]->MYRpGHS) . '</td>';
	}
	print '</tr>';
	print '<tr><td class="active">BTC Earnings per GH/s per Day</td>';
	for($n=0; $n<=4; $n++)
	{
		print '<td>' . sprintf("%08.08f BTC",$blocks[$n]->BTCpGHS) . '</td>';
	}
	print '</tr>';



	print '</tbody></table>';
	print '</div>';
	print '</div>';

	require_once('include/footer.php');
?>
