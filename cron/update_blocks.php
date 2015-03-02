<?php
	require_once('backend_functions.php');

	function update_blocks($dblink)
	{
		$message = '';
		// SHA256 Blocks
		$blocks = get_p2p_info_db(14,'recent_blocks',$dblink);
		$message .= myr_blocks($dblink,$blocks,0);

		// Scrypt Blocks
		$blocks = get_p2p_info_db(15,'recent_blocks',$dblink);
		$message .= myr_blocks($dblink,$blocks,1);

		// groestl
 		$blocks = get_p2p_info_db(5,'recent_blocks',$dblink);
 		$message .= myr_blocks($dblink,$blocks,2);

		// Skein Blocks
		$blocks = get_p2p_info_db(6,'recent_blocks',$dblink);
		$message .= myr_blocks($dblink,$blocks,3);

		// Qubit Blocks
		$blocks = get_p2p_info_db(7,'recent_blocks',$dblink);
		$message .= myr_blocks($dblink,$blocks,4);


		return $message;
	}




	function myr_blocks($dblink,$blocks,$network)
	{
		$message = "Looking for new MYR blocks for network $network.\r\n";
		// first - create records for any new blocks.
		$n = 0;
		foreach($blocks as $block)
		{
			$query = "SELECT * FROM tblblocks WHERE height='$block->number' AND coin='MYR' AND network=$network";
			$result = $dblink->query($query) OR dberror($dblink,$query);
			if($result->num_rows==0)
			{
				$foundtime = date('Y-m-d H:i:s',$block->ts);
				$hash = $block->hash;
				$query = "INSERT INTO tblblocks SET height='$block->number',hash='$hash',coin='MYR',moment='$foundtime',elapsedtime=0,avgdifficulty=0,avghashrate=0,est_time=0,luck=0,status='NEW',network=$network";
				$dblink->query($query) OR dberror($dblink,$query);

				$message .= "Found new block $block->number\r\n";
				$n++;
			}
		}
		if($n>0)
		{
			$message .= "Found $n new blocks on network $network.\r\n";
		}
		else
		{
			$message .= "No new blocks on network $network.\r\n";
		}

		// now go back and update stats.

		$query = "SELECT * FROM tblblocks WHERE coin='MYR' AND network=$network AND status='NEW' ORDER BY height";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			// get moment of last block.
			$query2 = "SELECT moment FROM tblblocks WHERE coin='MYR' AND network=$network AND height<$row[0] ORDER BY height DESC LIMIT 0,1";
			$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
			$row2 = $result2->fetch_row();

			$lastblocktime = $row2[0];
			$foundtime = $row[2];
			$elapsed = strtotime($foundtime) - strtotime($lastblocktime);

			$query2 = "SELECT AVG(hashrate) FROM tblpoolhashrate WHERE moment>=DATE_ADD('$lastblocktime',INTERVAL -3 MINUTE) AND moment<=DATE_ADD('$foundtime',INTERVAL 3 MINUTE) AND network=$network";
			$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
			$row2 = $result2->fetch_row();
			$avghashrate = $row2[0];

			$query2 = "SELECT AVG(difficulty) FROM tbldifficulty WHERE moment>=DATE_ADD('$lastblocktime',INTERVAL -3 MINUTE) AND moment<=DATE_ADD('$foundtime',INTERVAL 3 MINUTE) AND coin='MYR' AND network=$network";
			$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
			$row2 = $result2->fetch_row();
			$avgdiff = $row2[0];

			if($avghashrate==0 || $avgdiff==0)
			{
				$est_time = 0;
				$luck = 0;
			}
			else
			{
				$est_time = pow(2,32) * $avgdiff / $avghashrate;
				$luck = $elapsed / $est_time;
			}

			$query = "UPDATE tblblocks SET elapsedtime='$elapsed',avgdifficulty='$avgdiff',avghashrate='$avghashrate',est_time='$est_time',luck='$luck',status='' WHERE height=$row[0] AND coin='MYR' AND network=$network";
			$dblink->query($query) OR dberror($dblink,$query);

			$message .= "Updated statistics for MYR block $row[0].\r\n";
		}

		$message .= "Completed for network $network.\r\n";

		return $message;
	}

?>