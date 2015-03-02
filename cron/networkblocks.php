<?php

	require_once('config.inc.php');
 	require_once('database.inc.php');
	require_once('rpc.php');

 	$dblink = dbinit();

 	$tableName = "tblnetworkblocks";

	$wallet['rpcscheme'] = 'http';
	$wallet['rpcport'] = 10889;
	$wallet['rpchost'] = 'walletaddress';
	$wallet['rpcuser'] = 'rpcusername';
	$wallet['rpcpass'] = 'rpcpassword';
	$wallet['rpckey'] = '';

	function rpc_getblock($wallet,$hash)
	{
		$command = '{"jsonrpc": "1.0", "id":"curltest", "method": "getblock", "params": [ "' . $hash . '" ] }';
		$result = rpc_base($wallet,$command);
		return $result;
	}

	function rpc_getpeerinfo($wallet)
	{
		$command = '{"jsonrpc": "1.0", "id":"curltest", "method": "getpeerinfo", "params": [  ] }';
		$result = rpc_base($wallet,$command);
		return $result;
	}



	print "MYR| " . date("H:i:s");

	$info = rpc_getinfo($wallet);
	if($info->status == 'SUCCESS')
	{
		$myrinfo = json_encode($info->json_output->result);
		$query = "UPDATE tblmiscdata SET updated=NOW(),data='$myrinfo' WHERE name='myrinfo'";
		$dblink->query($query) OR dberror($dblink,$query);
		$blocks = $info->json_output->result->blocks;
		print " Best Block: $blocks.";
	}
	else
	{
		echo $info->statusmsg;
	}



	$query = "SELECT hash,height FROM $tableName ORDER BY height DESC LIMIT 0,1";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	if($result->num_rows==0)
	{
		$hash = '24eebf12179a096345be77cc7f719f5e1a7b34222bf805a224c5df7846364918';
	}
	else
	{
		$row = $result->fetch_row();
		$hash = $row[0];
		$info = rpc_getblock($wallet,$hash);
		if($info->status == 'SUCCESS')
		{
			if(isset($info->json_output->result->nextblockhash))
			{
				$hash = $info->json_output->result->nextblockhash;
			}
			else
			{
				if($row[1]<$blocks) // is best block in DB less than blockchain, yet no next hash available? Orphan found in last block
				{
					// delete top block from DB
					print " Deleting Orphan block $hash.";
					$query = "DELETE FROM $tableName WHERE hash='$hash'";
					$dblink->query($query) OR dberror($dblink,$query);

					$query = "SELECT hash,height FROM $tableName ORDER BY height DESC LIMIT 0,1";
					$result = $dblink->query($query) OR dberror($dblink,$query);
					$row = $result->fetch_row();
					$hash = $row[0];
					$info = rpc_getblock($wallet,$hash);
					if($info->status == 'SUCCESS')
					{
						if(isset($info->json_output->result->nextblockhash))
						{
							$hash = $info->json_output->result->nextblockhash;
						}
						else
						{
							$hash = '';
						}
					}
				}
				else
				{
					$hash = '';
				}
			}
		}
		else
		{
			echo $info->statusmsg;
			$hash = '';
		}
	}

	$algo_last = array();
	$last = 0;
	for($a=0; $a<=4; $a++)
	{
		$query = "SELECT time FROM $tableName WHERE pow_algo=$a ORDER BY height DESC LIMIT 0,1";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();
		$algo_last[$a] = $row[0];
		if($row[0]>$last)
		{
			$last = $row[0];
		}
	}


	while($hash!='')
	{
		$info = rpc_getblock($wallet,$hash);
		if($info->status == 'SUCCESS')
		{
			//var_dump($info->json_output);
			$height = $info->json_output->result->height;
			$algo = $info->json_output->result->pow_algo_id;
			$pow_hash = $info->json_output->result->pow_hash;
			$time = $info->json_output->result->time;
			$diff = $info->json_output->result->difficulty;
			$nonce = $info->json_output->result->nonce;
			$algo_elapsed = $time - $algo_last[$algo];
			$algo_last[$algo] = $time;
			$elapsed = $time - $last;
			$last = $time;

			$query = "INSERT INTO $tableName VALUES('$hash',$height,$algo,'$pow_hash',$diff,$time,FROM_UNIXTIME($time),$elapsed,$algo_elapsed,$nonce)";
			$dblink->query($query) OR dberror($dblink,$query);

			print " $height";

			if(isset($info->json_output->result->nextblockhash))
			{
				$hash = $info->json_output->result->nextblockhash;
			}
			else
			{
				$hash = '';
			}
		}
		else
		{
			echo $info->statusmsg;
			$hash = '';
		}
	}

	print "\r\n";

	$peers = rpc_getpeerinfo($wallet);

	if($peers->status == 'SUCCESS')
	{
		foreach($peers->json_output->result as $peer)
		{
			$ip = trim(substr($peer->addr,0,strpos($peer->addr,':')));
			if($ip!='127.0.0.1' && substr($ip,0,9)!='172.16.55')
			{
				if($peer->inbound)
				{
					$dir = "Inbound";
				}
				else
				{
					$dir = "Outbound";
				}
				$version = trim(substr($peer->subver,strpos($peer->subver,':')+1)," \t\n\r\0\x0B/");
				//print "IP: $ip, Direction: $dir, Version: $version \r\n";

				$query = "SELECT address FROM tblmyrpeers WHERE address = '$ip'";
				$result = $dblink->query($query) OR dberror($dblink,$query);
				if($result->num_rows==0)
				{
					$query = "INSERT INTO tblmyrpeers VALUES('$ip','$dir','$version',NOW(),1)";
				}
				else
				{
					$query = "UPDATE tblmyrpeers SET direction='$dir',version='$version',lastseen=NOW(),connected=1 WHERE address='$ip'";
				}
				$dblink->query($query) OR dberror($dblink,$query);
			}
		}

		$query = "UPDATE tblmyrpeers SET connected=0 WHERE lastseen<DATE_ADD(NOW(),INTERVAL -5 MINUTE)";
		$dblink->query($query) OR dberror($dblink,$query);
	}
	else
	{
		echo $peers->statusmsg;
	}



?>
