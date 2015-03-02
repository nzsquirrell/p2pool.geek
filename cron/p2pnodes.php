<?php

	require_once('config.inc.php');
	require_once('database.inc.php');

	$dblink = dbinit();

	update_network($dblink,0,5578);
 	update_network($dblink,1,5556);
 	update_network($dblink,2,3333);
 	update_network($dblink,3,5589);
 	update_network($dblink,4,5567);

	$ports[0] = 5578;
	$ports[1] = 5556;
	$ports[2] = 3333;
	$ports[3] = 5589;
	$ports[4] = 5567;


	function get_p2pinfo_curl($hostname,$port,$location)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://' . $hostname . ':' . $port . "/$location");
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

	function update_network($dblink,$network,$port)
	{

		$query = "UPDATE tblp2pnodes SET seenonupdate=0 WHERE network=$network";
		$dblink->query($query) OR dberror($dblink,$query);

		print "Network $network - Starting Phase 1.\r\n-----------------------------------------------------------------------------\r\n";
		$n=0;
		$polls = 0;

		$local_node = get_p2pinfo_curl('p2poolnodeserver',$port,'peer_addresses');
		if($local_node->errorno==0)
		{
			//var_dump($local_node->data);
			$nodes = explode(' ',$local_node->data);
			foreach($nodes as $node)
			{
				$parts = explode(':',$node);

				if(substr($parts[0],0,3)!='10.' && substr($parts[0],0,8)!='192.168.') // filter out private ip addresses;
				{
					$n++;
					$query = "SELECT id FROM tblp2pnodes WHERE ip='$parts[0]' AND network=$network";
					$result = $dblink->query($query) OR dberror($dblink,$query);
					if($result->num_rows==1)
					{
						$row = $result->fetch_row();
						$query = "UPDATE tblp2pnodes SET lastseen=NOW(),polled=0,seenonupdate=1 WHERE id='$row[0]'";
						$dblink->query($query) OR dberror($dblink,$query);
					}
					else
					{
						print "New IP Address: $parts[0].\r\n";
						$query = "INSERT INTO tblp2pnodes SET network=$network,ip='$parts[0]',lastseen=NOW(),polled=0,seenonupdate=1";
						$dblink->query($query) OR dberror($dblink,$query);
					}
				}
			}
		}
		print "Network $network - Phase 1 - $n Nodes polled.\r\n";
		$polls += $n;

		print "\r\nNetwork $network - Starting Phase 2.\r\n-----------------------------------------------------------------------------\r\n";
		$new_polls = find_nodes($dblink,$network,$port);
		print "Network $network - Phase 2 - $new_polls Nodes polled.\r\n";
		$polls += $new_polls;

		print "\r\nNetwork $network - Starting Phase 3.\r\n-----------------------------------------------------------------------------\r\n";
		$new_polls = find_nodes($dblink,$network,$port);
		print "Network $network - Phase 3 - $new_polls Nodes polled.\r\n";
		$polls += $new_polls;

		print "\r\nNetwork $network - Starting Phase 4.\r\n-----------------------------------------------------------------------------\r\n";
		$new_polls = find_nodes($dblink,$network,$port);
		print "Network $network - Phase 4 - $new_polls Nodes polled.\r\n";
		$polls += $new_polls;

		print "\r\nNetwork $network - Starting Phase 5.\r\n-----------------------------------------------------------------------------\r\n";
		$new_polls = find_nodes($dblink,$network,$port);
		print "Network $network - Phase 5 - $new_polls Nodes polled.\r\n";
		$polls += $new_polls;

		print "\r\n";
		print "4 Phases complete, a total of $polls nodes polled.\r\n";

		update_locations($dblink,$network);

		print "Updating statistics for each node.\r\n";

		$c = update_public_nodes($dblink,$network,$port);

		print "Update stats for $c public nodes.\r\n";


	}

	function update_locations($dblink,$network)
	{
		$query = "SELECT id,ip FROM tblp2pnodes WHERE network=$network AND country_code=''";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			print "Updating location information for node $row[1]\r\n";
			$data = get_p2pinfo_curl('freegeoip.net',80,"json/$row[1]");
			if($data->errorno==0)
			{
				$cc = $dblink->real_escape_string($data->data->country_code);
				$cn = $dblink->real_escape_string($data->data->country_name);
				$rc = intval($data->data->region_code);
				$rn = $dblink->real_escape_string($data->data->region_name);
				$city = $dblink->real_escape_string($data->data->city);
				$zip = $dblink->real_escape_string($data->data->zipcode);
				$lat = floatval($data->data->latitude);
				$long = floatval($data->data->longitude);
				$mc = $dblink->real_escape_string($data->data->metro_code);
				$ac = $dblink->real_escape_string($data->data->area_code);
				$query = "UPDATE tblp2pnodes SET country_code='$cc',country_name='$cn',region_code='$rc',region_name='$rn',city='$city',zipcode='$zip',latitude='$lat',longitude='$long',metro_code='$mc',area_code='$ac' WHERE id=$row[0]";
				$dblink->query($query) OR dberror($dblink,$query);
			}
		}
	}

	function update_public_nodes($dblink,$network,$port)
	{

		$query = "SELECT id,ip FROM tblp2pnodes WHERE network=$network AND public=1";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$data = get_p2pinfo_curl($row[1],$port,'local_stats');
			if($data->errorno==0)
			{
				$fee = $data->data->fee;
				$uptime = $data->data->uptime;
				$miners = 0;
				$hashrate = 0;
				foreach($data->data->miner_hash_rates as $address=>$rate)
				{
					$miners++;
					$hashrate+=$rate;
				}

				$query = "UPDATE tblp2pnodes SET fee='$fee',uptime='$uptime',miners=$miners,hashrate='$hashrate' WHERE id=$row[0]";
				$dblink->query($query) OR dberror($dblink,$query);
			}
		}
		return $row = $result->num_rows;
	}


	function find_nodes($dblink,$network,$port)
	{

		$polls = 0;
		$query = "SELECT id,ip FROM tblp2pnodes WHERE polled=0 AND network=$network";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			print "Polling $row[1] ...";
			$data = get_p2pinfo_curl($row[1],$port,'peer_addresses');
			$polls++;
			if($data->errorno==0)
			{
				$nodes = explode(' ',$data->data);
				$count = count($nodes);
				print "... response received, $count nodes.\r\n";
				$query = "UPDATE tblp2pnodes SET public=1,polled=1,lastseen=NOW(),seenonupdate=1 WHERE id=$row[0]";
				$dblink->query($query) OR dberror($dblink,$query);


				foreach($nodes as $node)
				{
					$parts = explode(':',$node);

					if(substr($parts[0],0,3)!='10.' && substr($parts[0],0,8)!='192.168.') // filter out private ip addresses;
					{
						$query2 = "SELECT id,seenonupdate FROM tblp2pnodes WHERE ip='$parts[0]' AND network=$network";
						$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
						if($result2->num_rows==1)
						{
							$row2 = $result2->fetch_row();
							if($row2[1]==0)
							{
								$query = "UPDATE tblp2pnodes SET lastseen=NOW(),polled=0,seenonupdate=1 WHERE id='$row2[0]'";
								$dblink->query($query) OR dberror($dblink,$query);
							}
						}
						else
						{
							print "New IP Address: $parts[0].\r\n";
							$query = "INSERT INTO tblp2pnodes SET network=$network,ip='$parts[0]',lastseen=NOW(),polled=0,seenonupdate=1";
							$dblink->query($query) OR dberror($dblink,$query);
						}
					}
				}
			}
			else
			{
				print "... no response.\r\n";
				$query = "UPDATE tblp2pnodes SET public=0,polled=1,lastseen=NOW(),seenonupdate=1 WHERE id=$row[0]";
				$dblink->query($query) OR dberror($dblink,$query);
			}
		}
		return $polls;
	}


?>
