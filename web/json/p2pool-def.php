<?php
require_once('../include/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/database.inc.php');

class p2poolinfo
{
	function __construct()
	{
		global $config;
		$dblink = dbinit();

		$this->Updated = gmdate('D jS M H:i:s e');

		$this->Nodes = get_nodes($dblink);

		$this->Coins = get_coins($dblink);

		$this->Pools = get_pools($dblink,$this->Nodes,$this->Coins);

		$this->Miners = get_miners($dblink,$this->Pools,0);

		$this->Alerts = get_alerts($dblink);

		$this->RecentBlocks = get_blocks($dblink,$this->Coins,12);
	}
}

function get_blocks($dblink,$coins,$count)
{
	$algos[0] = 'SHA256';
	$algos[1] = 'Scrypt';
	$algos[2] = 'Myr-Groestl';
	$algos[3] = 'Skein';
	$algos[4] = 'Qubit';

	$blocks = array();

	$query = "SELECT * FROM tblblocks ORDER BY moment DESC LIMIT 0,$count";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$i=0;
	while($row = $result->fetch_row())
	{
		$i++;
		$blocks[$i] = new stdClass;
		$blocks[$i]->Height = $row[0];
		$blocks[$i]->Hash = $row[14];
		$blocks[$i]->Coin = $row[1];
		$blocks[$i]->Network = $row[13];
		$blocks[$i]->Algorithm = $algos["$row[13]"];
		$blocks[$i]->GenerationTimeRaw = strtotime($row[2]);
		$blocks[$i]->GenerationTime = gmdate('D jS M H:i:s e',$blocks[$i]->GenerationTimeRaw);
		$blocks[$i]->TimeSinceRaw = time()-$blocks[$i]->GenerationTimeRaw;
		$blocks[$i]->TimeSince = seconds2time($blocks[$i]->TimeSinceRaw) . ' ago';
		$blocks[$i]->BlockExplorer = '';
		$blocks[$i]->ActualTimeRaw = $row[4];
		$blocks[$i]->ActualTime = seconds2time($row[4]);
		$blocks[$i]->EstimatedTimeRaw = $row[7];
		$blocks[$i]->EstimatedTime = seconds2time($row[7]);
		$blocks[$i]->LuckRaw = $row[8];
		$blocks[$i]->Luck = sprintf("%01.00f%%",round(100*$row[8],0));
		foreach($coins as $key=>$coin)
		{
			if($coin->CurrencySymbol==$row[1])
			{
				$coinid = $key;
			}
		}
		$blocks[$i]->CoinID = $coinid;
		
		if($row[3]=='orphan')
		{
			$blocks[$i]->Confirmations = 'Orphan';
		}
		else
		{
			if($row[1]=='MYR')
			{
				$confirms = $coins[1]->LastBlock - $row[0] - 1;
				if($confirms>=120)
				{
					$blocks[$i]->Confirmations = 'Confirmed';
				}
				elseif($confirms<0)
				{
					$blocks[$i]->Confirmations = '0 of 120';
				}
				else
				{
					$blocks[$i]->Confirmations = $confirms . ' of 120';
				}
			}
			else
			{
				$query2 = "SELECT mature FROM tblmergedcoins WHERE symbol='$row[1]'";
				$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
				$row2 = $result2->fetch_row();
				if($row[9]>=$row2[0])
				{
					$blocks[$i]->Confirmations = 'Confirmed';
				}
				else
				{
					$blocks[$i]->Confirmations = "$row[9] of $row2[0]";
				}
			}
		}
	}

	return $blocks;
}

