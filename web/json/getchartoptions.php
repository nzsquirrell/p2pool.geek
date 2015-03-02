<?php
	require_once('../include/database.inc.php');

	header('Content-type: application/json');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

	$dblink = dbinit();

	$algos[0] = 'SHA256';
	$algos[1] = 'Scrypt';
	$algos[2] = 'Groestl';
	$algos[3] = 'Skein';
	$algos[4] = 'Qubit';

	$info = new stdClass;
	$info->Nodes = array();
	$info->Miners = array();
	$i=0;
	$query = "SELECT id,abbr,name,network FROM tblnodes WHERE status='Online' AND enabled=1 ORDER BY abbr";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		$node = new stdClass;
		$node->id = $row[0];
		$node->abbr = $row[1];
		$node->name = $row[2];
		$node->algo = $algos["$row[3]"];
		$i++;
		$info->Nodes[$i] = $node;
	}

	$i = 0;
	$query = "SELECT address FROM tblnodeminers WHERE hashrate>0 GROUP BY address ORDER BY address";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		$i++;
		$info->Miners[$i] = $row[0];
	}


	echo json_encode($info);

?>