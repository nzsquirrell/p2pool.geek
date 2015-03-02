<?php
	require_once('config.inc.php');

	function seconds2time($seconds)
	{
		$seconds = round($seconds/60,0);
		$m = $seconds % 60;
		$seconds = floor($seconds/60);
		$h = $seconds % 24;
		$d = floor($seconds/24);

		if($d>1)
		{
			$r = $d . ' days, ';
		}
		elseif($d==1)
		{
			$r = '1 day, ';
		}
		else
		{
			$r='';
		}
		if($h==1)
		{
			$r .= sprintf("%01.0f hour, ",$h);
		}
		else
		{
			$r .= sprintf("%01.0f hours, ",$h);
		}
		if($m==1)
		{
			$r .= sprintf("%01.0f minute",$m);
		}
		else
		{
			$r .= sprintf("%01.0f minutes",$m);
		}
		return $r;
	}

	function get_coininfo($coin,$dblink,$network=0)
	{
		$query = "SELECT info FROM tblcoininfo WHERE coin='$coin' AND network=$network";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();
		return json_decode($row[0]);
	}



	function get_address_shares($address,$node,$dblink)
	{
		if($address=='')
		{
			$query1 = "SELECT Count(`id`) FROM `tblshares` WHERE `type`='Share' and `stale_info`='' AND `node`='$node'";
			$query2 = "SELECT Count(`id`) FROM `tblshares` WHERE `type`='Share' and `stale_info`='orphan' AND `node`='$node'";
			$query3 = "SELECT Count(`id`) FROM `tblshares` WHERE `type`='Share' and `stale_info`='doa' AND `node`='$node'";
		}
		else
		{
			$query1 = "SELECT Count(`id`) FROM `tblshares` WHERE `payout_address`='$address' AND `type`='Share' and `stale_info`=''";
			$query2 = "SELECT Count(`id`) FROM `tblshares` WHERE `payout_address`='$address' AND `type`='Share' and `stale_info`='orphan'";
			$query3 = "SELECT Count(`id`) FROM `tblshares` WHERE `payout_address`='$address' AND `type`='Share' and `stale_info`='doa'";
		}

		$result = $dblink->query($query1) OR dberror($dblink,$query1);
		$row = $result->fetch_row();
		$shares['valid'] = $row[0];

		$result = $dblink->query($query2) OR dberror($dblink,$query2);
		$row = $result->fetch_row();
		$shares['orphan'] = $row[0];

		$result = $dblink->query($query3) OR dberror($dblink,$query3);
		$row = $result->fetch_row();
		$shares['doa'] = $row[0];

		return $shares;

	}

	function get_p2p_info_db($node,$stat,$dblink)
	{
		$query = "SELECT data FROM tblp2poolinfo WHERE node='$node' AND stat='$stat'";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();
		return json_decode($row[0]);
	}


	function get_coin_lastvalue($dblink,$coin)
	{
		$query = "SELECT value FROM tblblocks WHERE coin='$coin' ORDER BY height DESC LIMIT 0,1";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();
		return $row[0];
	}


	function log_adminaction($dblink,$action,$detail)
	{
		if(isset($_SERVER['REMOTE_USER']))
		{
			$user = $dblink->real_escape_string($_SERVER['REMOTE_USER']);
		}
		else
		{
			$user = 'DEV';
		}
		$address = $dblink->real_escape_string($_SERVER['REMOTE_ADDR']);
		$action = $dblink->real_escape_string($action);
		$detail = $dblink->real_escape_string($detail);

		$query = "INSERT INTO tbladminaudit SET moment=NOW(),user='$user',address='$address',action='$action',detail='$detail'";
		$dblink->query($query) OR dberror($dblink,$query);
	}


	function calculate_esttime($dblink,$coin,$network)
	{
		$query = "SELECT moment FROM tblblocks WHERE coin='$coin' AND network=$network ORDER BY height DESC LIMIT 0,1"; // get time of last block.
		$result = $dblink->query($query) OR dberror($dblink,$query);
		if($row = $result->fetch_row())
		{
			$lastblocktime = $row[0];
		}
		else
		{
			$lastblocktime = date('Y-m-d H:i:s',time()-3600);
			//error_log($lastblocktime);
		}

		$query = "SELECT AVG(difficulty) FROM tbldifficulty WHERE coin='$coin' AND network='$network' AND moment>=DATE_ADD('$lastblocktime',INTERVAL -3 MINUTE)";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();
		if(is_null($row[0]))
		{
			$query = "SELECT difficulty FROM tbldifficulty WHERE coin='$coin' AND network='$network' ORDER BY moment DESC LIMIT 0,1";
			$result = $dblink->query($query) OR dberror($dblink,$query);
			$row = $result->fetch_row();
			$avgdiff = $row[0];
		}
		else
		{
			$avgdiff = $row[0];
		}


		//error_log("$coin = $avgdiff");
		if($coin=='MYR')
		{
			$table = 'tblpoolhashrate';
			$query = "SELECT AVG(hashrate) FROM $table WHERE moment>=DATE_ADD('$lastblocktime',INTERVAL -3 MINUTE) AND network=$network";
			$result = $dblink->query($query) OR dberror($dblink,$query);
			$row = $result->fetch_row();
			$avghash = $row[0];
		}
		else
		{
			$table = 'tblnodehashrate';
			$avghash = 0;
			$query = "SELECT id FROM tblnodes WHERE enabled=1 AND network=$network AND merged=1";
			$result = $dblink->query($query) OR dberror($dblink,$query);
			while($row = $result->fetch_row())
			{
				$query2 = "SELECT AVG(nonstalerate) FROM $table WHERE moment>=DATE_ADD('$lastblocktime',INTERVAL -3 MINUTE) AND node=$row[0]";
				$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
				$row2 = $result2->fetch_row();
				$avghash += $row2[0];
			}
		}
		if($avghash<1)
		{
			$avghash=1;
		}

		$esttime = pow(2,32) * $avgdiff / $avghash;

		//error_log("$coin est. time: $esttime");
		return $esttime;
	}

	function get_xrate($dblink,$src,$dst)
	{
		$query = "SELECT value FROM tblxrates WHERE src='$src' AND dst='$dst'";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();
		return $row[0];
	}


	function scale_rate($rate)
	{
		$output = '';

// 		if($rate<1e3)
// 		{
// 			$output = sprintf("%02.00f H/s",round($rate,0));
// 		}
		if($rate<1e6)
		{
			$output = sprintf("%02.02f kH/s",round($rate/1e3,2));
		}
		elseif($rate<1e9)
		{
			$output = sprintf("%02.02f MH/s",round($rate/1e6,2));
		}
		elseif($rate<1e12)
		{
			$output = sprintf("%02.02f GH/s",round($rate/1e9,2));
		}
		elseif($rate<1e15)
		{
			$output = sprintf("%02.02f TH/s",round($rate/1e12,2));
		}
		else
		{
			$output = sprintf("%02.02f PH/s",round($rate/1e15,2));
		}


		return $output;
	}
?>