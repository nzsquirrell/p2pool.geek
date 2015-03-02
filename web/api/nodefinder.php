<?php
	require_once('../include/database.inc.php');
	require_once('../include/functions.inc.php');

	$dblink = dbinit();

	function get_geoip($ip)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://freegeoip.net/json/$ip");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$response = curl_exec($ch);

		$output = new stdClass;
		$output->errorno = curl_errno($ch);
		$output->errormsg = curl_error($ch);
		$output->data = '';
		if($output->errorno==0)
		{
			$output->data = json_decode($response);
		}
		return $output;
	}

	function distance($lat1,$long1,$lat2,$long2)
	{
		$r = 6371; // km
		$dlat = deg2rad($lat2 - $lat1);
		$dlong = deg2rad($long2 - $long1);

		$l1 = deg2rad($lat1);
		$l2 = deg2rad($lat2);

		$a = pow(sin($dlat/2),2) + pow(sin($dlong/2),2) * cos($l1) * cos($l2);
		$c = 2 * atan2(sqrt($a), sqrt(1-$a));
		$d = $r * $c;

		return $d;
	}

	if(isset($_GET['ip']))
	{
		$ip = $dblink->real_escape_string($_GET['ip']);
		if(filter_var($ip, FILTER_VALIDATE_IP))
		{
		}
		else
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
	}
	else
	{
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	if(isset($_GET['network']))
	{
		$network = $dblink->real_escape_string($_GET['network']);
	}
	else
	{
		$network = 'A';
	}

	if(isset($_GET['results']))
	{
		$nodecount = $dblink->real_escape_string($_GET['results']);
	}
	else
	{
		$nodecount = 10;
	}

	$info = new stdClass;
	$info->ipAddress = $ip;
	$info->country = '';
	$info->latitude = 0;
	$info->longitude = 0;

	$update = FALSE;
	$query = "SELECT * FROM tblvisitors WHERE ip='$ip'";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	if($result->num_rows==1)
	{
		$row = $result->fetch_row();
		if($row[4]=='')
		{
			$update = TRUE;
		}
		else
		{
			$info->country = $row[4];
			$info->latitude = $row[5];
			$info->longitude = $row[6];
		}
		$query = "UPDATE tblvisitors SET lastseen=NOW(),visits=visits+1 WHERE ip='$ip'";
		$dblink->query($query) OR dberror($dblink,$query);
	}
	else
	{
		$query = "INSERT INTO tblvisitors VALUES('$ip',NOW(),NOW(),1,'',0,0)";
		$dblink->query($query) OR dberror($dblink,$query);
		$update = TRUE;
	}

	if($update)
	{
		$data = get_geoip($ip);
		if($data->errorno==0)
		{
			$cc = $dblink->real_escape_string($data->data->country_code);
			$lat = floatval($data->data->latitude);
			$long = floatval($data->data->longitude);
			$query = "UPDATE tblvisitors SET country='$cc',latitude='$lat',longitude='$long' WHERE ip='$ip'";
			$dblink->query($query) OR dberror($dblink,$query);

			$info->country = $cc;
			$info->latitude = $lat;
			$info->longitude = $long;
		}
	}

	$query = "CREATE TEMPORARY TABLE `tbldistances` (`ip` varchar(15) NOT NULL,`network` int(4) NOT NULL,`country_code` varchar(2) NOT NULL,";
	$query .= "`country_name` varchar(30) NOT NULL,`region_name` varchar(50) NOT NULL,`city` varchar(50) NOT NULL,`latitude` double NOT NULL,";
	$query .= "`longitude` double NOT NULL,`hostname` varchar(50) NOT NULL,`distance` double NOT NULL) ENGINE=MEMORY DEFAULT CHARSET=latin1 PAGE_CHECKSUM=1";
	$dblink->query($query) OR dberror($dblink,$query);
	$query = "ALTER TABLE `tbldistances` ADD PRIMARY KEY (`ip`, `network`)";
	$dblink->query($query) OR dberror($dblink,$query);

	if($network=='A')
	{
		$query = "INSERT INTO tbldistances SELECT ip,network,country_code,country_name,region_name,city,latitude,longitude,hostname,0 FROM tblp2pnodes WHERE public=1 AND seenonupdate=1";
	}
	else
	{
		$query = "INSERT INTO tbldistances SELECT ip,network,country_code,country_name,region_name,city,latitude,longitude,hostname,0 FROM tblp2pnodes WHERE public=1 AND seenonupdate=1 AND network='$network'";
	}
	$dblink->query($query) OR dberror($dblink,$query);

	$query = "SELECT ip,latitude,longitude FROM tbldistances";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{

		$distance = distance($info->latitude,$info->longitude,$row[1],$row[2]);

		//print "$row[1] : $row[2] = $distance \r\n";

		$query = "UPDATE tbldistances SET distance='$distance' WHERE ip='$row[0]'";
		$dblink->query($query) OR dberror($dblink,$query);
	}

	$info->nodes = array();
	$i = 0;

	$query = "SELECT * FROM tbldistances ORDER BY distance,network LIMIT 0,$nodecount";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		$ports[0] = 5578;
		$ports[1] = 5556;
		$ports[2] = 3333;
		$ports[3] = 5589;
		$ports[4] = 5567;

		$port = $ports["$row[1]"];

		$info->nodes[$i] = new stdClass;
		$info->nodes[$i]->network = $row[1];
		if($row[8]!='')
		{
			$info->nodes[$i]->url = "http://$row[8]:$port";
		}
		else
		{
			$info->nodes[$i]->url = "http://$row[0]:$port";
		}
		$info->nodes[$i]->distance = $row[9];
		$info->nodes[$i]->countryCode = $row[2];
		$info->nodes[$i]->country = $row[3];
		$i++;
	}


	header('Content-type: application/json');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

	echo json_encode($info);

?>
