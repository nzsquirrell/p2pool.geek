<?php
	require_once('../include/database.inc.php');
	include 'p2pool-def.php';

	header('Content-type: application/json');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

	$dblink = dbinit();

	$info = new stdClass;
	$info->Updated = gmdate('D jS M H:i:s e');
	$info->Nodes = get_nodes($dblink);

	echo json_encode($info);

?>