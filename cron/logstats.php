<?php
	require_once('backend_functions.php');
	require_once('rpc.php');

	function log_diff($dblink)
	{
		global $config;
		$diff_qubit = 'Unavailable';
		$diff_skein = 'Unavailable';
		$diff_groestl = 'Unavailable';
		$diff_sha256 = 'Unavailable';
		$diff_scrypt = 'Unavailable';

		$stats = vtc_mininginfo();
		if($stats->status == 'SUCCESS')
		{
			$diff_groestl = $stats->json_output->result->difficulty_groestl;
			$diff_skein = $stats->json_output->result->difficulty_skein;
			$diff_qubit = $stats->json_output->result->difficulty_qubit;
			$diff_sha256 = $stats->json_output->result->difficulty_sha256d;
			$diff_scrypt = $stats->json_output->result->difficulty_scrypt;

			$query = "INSERT INTO tbldifficulty VALUES(NOW(6),'MYR','$diff_sha256',0)";
			$dblink->query($query) OR dberror($dblink,$query);
			$query = "INSERT INTO tbldifficulty VALUES(NOW(6),'MYR','$diff_scrypt',1)";
			$dblink->query($query) OR dberror($dblink,$query);
			$query = "INSERT INTO tbldifficulty VALUES(NOW(6),'MYR','$diff_groestl',2)";
			$dblink->query($query) OR dberror($dblink,$query);
			$query = "INSERT INTO tbldifficulty VALUES(NOW(6),'MYR','$diff_skein',3)";
			$dblink->query($query) OR dberror($dblink,$query);
			$query = "INSERT INTO tbldifficulty VALUES(NOW(6),'MYR','$diff_qubit',4)";
			$dblink->query($query) OR dberror($dblink,$query);

			$info = json_encode($stats->json_output->result);
			$query = "UPDATE tblcoininfo SET lastupdate=NOW(),info='$info' WHERE coin='MYR'";
			$dblink->query($query) OR dberror($dblink,$query);
		}

		$msg = 'MYR SHA256 Difficulty: ' . $diff_sha256 . "\r\n";
		$msg .= 'MYR Scrypt Difficulty: ' . $diff_scrypt . "\r\n";
		$msg .= 'MYR Groestl Difficulty: ' . $diff_groestl . "\r\n";
		$msg .= 'MYR Skein Difficulty: ' . $diff_skein . "\r\n";
		$msg .= 'MYR Qubit Difficulty: ' . $diff_qubit . "\r\n";

		$query = "SELECT * FROM tblmergedcoins WHERE enabled=1 AND rpcenabled=1";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$network = $row[4];
			$wallet['rpcscheme'] = $row[5];
			$wallet['rpchost'] = $row[6];
			$wallet['rpcport'] = $row[7];
			$wallet['rpcuser'] = $row[8];
			$wallet['rpcpass'] = $row[9];
			$wallet['rpckey'] = '';

			$balance = 0;

			if($row[2]=='HUC' || $row[2]=='NMC')
			{
				$info = rpc_getinfo($wallet);
				if($info->status == 'SUCCESS')
				{
					$balance = $info->json_output->result->balance;
				}
			}
			else
			{
				$info = rpc_mininginfo($wallet);
				if($info->status == 'SUCCESS')
				{
					$balance = rpc_getinfo($wallet)->json_output->result->balance;
				}

			}

			if($info->status == 'SUCCESS')
			{
				$diff = $info->json_output->result->difficulty;
				$query = "INSERT INTO tbldifficulty VALUES(NOW(6),'$row[2]','$diff','$row[4]')";
				$dblink->query($query) OR dberror($dblink,$query);
				$query = "UPDATE tblcoininfo SET lastupdate=NOW(),info='" . $dblink->real_escape_string(json_encode($info->json_output->result)) . "',balance='$balance' WHERE coin='$row[2]' AND network=$network";
				$dblink->query($query) OR dberror($dblink,$query);

				$msg .= "$row[2] Difficulty: $diff\r\n";
			}
			else
			{
				$msg .= "Failed to get $row[2] Difficulty: " . $info->statusmsg . "\r\n";
			}
		}


		return $msg;
	}

	function log_hashrate($dblink)
	{
		global $config;
		$message = "";

		$global_stats = get_p2p_info_db(14,'global_stats',$dblink);
		$query = "INSERT INTO tblpoolhashrate SET moment=NOW(6),hashrate='$global_stats->pool_nonstale_hash_rate',network=0";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$message .= 'P2Pool SHA256d Hashrate: ' . $global_stats->pool_nonstale_hash_rate . "\r\n";
		$p2p_sha256 = $global_stats->pool_nonstale_hash_rate;

		$global_stats = get_p2p_info_db(15,'global_stats',$dblink);
		$query = "INSERT INTO tblpoolhashrate SET moment=NOW(6),hashrate='$global_stats->pool_nonstale_hash_rate',network=1";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$message .= 'P2Pool Scrypt Hashrate: ' . $global_stats->pool_nonstale_hash_rate . "\r\n";
		$p2p_scrypt = $global_stats->pool_nonstale_hash_rate;

		$global_stats = get_p2p_info_db(5,'global_stats',$dblink);
		$query = "INSERT INTO tblpoolhashrate SET moment=NOW(6),hashrate='$global_stats->pool_nonstale_hash_rate',network=2";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$message .= 'P2Pool Groestl Hashrate: ' . $global_stats->pool_nonstale_hash_rate . "\r\n";
		$p2p_groestl = $global_stats->pool_nonstale_hash_rate;

		$global_stats = get_p2p_info_db(6,'global_stats',$dblink);
		$query = "INSERT INTO tblpoolhashrate SET moment=NOW(6),hashrate='$global_stats->pool_nonstale_hash_rate',network=3";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$message .= 'P2Pool Skein Hashrate: ' . $global_stats->pool_nonstale_hash_rate . "\r\n";
		$p2p_skein = $global_stats->pool_nonstale_hash_rate;

		$global_stats = get_p2p_info_db(7,'global_stats',$dblink);
		$query = "INSERT INTO tblpoolhashrate SET moment=NOW(6),hashrate='$global_stats->pool_nonstale_hash_rate',network=4";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$message .= 'P2Pool Qubit Hashrate: ' . $global_stats->pool_nonstale_hash_rate . "\r\n";
		$p2p_qubit = $global_stats->pool_nonstale_hash_rate;

		$stats = vtc_mininginfo();
		if($stats->status == 'SUCCESS')
		{
			$hash_groestl = (pow(2,32) * $stats->json_output->result->difficulty_groestl) / 150;
			$hash_skein = (pow(2,32) * $stats->json_output->result->difficulty_skein) / 150;
			$hash_qubit = (pow(2,32) * $stats->json_output->result->difficulty_qubit) / 150;
			$hash_sha256 = (pow(2,32) * $stats->json_output->result->difficulty_sha256d) / 150;
			$hash_scrypt = (pow(2,32) * $stats->json_output->result->difficulty_scrypt) / 150;

			$query = "INSERT INTO tblhashgraph VALUES(NOW(),$hash_sha256,$hash_scrypt,$hash_groestl,$hash_skein,$hash_qubit,$p2p_sha256,$p2p_scrypt,$p2p_groestl,$p2p_skein,$p2p_qubit)";
			$dblink->query($query) OR dberror($dblink,$query);

		}

		return $message;
	}

	function log_nodehashrate($dblink)
	{
		global $config;
		$message = "";
		$query = "SELECT id FROM tblnodes WHERE enabled=1";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$i = $row[0];
			$local_stats = get_p2p_info_db($i,'local_stats',$dblink);

			$hashrate = 0;
			$stalerate = 0;

			foreach($local_stats->miner_hash_rates as $rate)
			{
				$hashrate += $rate;
			}
			foreach($local_stats->miner_dead_hash_rates as $rate)
			{
				$stalerate += $rate;
			}
			$nonstalerate = $hashrate - $stalerate;

			$query = "INSERT INTO tblnodehashrate VALUES(NOW(6),$i,'$hashrate','$stalerate','$nonstalerate')";
			$dblink->query($query) OR dberror($dblink,$query);
			$message .= "Node $i hashrate: $nonstalerate\r\n";
		}
		return $message;
	}


?>