function get_miners($dblink,$pools,$nodeid)
{
	$algos[0] = 'SHA256';
	$algos[1] = 'Scrypt';
	$algos[2] = 'Myr-Groestl';
	$algos[3] = 'Skein';
	$algos[4] = 'Qubit';

	$miners = array();
	$global_stats4 = get_p2p_info_db(7,'global_stats',$dblink);  // qubit
 	$global_stats3 = get_p2p_info_db(6,'global_stats',$dblink);  // skein
 	$global_stats2 = get_p2p_info_db(5,'global_stats',$dblink);  // groestl
 	$global_stats0 = get_p2p_info_db(14,'global_stats',$dblink); // SHA256
 	$global_stats1 = get_p2p_info_db(15,'global_stats',$dblink); // Scrypt

	$current_payouts4 = get_p2p_info_db(7,'current_payouts',$dblink);  // qubit
 	$current_payouts3 = get_p2p_info_db(6,'current_payouts',$dblink);  // skein
 	$current_payouts2 = get_p2p_info_db(5,'current_payouts',$dblink);  // groestl
 	$current_payouts0 = get_p2p_info_db(14,'current_payouts',$dblink);  // sha256
 	$current_payouts1 = get_p2p_info_db(15,'current_payouts',$dblink);  // scrypt

	if($nodeid==0)
	{
		$query = "SELECT * FROM tblnodeminers WHERE hashrate>0 ORDER BY address,node";
	}
	else
	{
		$query = "SELECT * FROM tblnodeminers WHERE hashrate>0 AND node=$nodeid ORDER BY address";
	}
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$i=0;
	while($row = $result->fetch_row())
	{
		if($row[8]==4)
		{
			$global_stats = $global_stats4;
			$current_payouts = $current_payouts4;
			$poolid = 4;
		}
		elseif($row[8]==3)
		{
			$global_stats = $global_stats3;
			$current_payouts = $current_payouts3;
			$poolid = 3;
		}
		elseif($row[8]==2)
		{
			$global_stats = $global_stats2;
			$current_payouts = $current_payouts2;
			$poolid = 2;
		}
		elseif($row[8]==1)
		{
			$global_stats = $global_stats1;
			$current_payouts = $current_payouts1;
			$poolid = 1;
		}
		elseif($row[8]==0)
		{
			$global_stats = $global_stats0;
			$current_payouts = $current_payouts0;
			$poolid = 0;
		}

		$query = "SELECT abbr,name FROM tblnodes WHERE id='$row[2]'";
		$result2 = $dblink->query($query) OR dberror($dblink,$query);
		$row2 = $result2->fetch_row();

		$i++;
		$miners[$i] = new stdClass;
		$miners[$i]->id = $row[1];
		$miners[$i]->NodeID = $row[2];
		$miners[$i]->NodeAbbr = $row2[0];
		$miners[$i]->NodeName = $row2[1];
		$miners[$i]->Network = $row[8];
		$miners[$i]->Algorithm = $algos["$row[8]"];
		$miners[$i]->HashRateRaw = $row[4];
		$miners[$i]->HashRate = scale_rate($row[4]);
		$miners[$i]->StaleRateRaw = $row[5];
		$miners[$i]->StaleRate = sprintf("%01.01f kH/s",round($row[5]/1e3,1));
		$miners[$i]->StaleRateProp = sprintf("%01.02f%%",round(($row[5]/$row[4])*100,2));
		$miners[$i]->NonStaleHashRateRaw = $row[6];
		$miners[$i]->NonStaleHashRate = scale_rate($row[6]);
		$miners[$i]->NonStaleHashRateProp = sprintf("%01.02f%%",round($row[6]/$global_stats->pool_nonstale_hash_rate*100,2));
		$shares = get_address_shares($row[1],0,$dblink);
		$miners[$i]->Shares = new stdClass;
		$miners[$i]->Shares->Valid = $shares['valid'];
		$miners[$i]->Shares->Orphan = $shares['orphan'];
		$miners[$i]->Shares->Dead = $shares['doa'];

		$miners[$i]->Payouts = array();
		$miners[$i]->Payouts['MYR'] = new stdClass;

		$miners[$i]->Payouts['MYR']->Address = $row[1];
		$miners[$i]->Payouts['MYR']->Explorer = $pools[$poolid]->AddressExplorer . $row[1];
		if(isset($current_payouts->$row[1]))
		{
			$miners[$i]->Payouts['MYR']->Expected = sprintf("%01.08f MYR",$current_payouts->$row[1]);
			$miners[$i]->Payouts['MYR']->PerDay = sprintf("%01.08f MYR",round($current_payouts->$row[1] * $pools[$poolid]->EstBlocksPerDayRaw,8));
		}
		else
		{
			$miners[$i]->Payouts['MYR']->Expected = sprintf("%01.08f MYR",0);
			$miners[$i]->Payouts['MYR']->PerDay = sprintf("%01.08f MYR",0);
		}
	}

	return $miners;
}

