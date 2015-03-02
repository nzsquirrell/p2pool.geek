<?php
	require_once('backend_functions.php');

	function update_node($local_stats,$nodeid,$dblink)
	{
		$peers_out = $local_stats->peers->outgoing;
		$peers_in = $local_stats->peers->incoming;
		if($local_stats->efficiency == null)
		{
			$efficiency = 0;
		}
		else
		{
			$efficiency = $local_stats->efficiency;
		}
		$hashrate = 0;
		$stalerate = 0;
		$miners = 0;

		$query = "SELECT network FROM tblnodes WHERE id='$nodeid'";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();
		$network = $row[0];

		$query = "UPDATE tblnodeminers SET updateflag=0 WHERE node='$nodeid'";
		$dblink->query($query) OR dberror($dblink,$query);

		foreach($local_stats->miner_hash_rates as $address => $rate)
		{
			if(strlen($address)==34)
			{
				$hashrate += $rate;
				$miners++;
				$rate_stale = 0;
				if(isset($local_stats->miner_dead_hash_rates->$address))
				{
					$stalerate += $local_stats->miner_dead_hash_rates->$address;
					$rate_stale = $local_stats->miner_dead_hash_rates->$address;
				}
				$rate_nonstale = $rate - $rate_stale;

				$query = "SELECT id FROM tblnodeminers WHERE address='$address' AND node=$nodeid";
				$result = $dblink->query($query) OR dberror($dblink,$query);
				if($result->num_rows==0)
				{
					$query = "INSERT INTO tblnodeminers SET address='$address',node=$nodeid,lastseen=NOW(),hashrate='$rate',stalerate='$rate_stale',nonstalerate='$rate_nonstale',updateflag=1,network=$network";
				}
				else
				{
					$row = $result->fetch_row();
					$query = "UPDATE tblnodeminers SET lastseen=NOW(),hashrate='$rate',stalerate='$rate_stale',nonstalerate='$rate_nonstale',updateflag=1,network=$network WHERE id=$row[0]";
				}
				$dblink->query($query) OR dberror($dblink,$query);
				$query = "SELECT address_vtc FROM tblminers WHERE address_vtc='$address'";
				$result = $dblink->query($query) OR dberror($dblink,$query);
				if($result->num_rows==0)
				{
					$query = "INSERT INTO tblminers SET address_vtc='$address',address_mon='',address_plx='',lastseen=NOW(),lastupdate=NOW(),notes=''";
				}
				else
				{
					$query = "UPDATE tblminers SET lastseen=NOW() WHERE address_vtc='$address'";
				}
				$dblink->query($query) OR dberror($dblink,$query);

				$query = "SELECT symbol,network,payout_min,payout_max FROM tblmergedcoins WHERE enabled=1";
				$result = $dblink->query($query) OR dberror($dblink,$query);
				while($row = $result->fetch_row())
				{
					$query2 = "SELECT * FROM tblminercoins WHERE address='$address' AND coin='$row[0]' AND network=$row[1]";
					$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
					if($result2->num_rows==0)
					{
						$query = "INSERT INTO tblminercoins SET address='$address',coin='$row[0]',network=$row[1],payout_min=$row[2]";
						$dblink->query($query) OR dberror($dblink,$query);
					}
				}

			}
		}

		$query = "UPDATE tblnodeminers SET hashrate='0',stalerate='0',nonstalerate='0' WHERE updateflag=0 AND node='$nodeid'";
		$dblink->query($query) OR dberror($dblink,$query);


		$nonstalerate = $hashrate - $stalerate;


		$query = "UPDATE tblnodes SET lastupdate=NOW(),uptime='$local_stats->uptime',peers_in='$peers_in',peers_out='$peers_out'";
		$query .= ",activeMiners='$miners',hashrate='$hashrate',stalerate='$stalerate',nonstalerate='$nonstalerate'";
		$query .= ",fee='$local_stats->fee',version='$local_stats->version',efficiency='$efficiency',status='Online',failures=0";
		$query .= " WHERE id=$nodeid";
		$dblink->query($query) OR dberror($dblink,$query);
	}

	function update_p2pgraphs($dblink)
	{
		$message = "P2P graph update starting.\r\n";

		$query = "SELECT id,hostname,port,failures FROM tblnodes WHERE status='Online' AND enabled=1";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$nodeid = $row[0];
			$nodeinfo = new stdClass;
			$nodeinfo->hostname = $row[1];
			$nodeinfo->port = $row[2];

			$state = 'UNKNOWN';

			$query2 = "SELECT * FROM tblp2poolinfo WHERE stat LIKE 'web/%' AND node='$nodeid'";
			$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
			while($row2 = $result2->fetch_row())
			{
				$t = strtotime($row2[3]); // get unix time of last update
				if((time()-$t)>($row2[4]-15)) // is time since last update greater than frequency?
				{
					if($state == 'UNKNOWN')
					{
						$message .= "Checking for a response from node $nodeid (" . $nodeinfo->hostname . ")...";
						$m = microtime(true);
						$data = get_p2pinfo_curl('fee',$nodeinfo);
						$delay = sprintf("%01.02f",round(microtime(true) - $m,2));
						if($data != NULL || $data == 0)
						{
							$message .= "... Received a response in $delay seconds.\r\n";
							$state = 'ALIVE';
						}
						else
						{
							$message .= "... No valid response received. Marking as dead.\r\n";
							$state = 'DEAD';
						}
					}

					if($state == 'ALIVE')
					{
						$m = microtime(true);
						$data = get_p2pinfo_curl($row2[1],$nodeinfo);
						$delay = sprintf("%01.02f",round(microtime(true) - $m,2));
						if($data != NULL)
						{
							$message .= "Retreived $row2[1] from node $nodeid (" . $nodeinfo->hostname . ") in $delay seconds.\r\n";
							store_stat($data,$nodeid,$row2[1],$dblink);
						}
						else
						{
							$message .= "Failed to retreive $row2[1] from node $nodeid (" . $nodeinfo->hostname . ").\r\n";
						}
					}
				}
			}
		}
		return $message;
	}


	function update_p2pinfo($dblink)
	{
		$algos[0] = 'SHA256';
		$algos[1] = 'Scrypt';
		$algos[2] = 'Myr-Groestl';
		$algos[3] = 'Skein';
		$algos[4] = 'Qubit';
	
		$message = "P2P node update starting.\r\n";

		$nodeinfo = array();
		$query = "SELECT id,hostname,port,failures,network FROM tblnodes";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$nodeid = $row[0];
			$nodeinfo[$nodeid] = new stdClass;
			$nodeinfo[$nodeid]->hostname = $row[1];
			$nodeinfo[$nodeid]->port = $row[2];
			$network = $row[4];
			$algo = $algos[$network];
			
			$m = microtime(true);
			$data = get_p2pinfo_curl('local_stats',$nodeinfo[$nodeid]);
			$delay = sprintf("%01.02f",round(microtime(true) - $m,2));

			if($data != NULL)
			{
				$message .= "Retrieved ($algo Local) local_stats from node $nodeid (" . $nodeinfo[$nodeid]->hostname . ") in $delay seconds.\r\n";

				store_stat($data,$nodeid,'local_stats',$dblink); // store info to tblp2poolinfo
				update_node($data,$nodeid,$dblink); // save node info

				if($nodeid==7 || $nodeid==6 || $nodeid==5 || $nodeid==14 || $nodeid==15) // do critical updates for node 1, 3, 4.
				{
					$criticals = array('global_stats','current_payouts','recent_blocks','web/currency_info');
					foreach($criticals as $stat)
					{
						$m = microtime(true);
						$data = get_p2pinfo_curl($stat,$nodeinfo[$nodeid]);
						$delay = sprintf("%01.02f",round(microtime(true) - $m,2));
						store_stat($data,$nodeid,$stat,$dblink);
						$message .= "Retrieved ($algo Global) $stat from node $nodeid (" . $nodeinfo[$nodeid]->hostname . ") in $delay seconds.\r\n";
					}
				}

				if($nodeid==7 || $nodeid==6 || $nodeid==5 || $nodeid==14 || $nodeid==15) // get payouts for each network.
				{
					$m = microtime(true);
					$payouts = json_encode(get_p2pinfo_curl('patron_sendmany/100',$nodeinfo[$nodeid]));
					$delay = sprintf("%01.02f",round(microtime(true) - $m,2));
					$query = "INSERT INTO tblminerpayout VALUES(NOW(),$network,'$payouts')";
					$dblink->query($query) OR dberror($dblink,$query);
					$message .= "Retrieved ($algo Global) payouts from node $nodeid (" . $nodeinfo[$nodeid]->hostname . ") in $delay seconds.\r\n";
				}
			}
			else
			{
				$message .= "No data received when pulling local_stats from node $nodeid (" . $nodeinfo[$nodeid]->hostname . ").\r\n";

				$failures = $row[3] + 1; // increase the failure count for this node.
				if($failures>4)
				{
					// assume node is offline - set all miners on that node to 0
					$query = "UPDATE tblnodeminers SET hashrate=0,stalerate=0,nonstalerate=0 WHERE node='$nodeid'";
					$dblink->query($query) OR dberror($dblink,$query);
					// set the node as offline with 0 hashrate
					$query = "UPDATE tblnodes SET status='Offline',failures='$failures',activeMiners=0,hashrate=0,stalerate=0,nonstalerate=0,uptime=0,peers_in=0,peers_out=0,efficiency=0 WHERE id='$nodeid'";
					$message .= "Node $nodeid state is Offline after $failures failed attempts to contact.\r\n";

					// log availability of node as 0 seem it really is down...
// 					$query = "INSERT INTO tblnodeavailability VALUES(NOW(),$nodeid,0)";
// 					$dblink->query($query) OR dberror($dblink,$query);
				}
				elseif($failures>1)
				{
					// node is in an unknown state
					$query = "UPDATE tblnodes SET status='Unknown',failures='$failures' WHERE id='$nodeid'";
					$message .= "Node $nodeid state is Unknown after $failures failed attempts to contact.\r\n";

					// log availability of node as 0 seem this is the second/third/fourth time we can't talk to it.
// 					$query = "INSERT INTO tblnodeavailability VALUES(NOW(),$nodeid,0)";
// 					$dblink->query($query) OR dberror($dblink,$query);

				}
				else
				{
					$query = "UPDATE tblnodes SET failures='$failures' WHERE id='$nodeid'";
					//$message .= "Node $nodeid state is Unknown after $failures failed attempts to contact.\r\n";
				}
				$dblink->query($query) OR dberror($dblink,$query);
			}
		}



		$message .= "Update complete.\r\n";
		return $message;
	}




	function store_stat($data,$node,$stat,$dblink)
	{
		if(isset($data) AND $data!='')
		{
			$query = "SELECT * FROM tblp2poolinfo WHERE node=$node AND stat='$stat'";
			$result = $dblink->query($query) OR dberror($dblink,$query);
			if($result->num_rows==0)
			{
				$query = "INSERT INTO tblp2poolinfo SET node=$node,stat='$stat',lastupdate=NOW(),data='" . $dblink->real_escape_string(json_encode($data)) . "',updatefreq=60";
			}
			else
			{
				$query = "UPDATE tblp2poolinfo SET lastupdate=NOW(),data='" . $dblink->real_escape_string(json_encode($data)) . "' WHERE node='$node' AND stat='$stat'";
			}
			$dblink->query($query) OR dberror($dblink,$query);
		}
	}


?>
