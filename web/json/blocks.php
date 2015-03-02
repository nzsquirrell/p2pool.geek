<?php
	require_once('../include/database.inc.php');

	$blocks[0][0] = 'Block Height';
	$blocks[0][1] = 'Estimated Time';
	$blocks[0][2] = 'Actual Time';

	$dblink = dbinit();
	$i = 0;
	$max_blocks = 25;
	$network = 0;
	$coin='MYR';

	if(isset($_GET['network']))
	{
		$network = strtoupper($dblink->real_escape_string($_GET['network']));
	}

	$query = "SELECT moment,height,elapsedtime,est_time FROM tblblocks WHERE est_time!=0 AND coin='$coin' AND status!='orphan' AND network='$network' ORDER BY moment";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	if($result->num_rows>$max_blocks)
	{
		$query = "SELECT height FROM `tblblocks` WHERE est_time!=0 AND coin='$coin' AND status!='orphan' AND network='$network' ORDER BY moment DESC LIMIT " . ($max_blocks-1) . ",1";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();
		$query = "SELECT moment,height,elapsedtime,est_time FROM tblblocks WHERE est_time!=0 AND height>=$row[0] AND coin='$coin' AND status!='orphan' AND network='$network' ORDER BY moment";
		$result = $dblink->query($query) OR dberror($dblink,$query);
	}
	while($row = $result->fetch_row())
	{
		$i++;
		$blocks[$i][0] = $row[1] . date(' @ jS M H:i',strtotime($row[0]));
		$blocks[$i][1] = round($row[3]/60,1);
		$blocks[$i][2] = round($row[2]/60,1);
	}
	$result->close();

	$history = new stdClass;
	$history->last24h = new stdClass;
	$history->last7d = new stdClass;
	$history->last4w = new stdClass;

	$history->last24h->name = "Last 24 Hours";
	$history->last7d->name = "Last 7 Days";
	$history->last4w->name = "Last 4 Weeks";

	$query = "SELECT DATE_ADD(NOW(),INTERVAL -24 HOUR),DATE_ADD(NOW(),INTERVAL -7 DAY),DATE_ADD(NOW(),INTERVAL -28 DAY),NOW()";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();
	$time_24h = $row[0];
	$time_7d = $row[1];
	$time_4w = $row[2];
	$time_now = $row[3];

	$query = "SELECT moment FROM tbldifficulty ORDER BY moment LIMIT 0,1";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();
	if($row[0]>$time_24h)
	{
		$time_24h = $row[0];
	}
	if($row[0]>$time_7d)
	{
		$time_7d = $row[0];
	}
	if($row[0]>$time_4w)
	{
		$time_4w = $row[0];
	}


	$query = "SELECT COUNT(height) FROM tblblocks WHERE moment>='$time_24h' AND est_time!=0 AND coin='$coin' AND status!='orphan' AND network='$network'";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();
	$history->last24h->count = $row[0];
	$query = "SELECT COUNT(height) FROM tblblocks WHERE moment>='$time_7d' AND est_time!=0 AND coin='$coin' AND status!='orphan' AND network='$network'";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();
	$history->last7d->count = $row[0];
	$query = "SELECT COUNT(height) FROM tblblocks WHERE moment>='$time_4w' AND est_time!=0 AND coin='$coin' AND status!='orphan' AND network='$network'";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();
	$history->last4w->count = $row[0];

	$query = "SELECT AVG(luck) FROM tblblocks WHERE moment>='$time_24h' AND est_time!=0 AND coin='$coin' AND status!='orphan' AND network='$network'";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();
	$history->last24h->luck = sprintf("%01.00f%%",round($row[0]*100,0));
	$query = "SELECT AVG(difficulty) FROM tbldifficulty WHERE moment>='$time_24h' AND coin='$coin' AND network='$network'";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();
	$diff = $row[0];

	if($coin=='MYR')
	{
		$table = 'tblpoolhashrate';
		$query24 = "SELECT AVG(hashrate) FROM $table WHERE moment>='$time_24h' AND network='$network'";
		$query7 = "SELECT AVG(hashrate) FROM $table WHERE moment>='$time_7d' AND network='$network'";
		$query4 = "SELECT AVG(hashrate) FROM $table WHERE moment>='$time_4w' AND network='$network'";
	}
	else
	{
		$table = 'tblnodehashrate';
		$query24 = "SELECT SUM(AVGHash) FROM (SELECT AVG(nonstalerate) AS AVGHash FROM `tblnodehashrate` WHERE moment>'$time_24h' GROUP BY node) tmpHashrates";
		$query7 = "SELECT SUM(AVGHash) FROM (SELECT AVG(nonstalerate) AS AVGHash FROM `tblnodehashrate` WHERE moment>'$time_7d' GROUP BY node) tmpHashrates";
		$query4 = "SELECT SUM(AVGHash) FROM (SELECT AVG(nonstalerate) AS AVGHash FROM `tblnodehashrate` WHERE moment>'$time_4w' GROUP BY node) tmpHashrates";
	}

	$result = $dblink->query($query24) OR dberror($dblink,$query24);
	$row = $result->fetch_row();
	$hash = $row[0];
	$expected = (strtotime($time_now)-strtotime($time_24h))/(pow(2,32) * $diff / $hash);
	$history->last24h->expected = round($expected,1);
	$history->last24h->difficulty = sprintf("%01.06f",round($diff,6));

	$query = "SELECT AVG(luck) FROM tblblocks WHERE moment>='$time_7d' AND est_time!=0 AND coin='$coin' AND status!='orphan' AND network='$network'";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();
	$history->last7d->luck = sprintf("%01.00f%%",round($row[0]*100,0));
	$query = "SELECT AVG(difficulty) FROM tbldifficulty WHERE moment>='$time_7d' AND coin='$coin' AND network='$network'";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();
	$diff = $row[0];
	$result = $dblink->query($query7) OR dberror($dblink,$query7);
	$row = $result->fetch_row();
	$hash = $row[0];
	$expected = (strtotime($time_now)-strtotime($time_7d))/(pow(2,32) * $diff / $hash);
	$history->last7d->expected = round($expected,1);
	$history->last7d->difficulty = sprintf("%01.06f",round($diff,6));

	$query = "SELECT AVG(luck) FROM tblblocks WHERE moment>='$time_4w' AND est_time!=0 AND coin='$coin' AND status!='orphan' AND network='$network'";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();
	$history->last4w->luck = sprintf("%01.00f%%",round($row[0]*100,0));
	$query = "SELECT AVG(difficulty) FROM tbldifficulty WHERE moment>='$time_4w' AND coin='$coin' AND network='$network'";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();
	$diff = $row[0];
	$result = $dblink->query($query4) OR dberror($dblink,$query4);
	$row = $result->fetch_row();
	$hash = $row[0];
	$expected = (strtotime($time_now)-strtotime($time_4w))/(pow(2,32) * $diff / $hash);
	$history->last4w->expected = round($expected,1);
	$history->last4w->difficulty = sprintf("%01.06f",round($diff,6));

/*	$query = "SELECT AVG(avgdifficulty),AVG(avghashrate),AVG(luck) FROM tblblocks WHERE moment>='$time_7d' AND est_time!=0";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();
	$history->last7d->expected = round((strtotime($time_now)-strtotime($time_7d))/(pow(2,32) * $row[0] / $row[1]),1);
	$history->last7d->luck = sprintf("%01.00f%%",round($row[2]*100,2));
	$history->last7d->difficulty = sprintf("%01.06f",round($row[0],6));
	$query = "SELECT AVG(avgdifficulty),AVG(avghashrate),AVG(luck) FROM tblblocks WHERE moment>='$time_4w' AND est_time!=0";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();
	$history->last4w->expected = round((strtotime($time_now)-strtotime($time_4w))/(pow(2,32) * $row[0] / $row[1]),1);
	$history->last4w->luck = sprintf("%01.00f%%",round($row[2]*100,2));
	$history->last4w->difficulty = sprintf("%01.06f",round($row[0],6));*/

	$dblink->close();

	header('Content-type: application/json');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

	$output = new stdClass;
	$output->blocks = $blocks;
	$output->history = $history;

	echo json_encode($output);


?>