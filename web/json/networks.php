<?php
	require_once('../include/database.inc.php');

	$dblink = dbinit();

	$nodes = array();
	$i = 0;

	if(isset($_GET['network']))
	{
		$network = $dblink->real_escape_string($_GET['network']);
	}
	else
	{
		$network=0;
	}

	$ports[0] = 5578;
	$ports[1] = 5556;
	$ports[2] = 3333;
	$ports[3] = 5589;
	$ports[4] = 5567;

	$port = $ports[$network];

	$query = "SELECT ip,country_name,region_name,city,latitude,longitude,miners,hashrate,hostname,isvertgeek FROM tblp2pnodes WHERE network=$network AND public=1 AND seenonupdate=1 ORDER BY country_code,city,miners,hostname DESC";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		$i++;
		$nodes[$i] = new stdClass;
		$nodes[$i]->IPAddress = $row[0];
		$nodes[$i]->Country = $row[1];
		$nodes[$i]->Region = $row[2];
		$nodes[$i]->City = $row[3];
		$nodes[$i]->Latitude = $row[4];
		$nodes[$i]->Longitude = $row[5];
		$nodes[$i]->Title = '';
		if($row[3]!='')
		{
			$nodes[$i]->Title = "$row[3], ";
		}
		if($row[2]!='')
		{
			$nodes[$i]->Title .= "$row[2], ";
		}
		$nodes[$i]->Title .= $row[1];
		if($row[8]!='')
		{
			$url = $row[8];
		}
		else
		{
			$url = $row[0];
		}
		$nodes[$i]->Info = $nodes[$i]->Title . "<br/>Miners: $row[6]<br/>Hashrate: " . sprintf("%00.02f MH/s",round($row[7]/1e6,2)) . "<br/>";
		$nodes[$i]->URL = 'http://' . $url . ':' . $port;
		$nodes[$i]->IsVertGeek = ($row[9]==1);
	}



	header('Content-type: application/json');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

	$info = new stdClass;

	$query = "SELECT lastseen FROM tblp2pnodes WHERE network=$network ORDER BY lastseen DESC LIMIT 0,1";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();

	$info->Updated = gmdate('l jS F H:i:s e',strtotime($row[0]));
	$info->Nodes = $nodes;

	echo json_encode($info);


?>