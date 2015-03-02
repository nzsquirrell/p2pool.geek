<?php
	require_once('../include/database.inc.php');
	include 'p2pool-def.php';

	header('Content-type: application/json');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

	$dblink = dbinit();

	if(isset($_GET['nodeid']))
	{
		$nodeid = $dblink->real_escape_string($_GET['nodeid']);
	}
	else
	{
		$nodeid = 0;
	}

	$coins = get_coins($dblink);
	$nodes = get_nodes($dblink);
	$pools = get_pools($dblink,$nodes,$coins);

	$info = new stdClass;
	$info->Updated = gmdate('D jS M H:i:s e');
	$info->nodes = $nodes;
	$info->miners = get_miners($dblink,$pools,$nodeid);

	echo json_encode($info);


?>