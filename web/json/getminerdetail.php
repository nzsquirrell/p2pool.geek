<?php
	require_once('../include/config.inc.php');
	require_once('../include/database.inc.php');
	require_once('../include/functions.inc.php');

	$algos[0] = 'SHA256';
	$algos[1] = 'Scrypt';
	$algos[2] = 'Groestl';
	$algos[3] = 'Skein';
	$algos[4] = 'Qubit';

	$dblink = dbinit();

	if(isset($_GET['address']))
	{
		$address = $dblink->real_escape_string($_GET['address']);
		$query = "SELECT * FROM tblminers WHERE address_vtc='$address'";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		if($result->num_rows==0)
		{
			$address = '';
		}
	}
	else
	{
		$address = '';
	}


	$info = new stdClass;

	$info->Updated = gmdate('D jS M H:i:s e');

	if($address=='')
	{
		$info->Error = 'No valid address supplied.';
	}
	else
	{
		if(isset($_GET['network']))
		{
			$network = $dblink->real_escape_string($_GET['network']);
		}
		else
		{
			$query = "SELECT network FROM tblnodeminers WHERE address='$address' ORDER BY hashrate DESC LIMIT 0,1";
			$resultnet = $dblink->query($query) OR dberror($dblink,$query);
			$rownet = $resultnet->fetch_row();
			$network = $rownet[0];
		}

		if($network == 4)
		{
			$current_payouts = get_p2p_info_db(1,'current_payouts',$dblink);
			$global_stats = get_p2p_info_db(1,'global_stats',$dblink);
		}
		elseif($network == 3)
		{
			$current_payouts = get_p2p_info_db(3,'current_payouts',$dblink);
			$global_stats = get_p2p_info_db(3,'global_stats',$dblink);
		}
		elseif($network == 2)
		{
			$current_payouts = get_p2p_info_db(4,'current_payouts',$dblink);
			$global_stats = get_p2p_info_db(4,'global_stats',$dblink);
		}
		elseif($network == 1)
		{
			$current_payouts = get_p2p_info_db(13,'current_payouts',$dblink);
			$global_stats = get_p2p_info_db(13,'global_stats',$dblink);
		}
		elseif($network == 0)
		{
			$current_payouts = get_p2p_info_db(12,'current_payouts',$dblink);
			$global_stats = get_p2p_info_db(12,'global_stats',$dblink);
		}

		$row = $result->fetch_row();


		$pool_merged_rate = 0;
		$query = "SELECT id FROM tblnodes WHERE enabled=1 AND network=$network AND merged=1";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$query2 = "SELECT nonstalerate FROM tblnodehashrate WHERE node=$row[0] ORDER BY moment DESC LIMIT 0,1";
			$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
			$row2 = $result2->fetch_row();
			$pool_merged_rate += $row2[0];
		}

		$coins = array();

		$info->BTCPerDayRaw = 0;

		$query = "SELECT tblcoininfo.coin,tblcoininfo.info FROM tblcoininfo,tblmergedcoins WHERE tblcoininfo.coin='MYR' OR (tblcoininfo.coin=tblmergedcoins.symbol AND tblmergedcoins.enabled=1 AND tblmergedcoins.network='$network') GROUP BY tblcoininfo.coin";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$coin = $row[0];
			$coins[$coin] = new stdClass;
			if($coin=='MYR')
			{
				$coins[$coin]->Merged = False;
				switch($network)
				{
					case 0:
						$coins[$coin]->Difficulty = json_decode($row[1])->difficulty_sha256d;
						break;
					case 1:
						$coins[$coin]->Difficulty = json_decode($row[1])->difficulty_scrypt;
						break;
					case 2:
						$coins[$coin]->Difficulty = json_decode($row[1])->difficulty_groestl;
						break;
					case 3:
						$coins[$coin]->Difficulty = json_decode($row[1])->difficulty_skein;
						break;
					case 4:
						$coins[$coin]->Difficulty = json_decode($row[1])->difficulty_qubit;
						break;
				}

				$coins[$coin]->PoolHashRate = $global_stats->pool_nonstale_hash_rate;
				$coins[$coin]->TimePerBlock = calculate_esttime($dblink,$coin,$network);

				if(isset($current_payouts->$address))
				{
					$coins[$coin]->PerBlockRaw = $current_payouts->$address;
				}
				else
				{
					$coins[$coin]->PerBlockRaw = 0;
				}
				$coins[$coin]->BlocksPerDay = (24*3600) / $coins[$coin]->TimePerBlock;
				$myrbtc = get_xrate($dblink,'MYR','BTC');

				$coins[$coin]->PerBlock = sprintf("%01.08f MYR",$coins[$coin]->PerBlockRaw);
				$coins[$coin]->PerDay = sprintf("%01.08f MYR",round($coins[$coin]->PerBlockRaw * $coins[$coin]->BlocksPerDay,8));
				$coins[$coin]->PerBlockBTC = sprintf("%01.08f BTC",round($coins[$coin]->PerBlockRaw * $myrbtc,8));
				$coins[$coin]->PerDayBTC = sprintf("%01.08f BTC",round($coins[$coin]->PerBlockRaw * $coins[$coin]->BlocksPerDay * $myrbtc,8));
				$info->BTCPerDayRaw += ($coins[$coin]->PerBlockRaw * $coins[$coin]->BlocksPerDay * $myrbtc);
			}
			else
			{
				$coins[$coin]->Merged = True;
				$coins[$coin]->Difficulty = json_decode($row[1])->difficulty;
				$coins[$coin]->PoolHashRate = $pool_merged_rate;
				$coins[$coin]->TimePerBlock = calculate_esttime($dblink,$coin,$network);

				$coinbtc = get_xrate($dblink,$coin,'BTC');

				$query2 = "SELECT estimate,coinaddress,payout_min,donation,balance FROM tblminercoins WHERE address='$address' AND coin='$coin' AND network='$network'";
				$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
				if($result2->num_rows>0)
				{
					$row2 = $result2->fetch_row();
					$coins[$coin]->PerBlockRaw = $row2[0];
					$coins[$coin]->PayoutAddress = $row2[1];
					$coins[$coin]->PayoutMinimum = sprintf("%01.08f",$row2[2]) . " $coin";
					$coins[$coin]->Donation = sprintf("%01.02f%%",$row2[3]);
					$coins[$coin]->Balance = sprintf("%01.08f",$row2[4]) . " $coin";

					$coins[$coin]->PerBlockBTCRaw = round($row2[0] * $coinbtc,8);
				}
				else
				{
					$coins[$coin]->PerBlockRaw = 0;
					$coins[$coin]->PayoutAddress = '';
					$coins[$coin]->PayoutMinimum = sprintf("%01.08f",0) . " $coin";
					$coins[$coin]->Donation = sprintf("%01.02f%%",0);
					$coins[$coin]->Balance = sprintf("%01.08f",0) . " $coin";
					$coins[$coin]->PerBlockBTCRaw = 0;
				}

				$coins[$coin]->PerBlock = sprintf("%01.08f",$coins[$coin]->PerBlockRaw) . " $coin";
				$coins[$coin]->PerBlockBTC = sprintf("%01.08f",$coins[$coin]->PerBlockBTCRaw) . ' BTC';
				if($coins[$coin]->TimePerBlock)
				{
					$coins[$coin]->BlocksPerDay = (24*3600) / $coins[$coin]->TimePerBlock;
				}
				else
				{
					$coins[$coin]->TimePerBlock = 'N/A';
					$coins[$coin]->BlocksPerDay = 0;
				}
				$coins[$coin]->PerDay = sprintf("%01.08f",round($coins[$coin]->PerBlockRaw * $coins[$coin]->BlocksPerDay,8)) . " $coin";
				$coins[$coin]->PerDayBTC = sprintf("%01.08f",round($coins[$coin]->PerBlockBTCRaw * $coins[$coin]->BlocksPerDay,8)) . ' BTC';
				$info->BTCPerDayRaw += ($coins[$coin]->PerBlockBTCRaw * $coins[$coin]->BlocksPerDay);
			}
		}


		$info->BTCPerDay = sprintf("%01.08f BTC",round($info->BTCPerDayRaw,8));

		$info->Coins = $coins;

		$query = "SELECT hashrate,stalerate,nonstalerate,node,network FROM tblnodeminers WHERE address='$address' ORDER BY hashrate DESC LIMIT 0,1";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();
		$info->HashRateRaw = $row[0];
		$info->StaleRateRaw = $row[1];
		$info->Network = $row[4];

		$info->HashRate = scale_rate($row[0]);
		$info->StaleRate = scale_rate($row[1]);
		if($row[0]>0)
		{
			$info->StaleRateProp = sprintf("%01.02f%%",round(($row[1]/$row[0])*100,2));
		}
		else
		{
			$info->StaleRateProp = sprintf("%01.02f%%",0);
		}
		$info->NonStaleHashRateRaw = $row[2];
		$info->NonStaleHashRate = scale_rate($row[2]);
		$info->NonStaleHashRateProp = sprintf("%01.02f%%",round($row[2]/$global_stats->pool_nonstale_hash_rate*100,2));

		$info->Algorithm = $algos[$network];

		$query = "SELECT name FROM tblnodes WHERE id='$row[3]'";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();
		$info->Node = $row[0];

		$query = "SELECT difficulty FROM tblminerdiff WHERE address='$address' ORDER BY moment DESC LIMIT 0,1";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		if($result->num_rows==1)
		{
			$row = $result->fetch_row();
			$info->DifficultyRaw = $row[0];
			$query = "SELECT AVG(difficulty) FROM tblminerdiff WHERE address='$address' AND moment>=DATE_ADD(NOW(),INTERVAL -1 DAY)";
			$result = $dblink->query($query) OR dberror($dblink,$query);
			$row = $result->fetch_row();
			$info->AverageDifficultyRaw = $row[0];
		}
		else
		{
			$info->DifficultyRaw = $global_stats->min_difficulty;
			$info->AverageDifficultyRaw = $global_stats->min_difficulty;
		}
		$info->Difficulty = sprintf("%01.06f",round($info->DifficultyRaw,6));
		$info->AverageDifficulty = sprintf("%01.06f",round($info->AverageDifficultyRaw,6));

		if($info->NonStaleHashRateRaw>0)
		{
			$time_to_share = pow(2,32) * $info->DifficultyRaw / $info->NonStaleHashRateRaw;
			$info->TimeToShare = seconds2time($time_to_share);
		}
		else
		{
			$info->TimeToShare = 'N/A';
		}


		$info->AccountHistory = array();
		$n = 0;
		$query = "SELECT moment,message FROM tblaccounthistory WHERE address='$address' ORDER BY moment DESC,id DESC LIMIT 0,15";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$info->AccountHistory[$n] = new stdClass;
			$info->AccountHistory[$n]->Date = gmdate('H:ia D jS e',strtotime($row[0]));
			$info->AccountHistory[$n]->Message = $row[1];
			$n++;
		}

		$info->TransactionHistory = array();
		$n = 0;
		$query = "SELECT moment,coin,amount,message,block FROM tblpayments WHERE address='$address' AND network=$network ORDER BY moment DESC,block DESC,id DESC LIMIT 0,30";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$info->TransactionHistory[$n] = new stdClass;
			$info->TransactionHistory[$n]->Date = gmdate('H:ia D jS e',strtotime($row[0]));
			$info->TransactionHistory[$n]->Coin = $row[1];
			$info->TransactionHistory[$n]->Amount = sprintf("%01.08f",$row[2]);
			$info->TransactionHistory[$n]->Message = $row[3];
			$info->TransactionHistory[$n]->Block = ($row[4] == 0) ? '' : $row[4];
			$n++;
		}

		$info->EarningsHistory = array();
		$query = "SELECT coin,SUM(amount) FROM tblpayments WHERE address='$address' AND network=$network AND message='Payment' GROUP BY coin";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$coin = $row[0];
			$info->EarningsHistory[$coin] = new stdClass;
			$info->EarningsHistory[$coin]->Payments = sprintf("%01.08f",$row[1]);
			$info->EarningsHistory[$coin]->Fees = sprintf("%01.08f",0);
			$info->EarningsHistory[$coin]->Donations = sprintf("%01.08f",0);
			$info->EarningsHistory[$coin]->Payouts = sprintf("%01.08f",0);
		}
		$query = "SELECT coin,ABS(SUM(amount)) FROM tblpayments WHERE address='$address' AND network=$network AND message='Fee' GROUP BY coin";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$coin = $row[0];
			$info->EarningsHistory[$coin]->Fees = sprintf("%01.08f",$row[1]);
		}
		$query = "SELECT coin,ABS(SUM(amount)) FROM tblpayments WHERE address='$address' AND network=$network AND message='Donation' GROUP BY coin";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$coin = $row[0];
			$info->EarningsHistory[$coin]->Donations = sprintf("%01.08f",$row[1]);
		}
		$query = "SELECT coin,ABS(SUM(amount)) FROM tblpayments WHERE address='$address' AND network=$network AND message='Pay Out' GROUP BY coin";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$coin = $row[0];
			$info->EarningsHistory[$coin]->Payouts = sprintf("%01.08f",$row[1]);
		}
	}



	header('Content-type: application/json');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');


	echo json_encode($info);
?>