function get_algorithms($dblink)
{
	global $config;
	$algos = array();
	$stats = get_coininfo('MYR',$dblink);

	$algos[0] = new stdClass;
	$algos[0]->Name = 'SHA256d';
	$algos[0]->DifficultyRaw = $stats->difficulty_sha256d;

	$algos[1] = new stdClass;
	$algos[1]->Name = 'Scrypt';
	$algos[1]->DifficultyRaw = $stats->difficulty_scrypt;

	$algos[2] = new stdClass;
	$algos[2]->Name = 'Myr-Groestl';
	$algos[2]->DifficultyRaw = $stats->difficulty_groestl;

	$algos[3] = new stdClass;
	$algos[3]->Name = 'Skein';
	$algos[3]->DifficultyRaw = $stats->difficulty_skein;

	$algos[4] = new stdClass;
	$algos[4]->Name = 'Qubit';
	$algos[4]->DifficultyRaw = $stats->difficulty_qubit;

	for($i=0; $i<=4; $i++)
	{
		$algos[$i]->Difficulty = sprintf("%01.06f",round($algos[$i]->DifficultyRaw,6));
		$algos[$i]->HashRateRaw = (pow(2,32) * $algos[$i]->DifficultyRaw) / 150;
		$algos[$i]->HashRate = scale_rate($algos[$i]->HashRateRaw);
	}

	return $algos;
}


function get_coins($dblink)
{
	$algos[0] = 'SHA256';
	$algos[1] = 'Scrypt';
	$algos[2] = 'Myr-Groestl';
	$algos[3] = 'Skein';
	$algos[4] = 'Qubit';

	global $config;

	$coins = array();

	$dstats = get_coininfo('MYR',$dblink);

	$local_stats = get_p2p_info_db(1,'local_stats',$dblink);
	$currency_info = get_p2p_info_db(1,'web/currency_info',$dblink);

	$query = "SELECT (3600*24*7)/COUNT(hash) FROM tblnetworkblocks WHERE moment>=DATE_ADD(NOW(),INTERVAL (-3600*24*7) SECOND)";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();
	$secsperblock = $row[0];
	
	$i = 1;
	$coins[$i] = new stdClass;
	$coins[$i]->Merged = False;
	$coins[$i]->Name = 'Myriadcoin';
	$coins[$i]->CurrencySymbol = $currency_info->symbol;
// 	$coins[$i]->HashRate = sprintf("%01.02f GH/s",round($dstats->networkhashps/1e9,2));
// 	$coins[$i]->HashRateRaw = $dstats->networkhashps;
	$coins[$i]->Difficulty = sprintf("%01.06f",round($dstats->difficulty,6));
	$coins[$i]->DifficultyRaw = $dstats->difficulty;
	$coins[$i]->LastBlock = $dstats->blocks;
	$coins[$i]->BlockValue = sprintf("%01.08f",$local_stats->block_value);
// 	$coins[$i]->AddressExplorer = $currency_info->address_explorer_url_prefix;
// 	$coins[$i]->BlockExplorer = $currency_info->block_explorer_url_prefix;
 	$coins[$i]->AddressExplorer = 'http://insight-myr.cryptap.us/address/';
 	$coins[$i]->BlockExplorer = 'http://insight-myr.cryptap.us/block/';
 	$BlocksTillHalf = 967680 - ($dstats->blocks % 967680);
 	$coins[$i]->BlocksTillHalf = $BlocksTillHalf . ' (' . sprintf("%01.01f%%",round(100*($dstats->blocks % 967680)/967680,1)) . ')';
 	$coins[$i]->HalvingDate = gmdate('g:ia l, jS F Y e',time() + $secsperblock * $BlocksTillHalf);


 	$query = "SELECT * FROM tblmergedcoins WHERE enabled=1 ORDER BY symbol";
 	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		$i++;
		$coins[$i] = new stdClass;
		$coins[$i]->Merged = True;
		$coins[$i]->Name = $row[3];
		$coins[$i]->CurrencySymbol = $row[2];
		$coins[$i]->Algorithm = $algos["$row[4]"];

		$dstats = get_coininfo($row[2],$dblink, $row[4]);
		$coins[$i]->Difficulty = sprintf("%01.06f",round($dstats->difficulty,6));
		$coins[$i]->DifficultyRaw = $dstats->difficulty;
		if(isset($dstats->networkhashps))
		{
			$coins[$i]->HashRateRaw = $dstats->networkhashps;
			$coins[$i]->HashRate = scale_rate($dstats->networkhashps);
		}
		else
		{
			if($row[2]=='UIS')
			{
				$blocktime = 300;
			}
			else
			{
				$blocktime = 120;
			}
			$coins[$i]->HashRateRaw = (pow(2,32) * $dstats->difficulty) / $blocktime;
			$coins[$i]->HashRate = scale_rate($coins[$i]->HashRateRaw);
		}
		$coins[$i]->LastBlock = $dstats->blocks;

		$query2 = "SELECT value FROM tblblocks WHERE coin='$row[2]' AND status!='orphan' ORDER BY height DESC LIMIT 0,1";
		$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
		if($row2 = $result2->fetch_row())
		{
			$coins[$i]->BlockValue = sprintf("%01.08f",$row2[0]) . " $row[2]";
		}
		else
		{
			$coins[$i]->BlockValue = sprintf("%01.08f",$row[28]) . " $row[2]";
		}
		
		if($coins[$i]->CurrencySymbol=='UIS')
		{
			$coins[$i]->AddressExplorer = 'http://explorer.unitus.info/address/';
			$coins[$i]->BlockExplorer = 'http://explorer.unitus.info/block/';
		}
		else
		{
			$coins[$i]->AddressExplorer = '';
			$coins[$i]->BlockExplorer = '';
		}
		
		$coins[$i]->TimeToBlock = seconds2time(calculate_esttime($dblink,$row[2],$row[4]));
	}

	return $coins;
}






