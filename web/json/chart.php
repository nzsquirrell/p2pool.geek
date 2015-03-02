<?php
	require_once('../include/config.inc.php');
	require_once('../include/database.inc.php');
	require_once('../include/functions.inc.php');

	$dblink = dbinit();

	if(isset($_GET['interval']))
	{
		$mode = $_GET['interval'];
	}
	else
	{
		$mode = 'hour';
	}

	if(isset($_GET['miner']))
	{
		$miner = $_GET['miner'];
	}
	else
	{
		$miner = 'node1';
	}

	if(substr($miner,0,4)=='node')
	{
		$nodeid = substr($miner,4,1);
		$miner = 'node';
	}
	else
	{
		$query = "SELECT node FROM tblnodeminers WHERE address='$miner' ORDER BY hashrate DESC,lastseen DESC LIMIT 0,1";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();
		$nodeid = $row[0];
	}

	if(substr($miner,0,4)=='node')
	{
		$hashrate = get_p2p_info_db($nodeid,"web/graph_data/local_hash_rate/last_$mode",$dblink);
		$deadrate = get_p2p_info_db($nodeid,"web/graph_data/local_dead_hash_rate/last_$mode",$dblink);
	}
	else
	{
		$hashrate = get_p2p_info_db($nodeid,"web/graph_data/miner_hash_rates/last_$mode",$dblink);
		$deadrate = get_p2p_info_db($nodeid,"web/graph_data/miner_dead_hash_rates/last_$mode",$dblink);

// 		$msg = file_get_contents('http://' . $config['p2pool_host'] . ':' . $config['p2pool_port'] . "/web/graph_data/miner_hash_rates/last_$mode");
// 		$hashrate = json_decode($msg);

// 		$msg = file_get_contents('http://' . $config['p2pool_host'] . ':' . $config['p2pool_port'] . "/web/graph_data/miner_dead_hash_rates/last_$mode");
// 		$deadrate = json_decode($msg);
	}

	$r = count($hashrate);

	if($mode=='month')
	{
		$dfmt = 'D jS';
		$div = 10;
	}
	elseif($mode=='year')
	{
		$dfmt = 'M';
		$div = 10;
	}
	elseif($mode=='week')
	{
		$dfmt = 'D ga';
		$div = 10;
	}
	elseif($mode=='day')
	{
		$dfmt = 'g:i a';
		$div = 10;
	}
	else
	{
		$dfmt = 'H:i';
		$div = 5;
	}

	$chart[0][0] = 'Date & Time';
	$chart[0][1] = 'Hash Rate';
	$chart[0][2] = 'Dead Rate';

	for($i=0; $i<($r-1); $i++)
	{
		$values = $hashrate[$r-$i-1];
		$dead = $deadrate[$r-$i-1];

		$chart[$i+1][0] = date($dfmt,$values[0]);

		if($miner=='node')
		{
			$chart[$i+1][1] = round($values[1]/1e6,4);
			$chart[$i+1][2] = round($dead[1]/1e6,4);
		}
		else
		{
			if(isset($values[1]->$miner))
			{
				$chart[$i+1][1] = round($values[1]->$miner/1e6,4);
			}
			else
			{
				$chart[$i+1][1] = 0;
			}
			if(isset($dead[1]->$miner))
			{
				$chart[$i+1][2] = round($dead[1]->$miner/1e6,4);
			}
			else
			{
				$chart[$i+1][2] = 0;
			}
		}
	}

	header('Content-type: application/json');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

	echo json_encode($chart);

?>
