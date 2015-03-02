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

 	$global_stats[0] = get_p2p_info_db(12,'global_stats',$dblink); // SHA256
 	$global_stats[1] = get_p2p_info_db(13,'global_stats',$dblink); // Scrypt
 	$global_stats[2] = get_p2p_info_db(4,'global_stats',$dblink); // Groestl
 	$global_stats[3] = get_p2p_info_db(3,'global_stats',$dblink); // Skein
	$global_stats[4] = get_p2p_info_db(1,'global_stats',$dblink); // Qubit

	$info['MYR'] = new stdClass;
	$info['MYR']->AuxPoW = false;
	$info['MYR']->Algorithms = array();

	for($i=0; $i<=4; $i++)
	{
		$info['MYR']->Algorithms[$i] = new stdClass;
		$info['MYR']->Algorithms[$i]->Name = $algos[$i];
		$info['MYR']->Algorithms[$i]->PoolHashrate = $global_stats[$i]->pool_hash_rate;
		$info['MYR']->Algorithms[$i]->PoolNonStale = $global_stats[$i]->pool_nonstale_hash_rate;
	}

	$poolhash = array();
	for($i=0; $i<=4; $i++)
	{
		$poolhash[$i] = 0;
		$poolstale[$i] = 0;
	}

	$query = "SELECT id,network,nonstalerate,hashrate FROM tblnodes WHERE enabled=1 AND merged=1 AND status!='Offline'";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		$network = $row[1];
		$poolhash[$network] += $row[2];
		$poolstale[$network] += $row[3];
	}

	$query = "SELECT symbol,network FROM tblmergedcoins WHERE enabled=1";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		$coin = $row[0];
		$network = $row[1];
		if(!isset($info[$coin]))
		{
			$info[$coin] = new stdClass;
			$info[$coin]->AuxPoW = true;
			$info[$coin]->Algorithms = array();
		}
		$info[$coin]->Algorithms[$network] = new stdClass;
		$info[$coin]->Algorithms[$network]->Name = $algos[$network];
		$info[$coin]->Algorithms[$network]->PoolHashrate = $poolstale[$network];
		$info[$coin]->Algorithms[$network]->PoolNonStale = $poolhash[$network];
	}


	header('Content-type: application/json');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

	echo json_encode($info);

?>