function get_pools($dblink,$nodes,$coins)
{
	$algos[0] = 'SHA256';
	$algos[1] = 'Scrypt';
	$algos[2] = 'Myr-Groestl';
	$algos[3] = 'Skein';
	$algos[4] = 'Qubit';

 	$global_stats[0] = get_p2p_info_db(14,'global_stats',$dblink); // SHA256
 	$global_stats[1] = get_p2p_info_db(15,'global_stats',$dblink); // Scrypt
 	$global_stats[2] = get_p2p_info_db(5,'global_stats',$dblink); // Groestl
 	$global_stats[3] = get_p2p_info_db(6,'global_stats',$dblink); // Skein
	$global_stats[4] = get_p2p_info_db(7,'global_stats',$dblink); // Qubit

	$pools = array();

/*------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/

	for($i=0; $i<=4; $i++)
	{
		$nethash = (pow(2,32) * $global_stats[$i]->network_block_difficulty) / 150;

		$pools[$i] = new stdClass;
		$pools[$i]->Name = $coins[1]->Name;
		$pools[$i]->Algorithm = $algos[$i];
		$pools[$i]->HashRateRaw = $global_stats[$i]->pool_hash_rate;
		//$pools[$i]->HashRate = sprintf("%01.01f MH/s",round($global_stats[$i]->pool_hash_rate/1e6,1));
		$pools[$i]->HashRate = scale_rate($global_stats[$i]->pool_hash_rate);
		$pools[$i]->StaleProp = sprintf("%01.02f%%",round($global_stats[$i]->pool_stale_prop*100,2));
		$pools[$i]->NonStaleHashRate = scale_rate($global_stats[$i]->pool_nonstale_hash_rate);
		$pools[$i]->NonStaleHashRateRaw = $global_stats[$i]->pool_nonstale_hash_rate;
		$pools[$i]->NonStaleHashRateProp = sprintf("%01.02f%%",round($global_stats[$i]->pool_nonstale_hash_rate/$nethash*100,2));
		$pools[$i]->ShareDifficulty = sprintf("%01.06f",round($global_stats[$i]->min_difficulty,6));

		$pools[$i]->EstTimePerBlockRaw = calculate_esttime($dblink,'MYR',$i);
		$pools[$i]->EstTimePerBlock = seconds2time($pools[$i]->EstTimePerBlockRaw);
		$pools[$i]->EstBlocksPerDayRaw = (3600*24) / $pools[$i]->EstTimePerBlockRaw;
		$pools[$i]->EstBlocksPerDay = round($pools[$i]->EstBlocksPerDayRaw,1);

		$query = "SELECT moment FROM tblblocks WHERE coin='MYR' AND network=$i ORDER BY moment DESC LIMIT 0,1";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		if($row = $result->fetch_row())
		{
		 	$pools[$i]->TimeSinceLastBlockRaw = time() - strtotime($row[0]);
		 	$pools[$i]->TimeSinceLastBlock = seconds2time($pools[$i]->TimeSinceLastBlockRaw);
		 	$pools[$i]->Progress = sprintf("%01.00f%%",round($pools[$i]->TimeSinceLastBlockRaw/$pools[$i]->EstTimePerBlockRaw*100,0));
		}
		else
		{
		 	$pools[$i]->TimeSinceLastBlockRaw = 0;
		 	$pools[$i]->TimeSinceLastBlock = 'N/A';
		 	$pools[$i]->Progress = '-';
		}
		$pools[$i]->AddressExplorer = $coins[1]->AddressExplorer;
		$pools[$i]->BlockExplorer = $coins[1]->BlockExplorer;
	}

	return $pools;
}

function get_alerts($dblink)
{
	$query = "SELECT message,level FROM tblalerts WHERE enabled='Y' ORDER BY displayorder";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$i = 0;
	$alerts = array();
	while($row = $result->fetch_row())
	{
		$i++;
		$alerts[$i] = new stdClass;
		$alerts[$i]->message = $row[0];
		$alerts[$i]->level = $row[1];
	}
	return $alerts;
}

function get_nodes($dblink)
{

	$algos[0] = 'SHA256';
	$algos[1] = 'Scrypt';
	$algos[2] = 'Myr-Groestl';
	$algos[3] = 'Skein';
	$algos[4] = 'Qubit';


	$global_stats4 = get_p2p_info_db(7,'global_stats',$dblink);  // qubit
 	$global_stats3 = get_p2p_info_db(6,'global_stats',$dblink);  // skein
 	$global_stats2 = get_p2p_info_db(5,'global_stats',$dblink);  // groestl
 	$global_stats0 = get_p2p_info_db(14,'global_stats',$dblink); // SHA256
 	$global_stats1 = get_p2p_info_db(15,'global_stats',$dblink); // Scrypt

	$query = "SELECT * FROM tblnodes";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$i = 0;
	$nodes = array();
	while($row = $result->fetch_row())
	{
		if($row[21]==4)
		{
			$global_stats = $global_stats4;
		}
		elseif($row[21]==3)
		{
			$global_stats = $global_stats3;
		}
		elseif($row[21]==2)
		{
			$global_stats = $global_stats2;
		}
		elseif($row[21]==1)
		{
			$global_stats = $global_stats1;
		}
		elseif($row[21]==0)
		{
			$global_stats = $global_stats0;
		}

		$i++;
		$shares = get_address_shares('',$row[0],$dblink);

		$nodes[$i] = new stdClass;
		$nodes[$i]->NodeID = $row[0];
		$nodes[$i]->Abbr = $row[1];
		$nodes[$i]->Name = $row[2];
		$nodes[$i]->Network = $row[21];
		$nodes[$i]->Algorithm = $algos["$row[21]"];
		$nodes[$i]->URL = 'http://' . $row[20] . ':' . $row[4] . '/';
		$nodes[$i]->MinerCount = $row[6];
		$nodes[$i]->HashRateRaw = $row[7];
		$nodes[$i]->HashRate = scale_rate($row[7]);
		$nodes[$i]->HashRateProp = sprintf("%01.02f%%",round($row[7]/$global_stats->pool_hash_rate*100,2));
		$nodes[$i]->StaleRateRaw = $row[8];
		$nodes[$i]->StaleRate = sprintf("%01.02f MH/s",round($row[8]/1e6,2));
		if($row[7]>0)
		{
			$nodes[$i]->StaleRateProp = sprintf("%01.01f%%",round($row[8]/$row[7] * 100,1));
		}
		else
		{
			$nodes[$i]->StaleRateProp = sprintf("%01.01f%%",0);
		}
		$nodes[$i]->NonStaleRateRaw = $row[9];
		$nodes[$i]->NonStaleRate = scale_rate($row[9]);
		$nodes[$i]->NonStaleRateProp = sprintf("%01.02f%%",round($row[9]/$global_stats->pool_nonstale_hash_rate*100,2));
		$nodes[$i]->UptimeRaw = $row[10];
		$nodes[$i]->Uptime = seconds2time($row[10]);
		$nodes[$i]->PeersTotal = $row[11] + $row[12];
		$nodes[$i]->PeersInbound = $row[11];
		$nodes[$i]->PeersOutbound = $row[12];
		$nodes[$i]->Fee = sprintf("%01.02f%%",$row[13]);
		$nodes[$i]->FeeRaw = $row[13];
		$nodes[$i]->Version = $row[14];
		$nodes[$i]->Shares = new stdClass;
		$nodes[$i]->Shares->Valid = $shares['valid'];
		$nodes[$i]->Shares->Orphan = $shares['orphan'];
		$nodes[$i]->Shares->Dead = $shares['doa'];
		$nodes[$i]->Efficiency = sprintf("%01.02f%%",round($row[15]*100,2));
		$nodes[$i]->Status = $row[17];
		$dt = new DateTime();
		if($row[19]=='')
		{
			$dt->setTimezone(new DateTimeZone('UTC'));
		}
		else
		{
			$dt->setTimezone(new DateTimeZone($row[19]));
		}
		$nodes[$i]->LocalTime = $dt->format('D jS M H:i:s a T');
		$nodes[$i]->Merged = ($row[23]==1) ? 'Yes' : 'No';
	}
	return $nodes;
}

?>
