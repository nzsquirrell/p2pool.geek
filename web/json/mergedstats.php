<?php
	require_once('../include/database.inc.php');
	require_once('../include/functions.inc.php');

	include 'p2pool-def.php';

	$algos[0] = 'SHA256';
	$algos[1] = 'Scrypt';
	$algos[2] = 'Myr-Groestl';
	$algos[3] = 'Skein';
	$algos[4] = 'Qubit';

	header('Content-type: application/json');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

	$dblink = dbinit();

	$info = new stdClass;
	$info->Updated = gmdate('D jS M H:i:s e');

	$poolhash = array();
	for($i=0; $i<=4; $i++)
	{
		$poolhash[$i] = 0;
	}

	$query = "SELECT id,network,nonstalerate FROM tblnodes WHERE enabled=1 AND merged=1 AND status!='Offline'";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		$network = $row[1];
		$poolhash[$network] += $row[2];
	}

	$i = 0;
	
	$query = "SELECT tblmergedcoins.symbol,tblmergedcoins.name,tblmergedcoins.network,tblcoininfo.info FROM tblmergedcoins,tblcoininfo WHERE enabled=1 AND tblmergedcoins.symbol=tblcoininfo.coin AND tblmergedcoins.network=tblcoininfo.network ORDER BY tblmergedcoins.symbol";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		$coin = $row[0];
		$network = $row[2];
		$info->MergedCoins[$i] = new stdClass;
		$info->MergedCoins[$i]->Symbol = $row[0];
		$info->MergedCoins[$i]->Name = $row[1];
		$info->MergedCoins[$i]->Algorithm = $algos["$row[2]"];
		$info->MergedCoins[$i]->PoolHashrateRaw = $poolhash[$network];
		$info->MergedCoins[$i]->PoolHashrate = scale_rate($poolhash[$network]);
		$coininfo = json_decode($row[3]);
		if(isset($coininfo->networkhashps))
		{
			$info->MergedCoins[$i]->PoolHashrateProp = sprintf("%01.02f%%",round(100 * $poolhash[$network] / $coininfo->networkhashps,2));
		}
		else
		{
			$netrate = (pow(2,32) * $coininfo->difficulty) / 120;
			$info->MergedCoins[$i]->PoolHashrateProp = sprintf("%01.02f%%",round(100 * $poolhash[$network] / $netrate,2));
		}

		$query2 = "SELECT height,moment FROM tblblocks WHERE coin='$coin' AND network=$network AND status!='orphan' ORDER BY height DESC LIMIT 0,1";
		$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
		if($row2 = $result2->fetch_row())
		{
			$info->MergedCoins[$i]->LastBlock = $row2[0];
			$info->MergedCoins[$i]->TimeSince = seconds2time(time() - strtotime($row2[1]));
			$timetoblock = calculate_esttime($dblink,$coin,$network);
			$blocksperday = (24*3600) / $timetoblock;
			$info->MergedCoins[$i]->TimeToBlock = seconds2time($timetoblock);
			$info->MergedCoins[$i]->BlocksPerDay = round($blocksperday,1);

			$query2 = "SELECT COUNT(height),SUM(value) FROM tblblocks WHERE coin='$coin' AND network=$network AND status!='orphan' AND moment>=DATE_ADD(NOW(), INTERVAL -1 DAY)";
			$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
			$row2 = $result2->fetch_row();
			$info->MergedCoins[$i]->BlocksToday = $row2[0];
			$info->MergedCoins[$i]->ValueToday = sprintf("%01.08f $coin",round($row2[1],8));

		}
		else
		{
			$info->MergedCoins[$i]->LastBlock = '<em>None Found</em>';
			$info->MergedCoins[$i]->TimeSince = 'N/A';
			$timetoblock = calculate_esttime($dblink,$coin,$network);
			$blocksperday = (24*3600) / $timetoblock;
			$info->MergedCoins[$i]->TimeToBlock = seconds2time($timetoblock);
			$info->MergedCoins[$i]->BlocksPerDay = round($blocksperday,1);
			$info->MergedCoins[$i]->BlocksToday = 0;
			$info->MergedCoins[$i]->ValueToday = sprintf("%01.08f $coin",0);
		}
		$i++;
	}

	echo json_encode($info);

?>