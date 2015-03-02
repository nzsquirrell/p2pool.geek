<?php
	include 'p2pool-def.php';

	$pool = new p2poolinfo();

	header('Content-type: application/json');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

	echo json_encode($pool);

?>