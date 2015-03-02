<?php
	require_once('../include/database.inc.php');
	require_once('../include/functions.inc.php');

	$dblink = dbinit();

	$info = array();

	$algos[0] = 'SHA256';
	$algos[1] = 'Scrypt';
	$algos[2] = 'Myr-Groestl';
	$algos[3] = 'Skein';
	$algos[4] = 'Qubit';

	$query = "SELECT * FROM tbldonationest ORDER BY network";
 	$result = $dblink->query($query) OR dberror($dblink,$query);
 	while($row = $result->fetch_row())
	{
		$network = $row[0];
		$info[$network] = new stdClass;
		$info[$network]->Network = $network;
		$info[$network]->Algorithm = $algos[$network];
		$info[$network]->Difficulty = $row[1];
		$info[$network]->Hashrate = $row[2];
		$info[$network]->TimeToBlock = $row[3];
		$info[$network]->DonationWeight = $row[4];

	}

	$query = "SELECT network,SUM(amount) FROM tbldonationpayouts WHERE moment>=DATE_ADD(NOW(),INTERVAL -1 DAY) GROUP BY network";
 	$result = $dblink->query($query) OR dberror($dblink,$query);
 	while($row = $result->fetch_row())
	{
		$network = $row[0];
		$info[$network]->Last24HourPayout = $row[1];
	}


	header('Content-type: application/json');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

	echo json_encode($info);

?>
