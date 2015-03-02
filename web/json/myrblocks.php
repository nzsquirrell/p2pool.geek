<?php
	require_once('../include/database.inc.php');
	require_once('../include/functions.inc.php');

	if(isset($_GET['count']))
	{
		$count = $dblink->real_escape_string($_GET['count']);
	}
	else
	{
		$count = 50;
	}
	if(isset($_GET['algo']))
	{
		$algo = $dblink->real_escape_string($_GET['algo']);

		$query = "SELECT height,hash,pow_algo,difficulty,time,elapsed,algo_elapsed FROM tblnetworkblocks WHERE pow_algo=$algo ORDER BY height DESC LIMIT 0,$count";
	}
	else
	{
		$query = "SELECT height,hash,pow_algo,difficulty,time,elapsed,algo_elapsed FROM tblnetworkblocks ORDER BY height DESC LIMIT 0,$count";
	}

	$algos[0] = 'SHA256d';
	$algos[1] = 'Scrypt';
	$algos[2] = 'Myr-Groestl';
	$algos[3] = 'Skein';
	$algos[4] = 'Qubit';

	$dblink = dbinit();
	$info = new stdClass;
	$info->Updated = gmdate('D jS M H:i:s e');

	$info->Blocks = array();

	$result = $dblink->query($query) OR dberror($dblink,$query);
	$i=0;
	$last = 0;
	while($row = $result->fetch_row())
	{
		$info->Blocks[$i] = new stdClass;
		$info->Blocks[$i]->Height = $row[0];
		$info->Blocks[$i]->Hash = $row[1];
		$info->Blocks[$i]->AlgorithmID = $row[2];
		$info->Blocks[$i]->Algorithm = $algos["$row[2]"];
		$info->Blocks[$i]->Difficulty = sprintf("%02.06f",round($row[3],6));
		$info->Blocks[$i]->Time = gmdate('H:i:s',$row[4]);
		$info->Blocks[$i]->Elapsed = $row[5];
		$info->Blocks[$i]->AlgoElapsed = $row[6];
		$info->Blocks[$i]->AlgoElapsedProp = sprintf("%+d",round(($row[6]-150)/1.5,0));

		$i++;
	}


	$info->Stats = array();

	$info->Stats[0] = get_stats($dblink, '1 HOUR');
	$info->Stats[0]->Period = "Last Hour";

	$info->Stats[1] = get_stats($dblink, '12 HOUR');
	$info->Stats[1]->Period = "Last 12 Hours";

	$info->Stats[2] = get_stats($dblink, '1 DAY');
	$info->Stats[2]->Period = "Last Day";

	$info->Stats[3] = get_stats($dblink, '7 DAY');
	$info->Stats[3]->Period = "Last 7 Days";


	function get_stats($dblink, $period)
	{
		global $algos;

		$data = new stdClass;

		$query = "SELECT COUNT(height) FROM tblnetworkblocks WHERE moment >= DATE_ADD(NOW(),INTERVAL -$period)";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();
		$data->TotalBlocks = $row[0];

		$data->Algo = array();
		for($a=0; $a<=4; $a++)
		{
			$data->Algo[$a] = new stdClass;
			$data->Algo[$a]->Algorithm = $algos[$a];
			$data->Algo[$a]->Count = 0;
			$data->Algo[$a]->Difficulty = 'N/A';
			$data->Algo[$a]->Hashrate = 'N/A';
		}

		$query = "SELECT pow_algo,COUNT(height),AVG(difficulty) FROM tblnetworkblocks WHERE moment >= DATE_ADD(NOW(),INTERVAL -$period) GROUP BY pow_algo";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$id = $row[0];
			$data->Algo[$id]->Count = $row[1];
			$data->Algo[$id]->Difficulty = sprintf("%02.02f",round($row[2],2));
			$data->Algo[$id]->Hashrate = scale_rate($row[2] * pow(2,32) / 150);
		}

		return $data;
	}


	$info->GraphData = array();

	$info->GraphData[0] = array();
	$info->GraphData[0][0] = 'Algorithm';
	for($i=0;$i<=4;$i++)
	{
		$info->GraphData[0][$i+1] = $algos[$i];
	}
	for($i=0;$i<=3;$i++)
	{
		$info->GraphData[$i+1][0] = $info->Stats[$i]->Period;
		for($a=0; $a<=4; $a++)
		{
			$info->GraphData[$i+1][$a+1] = round(100 * $info->Stats[$i]->Algo[$a]->Count / $info->Stats[$i]->TotalBlocks,0);
		}
	}


	$dblink->close();

	header('Content-type: application/json');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

	echo json_encode($info);


?>