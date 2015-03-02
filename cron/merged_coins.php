<?php
	require_once('config.inc.php');
	require_once('backend_functions.php');
	require_once('rpc.php');


	function update_mergedtx($dblink)
	{
		$message = '';
		//$txn = 25;

		$query = "SELECT * FROM tblmergedcoins WHERE enabled=1 AND rpcenabled=1";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$coin = $row[2];
			$txn = $row[20];
			$network = $row[4];
			
			$wallet['rpcscheme'] = $row[5];
			$wallet['rpchost'] = $row[6];
			$wallet['rpcport'] = $row[7];
			$wallet['rpcuser'] = $row[8];
			$wallet['rpcpass'] = $row[9];
			$wallet['rpckey'] = '';

			$message .= "------------------------------------------------------------------------------------------------------------------------\r\n";
			$message .= "Starting update of immature $coin blocks.\r\n";

			$query2 = "SELECT txid,height FROM tblblocks WHERE coin='$coin' AND network='$network' AND status='immature' AND paid=0 ORDER BY height";
			$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
			while($row2 = $result2->fetch_row())
			{
				$txid = $row2[0];
				$height = $row2[1];
				$message .= "* Requesting TX $txid for $coin block $height.\r\n";
				$tx = rpc_gettransaction($wallet,$txid);

				if($tx->status=='SUCCESS')
				{
					$confirmations = $tx->json_output->result->confirmations;
					$category = $tx->json_output->result->details[0]->category;
					$message .= "+ Updating transaction and block information for $coin block $height (Confirmations=$confirmations, Status=$category).\r\n";
					$query3 = "UPDATE tblmergedtx SET category='$category',confirmations='$confirmations',lastupdate=NOW() WHERE txid='$txid' AND coin='$coin'";
					$dblink->query($query3) OR dberror($dblink,$query3);

					$query3 = "UPDATE tblblocks SET status='$category',confirmations='$confirmations' WHERE coin='$coin' AND height='$height'";
					$dblink->query($query3) OR dberror($dblink,$query3);
				}
				else
				{
					$message .= "- Failed to get TX $txid from $coin Daemon: {$tx->status}\r\n";
				}
			}


			$message .= "* Updating $txn transactions for $coin - $row[3].\r\n";

			$tx = rpc_transactions($wallet,$txn);

			if($coin=='HUC')
			{
				$stats = rpc_getinfo($wallet);
			}
			else
			{
				$stats = rpc_mininginfo($wallet);
			}
			$i = 0;

			if($tx->status=='SUCCESS' && $stats->status=='SUCCESS')
			{
				$stats = $stats->json_output->result;

				foreach($tx->json_output->result as $item)
				{
					$i++;
					$txid = $item->txid;
					if(isset($item->address))
					{
						$address = $item->address;
					}
					else
					{
						$address = '';
					}
					if(!isset($item->fee))
					{
						$item->fee = 0;
					}
					if(!isset($item->generated))
					{
						$item->generated = 0;
					}
					$t = date('Y-m-d H:i:s',$item->time);

					$query2 = "SELECT txid FROM tblmergedtx WHERE coin='$coin' AND txid='$txid' AND address='$address'";
					$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
					if($result2->num_rows==0)
					{
						$query2 = "INSERT INTO tblmergedtx VALUES('$coin','$item->txid','$item->address','$t','$item->amount','$item->fee','$item->category','$item->confirmations','$item->generated',NOW())";
						$dblink->query($query2) OR dberror($dblink,$query2);
					}
					else
					{
						$query2 = "UPDATE tblmergedtx SET moment='$t',amount='$item->amount',fee='$item->fee',category='$item->category',confirmations='$item->confirmations',generated='$item->generated',lastupdate=NOW() WHERE coin='$coin' AND txid='$txid' AND address='$address'";
						$dblink->query($query2) OR dberror($dblink,$query2);
					}

					// is this a block the node has found???
					if($item->generated==1)
					{
						$message .= save_block($item, $coin, $row[4], $stats, $wallet, $dblink);
						//$message .= "New $coin Block! \r\n";
					}
				}
				$message .= "+ Updated $i $coin Transactions.\r\n";
			}
			else
			{
				$message .= "! ERROR :" . $tx->statusmsg . "\r\n";
			}
		}

		return $message;
	}





	function save_block($item, $coin, $network, $stats, $wallet, $dblink)
	{
		$txid = $item->txid;
		$message = '';

		$query = "SELECT * FROM tblblocks WHERE txid = '$txid' AND coin='$coin'";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		if($result->num_rows==0)
		{
			$t = date('Y-m-d H:i:s',$item->time);
			$height = $stats->blocks - $item->confirmations + 1;

			$hash = rpc_getblockhash($wallet,$height);
			if($hash->status == 'SUCCESS')
			{
				$hash1 = $hash->json_output->result;
			}
			else
			{
				$hash1 = '';
			}
			//var_dump($hash);
			$message .= "! Found new $coin block - $height, hash - $hash1 \r\n";
			
			$query = "SELECT moment FROM tblblocks WHERE coin='$coin' AND network=$network AND status!='orphan' ORDER BY height DESC LIMIT 0,1";
			$result = $dblink->query($query) OR dberror($dblink,$query);
			if($row = $result->fetch_row())
			{
				$lastblocktime = $row[0];
				$elapsed = $item->time - strtotime($lastblocktime);
//print "DEBUG: $lastblocktime ~ $elapsed \r\n";
				$avghashrate = 0;

				$query = "SELECT id FROM tblnodes WHERE enabled=1 AND merged=1 AND network=$network";
				$result = $dblink->query($query) OR dberror($dblink,$query);
				while($row = $result->fetch_row())
				{
					$query2 = "SELECT AVG(nonstalerate) FROM tblnodehashrate WHERE moment>='$lastblocktime' AND moment<='$t' AND node=$row[0]";
					$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
					$row2 = $result2->fetch_row();
					$avghashrate += $row2[0];
				}

				$query = "SELECT AVG(difficulty) FROM tbldifficulty WHERE moment>=DATE_ADD('$lastblocktime',INTERVAL -3 MINUTE) AND moment<=DATE_ADD('$t',INTERVAL 3 MINUTE) AND coin='$coin' AND network=$network";
				$result = $dblink->query($query) OR dberror($dblink,$query);
				$row = $result->fetch_row();
				$avgdiff = $row[0];

				if($avghashrate==0 || $avgdiff==0 || $avghashrate==null || $avgdiff==null)
				{
					$est_time = 0;
					$luck = 0;
					$avghashrate = 0;
					$avgdiff = 0;
				}
				else
				{
					$est_time = pow(2,32) * $avgdiff / $avghashrate;
					$luck = $elapsed / $est_time;
				}
				$query = "INSERT INTO tblblocks VALUES('$height','$coin','$t','$item->category',$elapsed,$avgdiff,$avghashrate,$est_time,$luck,$item->confirmations,'$item->txid',0,$item->amount,$network,'$hash1')";
			}
			else
			{
				$query = "INSERT INTO tblblocks VALUES('$height','$coin','$t','$item->category',0,0,0,0,0,$item->confirmations,'$item->txid',0,$item->amount,$network,'$hash1')";
			}
		}
		else
		{
			$query = "UPDATE tblblocks SET status='$item->category',confirmations='$item->confirmations',value=$item->amount WHERE txid='$txid' AND coin='$coin'";
		}
		$dblink->query($query) OR dberror($dblink,$query);

		return $message;
	}


	function update_mergedestimates($dblink)
	{
		$message = '';
		$query = "SELECT symbol,network,fee,payout_min,payout_max,blockval FROM tblmergedcoins WHERE enabled=1";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$coin = $row[0];
			$network = $row[1];
			$fee = $row[2];

			$message .= "Updating estimated payouts for $coin.\r\n";

			$query2 = "UPDATE tblminercoins SET estimate=0 WHERE coin='$row[0]' AND network=$row[1]";
			$dblink->query($query2) OR dberror($dblink,$query2);

			$query2 = "SELECT value FROM tblblocks WHERE coin='$coin' AND status!='orphan' ORDER BY height DESC LIMIT 0,1";
			$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
			if($result2->num_rows==1)
			{
				$row2 = $result2->fetch_row();
				$block = $row2[0];
			}
			else
			{
				$block = $row[5];
			}

			$query2 = "SELECT data FROM tblminerpayout WHERE network=$row[1] ORDER BY moment DESC LIMIT 0,1";
			$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
			$row2 = $result2->fetch_row();
			$payouts = json_decode($row2[0]);

			$minertotal = 0;
			$miners = array();

			$query2 = "SELECT address FROM tblnodeminers,tblnodes WHERE tblnodeminers.lastseen>=DATE_ADD(NOW(),INTERVAL -1 DAY) AND tblnodeminers.node=tblnodes.id AND tblnodes.enabled=1 AND tblnodes.merged=1 AND tblnodes.network=$row[1]";
			$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
			while($row2 = $result2->fetch_row())
			{
				$address = $row2[0];
				if(isset($payouts->$address))
				{
					$miners[$address] = $payouts->$address;
					$minertotal += $payouts->$address;
				}
			}

			if($minertotal>0)
			{
				$payout_factor = ($block * (100-$row[2]) / 100) / $minertotal;
			}
			else
			{
				$payout_factor = 0;
			}

			$total_payout = 0;

			foreach($miners as $address => $amount)
			{
				$payout = round($amount * $payout_factor,8);
				$total_payout += $payout;
				$query2 = "SELECT address FROM tblminercoins WHERE address='$address' AND coin='$row[0]' AND network=$row[1]";
				$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
				if($result2->num_rows==1)
				{
					$query2 = "UPDATE tblminercoins SET estimate=$payout WHERE address='$address' AND coin='$row[0]' AND network=$row[1]";
					$dblink->query($query2) OR dberror($dblink,$query2);
				}
				else
				{
					$query2 = "INSERT INTO tblminercoins SET address='$address',coin='$row[0]',network=$row[1],payout_min=$row[3],estimate=$payout";
					$dblink->query($query2) OR dberror($dblink,$query2);
				}
			}
			$message .= "A total of $total_payout $coin would be paid out.\r\n";
		}

		return $message;
	}

	function payout_blocks($dblink)
	{
		$message = '';

		$query = "SELECT * FROM tblmergedcoins WHERE enabled=1 AND pmtenabled=1";
		//$query = "SELECT * FROM tblmergedcoins WHERE enabled=1 AND symbol='LTS'";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$coin = $row[2];
			$network = $row[4];
			$fee = $row[14];
			$payout_min = $row[12];

			$message .= "------------------------------------------------------------------------------------------------------------------------\r\n";
			$message .= "~ Checking for newly matured blocks for $coin - $row[3] on network $network.\r\n";

			$wallet['rpcscheme'] = $row[5];
			$wallet['rpchost'] = $row[6];
			$wallet['rpcport'] = $row[7];
			$wallet['rpcuser'] = $row[8];
			$wallet['rpcpass'] = $row[9];
			$wallet['rpckey'] = '';

			// check to see if any blocks that have matured have not already been paid out.
			$query2 = "SELECT height,moment,value,txid FROM tblblocks WHERE coin='$coin' AND network='$network' AND status='generate' AND paid=0 ORDER BY height";
			$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
			if($result2->num_rows>0)
			{
				while($row2 = $result2->fetch_row())
				{
					$height = $row2[0];
					$value = floatval($row2[2]);
					$moment = $row2[1];

					$message .= "\r\n! Block $height ($value $coin @ $moment) has matured, starting payment run.\r\n";

					$query3 = "SELECT data,moment FROM tblminerpayout WHERE network=$network AND moment<='$moment' ORDER BY moment DESC LIMIT 0,1";
					$result3 = $dblink->query($query3) OR dberror($dblink,$query3);
					$row3 = $result3->fetch_row();
					$payouts = json_decode($row3[0]);
					$payoutmoment = $row3[1];

					$message .= "* Got miner payouts for $payoutmoment on network $network.\r\n";

					$minertotal = 0;
					$miners = array();

					$query3 = "SELECT address FROM tblnodeminers,tblnodes WHERE tblnodeminers.lastseen>=DATE_ADD(NOW(),INTERVAL -1 DAY) AND tblnodeminers.node=tblnodes.id AND tblnodes.enabled=1 AND tblnodes.merged=1 AND tblnodes.network=$network GROUP BY address";
					$result3 = $dblink->query($query3) OR dberror($dblink,$query3);
					while($row3 = $result3->fetch_row())
					{
						$address = $row3[0];
						if(isset($payouts->$address))
						{
							$miners[$address] = $payouts->$address;
							$minertotal += $payouts->$address;
						}
					}

					$payout_factor = $value / $minertotal;

					$message .= "* " . count($miners) . " miners eligible for payout, $minertotal% of p2pool network. Payout factor is $payout_factor.\r\n\r\n";

					$total_payout = 0;
					$total_fee = 0;
					$total_donation = 0;

					foreach($miners as $address => $amount)
					{
						$query3 = "SELECT address,coinaddress,donation,balance FROM tblminercoins WHERE address='$address' AND coin='$coin' AND network=$network";
						$result3 = $dblink->query($query3) OR dberror($dblink,$query3);
						if($result3->num_rows==1)
						{
							$row3 = $result3->fetch_row();
							if($row3[1]=='')
							{
								$donation = 100;
								$message .= "- Miner $address has no valid $coin payout address, so is donating all earnings.\r\n";
							}
							else
							{
								$donation = $row3[2];
							}
							$balance = $row3[3];
						}
						else
						{
							$query3 = "INSERT INTO tblminercoins SET address='$address',coin='$coin',network=$network,payout_min=$payout_min,estimate=0";
							$dblink->query($query3) OR dberror($dblink,$query3);
							$message .= "- Miner $address has no valid $coin payout address, so is donating all earnings.\r\n";
							$donation = 100;
							$balance = 0;
						}

						$miner_payout = round($amount * $payout_factor,8);
						$miner_fee = round(($fee/100) * $miner_payout,8);
						$miner_donation = round(($donation/100) * $miner_payout,8);

						if(round($miner_payout-$miner_fee-$miner_donation,8)<0)
						{
							$miner_fee = 0;
							$miner_donation = $miner_payout;
						}

						$message .= "+ Miner $address is eligible for a payout of $miner_payout minus fees of $miner_fee and a donation of $miner_donation.\r\n";

						if($miner_payout>0)
						{
							$query3 = "INSERT INTO tblpayments SET moment=NOW(),coin='$coin',network=$network,address='$address',amount='$miner_payout',message='Payment',block=$height";
							$dblink->query($query3) OR dberror($dblink,$query3);
						}
						if($miner_fee>0)
						{
							$query3 = "INSERT INTO tblpayments SET moment=NOW(),coin='$coin',network=$network,address='$address',amount='-$miner_fee',message='Fee',block=$height";
							$dblink->query($query3) OR dberror($dblink,$query3);
						}
						if($miner_donation>0)
						{
							$query3 = "INSERT INTO tblpayments SET moment=NOW(),coin='$coin',network=$network,address='$address',amount='-$miner_donation',message='Donation',block=$height";
							$dblink->query($query3) OR dberror($dblink,$query3);
						}

						$balance = round($balance + $miner_payout - $miner_fee - $miner_donation,8);

						$query3 = "UPDATE tblminercoins SET balance='$balance' WHERE coin='$coin' AND network=$network AND address='$address'";
						$dblink->query($query3) OR dberror($dblink,$query3);


						$total_payout += $miner_payout;
						$total_fee += $miner_fee;
						$total_donation += $miner_donation;
					}
					$message .= "\r\n* A total of $total_payout $coin has been paid out, $total_fee fees collected, and $total_donation donations received.\r\n";

					// update tblblocks signifying this block has been paid out.

					$query3 = "UPDATE tblblocks SET paid=1 WHERE coin='$coin' AND height='$height' AND txid='$row2[3]'";
					$dblink->query($query3) OR dberror($dblink,$query3);
					$message .= "* Block $height marked as paid.\r\n";
				}
			}
			else
			{
				$message .= "~ No newly matured blocks for $coin - $row[3].\r\n";
			}
		}

		$message .= "------------------------------------------------------------------------------------------------------------------------\r\n";

		return $message;
	}


	function queue_payouts($dblink)
	{
		$message = "Starting to queue payouts.\r\n";

		$query = "SELECT * FROM tblmergedcoins WHERE enabled=1 AND payenabled=1";
		//$query = "SELECT * FROM tblmergedcoins WHERE enabled=1 AND symbol='LTS'";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$coinid = $row[0];
			$coin = $row[2];
			$network = $row[4];
			$np = 0;

			if($row[21]=='OPEN') // can we add more payments?
			{
				$message .= "------------------------------------------------------------------------------------------------------------------------\r\n";
				$message .= "~ Checking for miners of $coin on network $network who need payout.\r\n";

				$query2 = "SELECT address,coinaddress,balance FROM tblminercoins WHERE coin='$coin' AND network=$network AND coinaddress!='' AND balance>=payout_min AND balance!=0";
				$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
				while($row2 = $result2->fetch_row())
				{
					$address = $row2[0];
					$amount = $row2[2];
					$coinaddress = $row2[1];

					$message .= "+ Miner $address has balance $amount $coin, queuing for payout.\r\n";

					$query3 = "SELECT * FROM tblpendingtx WHERE coinaddress='$coinaddress' AND coin='$coin' AND network=$network";
					$result3 = $dblink->query($query3) OR dberror($dblink,$query3);
					if($result3->num_rows==0)
					{
						// create the pending payout record.
						$query3 = "INSERT INTO tblpendingtx VALUES('$address','$coinaddress','$coin',$amount,NOW(),$network)";
						$dblink->query($query3) OR dberror($dblink,$query3);

						$np++;
						// lock payment state for this coin
						$query3 = "UPDATE tblmergedcoins SET payoutstate='LOCKED' WHERE id='$coinid'";
						$dblink->query($query3) OR dberror($dblink,$query3);

						// add a payment record for this miner
						//$query3 = "INSERT INTO tblpayments SET moment=NOW(),coin='$coin',network='$network',address='$address',amount=-$amount,txid='',message='PayoutQueued'";
						$query3 = "INSERT INTO tblaccounthistory SET moment=NOW(),address='$address',message='Payout Queued for $amount $coin.'";
						$dblink->query($query3) OR dberror($dblink,$query3);
						$message .= "+ Payout for $address queued.\r\n";
					}
					else
					{
						// add new amount to existing.
// 						$query3 = "UPDATE tblpendingtx SET amount=(amount+$amount) WHERE coinaddress='$coinaddress' AND coin='$coin'";
// 						$dblink->query($query3) OR dberror($dblink,$query3);

// 						$message .= "* Existing pending payout for $address - $coinaddress. Adding $amount to it.\r\n";
						$message .= "- Existing pending payout for $address - $coinaddress. Not adding a new payment now.\r\n";
					}


				}


				if($np>0)
				{
					// set payment state to 'PENDING' for this coin
					$query3 = "UPDATE tblmergedcoins SET payoutstate='PENDING' WHERE id='$coinid'";
					$dblink->query($query3) OR dberror($dblink,$query3);
					$message .= "* Total of $np payouts queued for $coin.\r\n";
					$message .= "* Changing payout state for $coin to `PENDING`.\r\n\r\n";
				}
				else
				{
					$message .= "~ No payouts queued for $coin.\r\n\r\n";
				}
			}
			else
			{
				$message .= "- Not checking for due $coin payouts as payout state is `$row[21]`.\r\n\r\n";
			}
		}

		return $message;
	}

	function queue_settlements($dblink)
	{
		$message = "Starting to queue settlement payouts.\r\n";

		$query = "SELECT * FROM tblmergedcoins WHERE enabled=1 AND payenabled=1";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$coinid = $row[0];
			$coin = $row[2];
			$network = $row[4];
			$np = 0;

			if($row[21]=='OPEN') // can we add more payments?
			{
				$message .= "------------------------------------------------------------------------------------------------------------------------\r\n";
				$message .= "~ Checking for miners of $coin on network $network who need settlement payout.\r\n";

				$query2 = "SELECT address_vtc,lastseen,balance,coinaddress FROM tblminers,tblminercoins WHERE tblminercoins.coin='$coin' AND tblminers.address_vtc=tblminercoins.address AND tblminercoins.balance>0 AND tblminercoins.network= $network AND tblminers.lastseen<DATE_ADD(NOW(),INTERVAL -4 DAY)";
				$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
				while($row2 = $result2->fetch_row())
				{
					$address = $row2[0];
					$amount = $row2[2];
					$coinaddress = $row2[3];

					$query3 = "SELECT * FROM tblpendingtx WHERE coinaddress='$coinaddress' AND coin='$coin' AND network=$network";
					$result3 = $dblink->query($query3) OR dberror($dblink,$query3);
					if($result3->num_rows==0)
					{
						// create the pending payout record.
						$query3 = "INSERT INTO tblpendingtx VALUES('$address','$coinaddress','$coin',$amount,NOW(),$network)";
						$dblink->query($query3) OR dberror($dblink,$query3);

						$np++;
						// lock payment state for this coin
						$query3 = "UPDATE tblmergedcoins SET payoutstate='LOCKED' WHERE id='$coinid'";
						$dblink->query($query3) OR dberror($dblink,$query3);

						// add a payment record for this miner
						//$query3 = "INSERT INTO tblpayments SET moment=NOW(),coin='$coin',network='$network',address='$address',amount=-$amount,txid='',message='PayoutQueued'";
						$query3 = "INSERT INTO tblaccounthistory SET moment=NOW(),address='$address',message='Settlement Payout Queued for $amount $coin.'";
						$dblink->query($query3) OR dberror($dblink,$query3);
						$message .= "+ Settlement Payout for $address queued.\r\n";

					}
					else
					{
						$message .= "- Existing pending payout for $address - $coinaddress. Not adding a new payment now.\r\n";
					}
				}

				if($np>0)
				{
					// set payment state to 'PENDING' for this coin
					$query3 = "UPDATE tblmergedcoins SET payoutstate='PENDING' WHERE id='$coinid'";
					$dblink->query($query3) OR dberror($dblink,$query3);
					$message .= "* Total of $np payouts queued for $coin.\r\n";
					$message .= "* Changing payout state for $coin to `PENDING`.\r\n\r\n";
				}
				else
				{
					$message .= "~ No payouts queued for $coin.\r\n\r\n";
				}

			}
			else
			{
				$message .= "- Not checking for $coin settlements as payout state is `$row[21]`.\r\n\r\n";
			}
		}

		return $message;
	}

	function run_payouts($dblink)
	{
		$message = "Starting to run pending payouts.\r\n";

		$query = "SELECT * FROM tblmergedcoins WHERE enabled=1 AND rpcenabled=1 AND payenabled=1";
		//$query = "SELECT * FROM tblmergedcoins WHERE enabled=1 AND symbol='LTS'";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$coinid = $row[0];
			$coin = $row[2];
			$network = $row[4];
			$np = 0;

			$wallet['rpcscheme'] = $row[5];
			$wallet['rpchost'] = $row[6];
			$wallet['rpcport'] = $row[7];
			$wallet['rpcuser'] = $row[8];
			$wallet['rpcpass'] = $row[9];
			$wallet['rpckey'] = $row[10];

			if($row[21]=='PENDING') // can we make payouts?
			{
				$message .= "------------------------------------------------------------------------------------------------------------------------\r\n";
				$message .= "~ Checking for pending payouts for $coin on network $network.\r\n";

				$payments = array();

				$message .= "! New Payments \r\n";
				$query2 = "SELECT * FROM tblpendingtx WHERE coin='$coin' AND network=$network";
				$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
				while($row2 = $result2->fetch_row())
				{
					$address = $row2[1];
					$amount = $row2[3];
					$message .= "+ Sending $amount $coin to address $address for miner $row2[0].\r\n";
					$payments[$address] = floatval($amount);
				}
				$npayments = count($payments);
				$message .= "! End of Payments, $npayments ready.\r\n";

				if($npayments>0)
				{
					$walletunlocked = FALSE;

					if($wallet['rpckey']=='')
					{
						$walletunlocked = TRUE;
						$message .= "* $coin Wallet unlocked.\r\n";
					}
					else
					{
						// unlock wallet for 20s.
						$u = rpc_unlock($wallet,20);

						if($u->status=='SUCCESS')
						{
							$walletunlocked = TRUE; // great, wallet is unlocked.
							$message .= "* $coin Wallet unlocked.\r\n";
						}
						else
						{
							$walletunlocked = FALSE;
							$message .= "- Error unlocking $coin wallet: " . $u->statusmsg . "\r\n";
						}
					}

					if($walletunlocked) // wallet is unlocked, proceed to payout.
					{
						$tx = rpc_sendmany($wallet,$payments,120); // long timeout to cater for many transactions.

						if($tx->status=='SUCCESS') // was is successful?
						{
							$txid = $tx->json_output->result;
							$message .= "* Payout successful, $coin transaction ID: $txid \r\n";
							// create payout records everywhere and change payout state
							$message .= record_payouts($dblink,$coin,$txid,$network);
						}
						else
						{
							$message .= "- $coin payout failed: " . $tx->statusmsg . "\r\n";
							// set payout state to ERROR
							$query3 = "UPDATE tblmergedcoins SET payoutstate='ERROR' WHERE id='$coinid'";
							$dblink->query($query3) OR dberror($dblink,$query3);
							$message .= "- Changing payout state for $coin to `ERROR`.\r\n";
						}


						// lock wallet
						if($wallet['rpckey']=='')
						{
							$message .= "* $coin Wallet Locked\r\n";
						}
						else
						{
							rpc_lock($wallet);
							$message .= "* $coin Wallet Locked\r\n";
						}
					}

				}
				else // $npayments is ZERO
				{
					$message .= "* No pending payment records, changing payout state to `OPEN`.\r\n";
					$query3 = "UPDATE tblmergedcoins SET payoutstate='OPEN' WHERE id='$coinid'";
					$dblink->query($query3) OR dberror($dblink,$query3);
				}


			}
			else // payout state is NOT 'PENDING'
			{
				$message .= "------------------------------------------------------------------------------------------------------------------------\r\n";
				$message .= "~ Not checking for pending $coin payouts as payout state is `$row[21]`.\r\n\r\n";
			}
		}
		return $message;
	}



	function record_payouts($dblink,$coin,$txid,$network)
	{
		$message = "";

		$query = "SELECT * FROM tblpendingtx WHERE coin='$coin' AND network=$network";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$address = $row[0];
			$coinaddress = $row[1];
			$amount = $row[3];

			// add a payment record for this miner
			$query = "INSERT INTO tblpayments SET moment=NOW(),coin='$coin',network='$network',address='$address',amount=-$amount,txid='',message='Pay Out'";
			$dblink->query($query) OR dberror($dblink,$query);

			$message .= "+ Created record for miner $address for Payout of $amount $coin to $coinaddress.\r\n";

			$query2 = "SELECT balance FROM tblminercoins WHERE address='$address' AND coin='$coin' AND network='$network'";
			$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
			$row2 = $result2->fetch_row();
			$newbalance = round($row2[0] - $amount,8);
			$query2 = "UPDATE tblminercoins SET balance=$newbalance WHERE address='$address' AND coin='$coin' AND network='$network'";
			$dblink->query($query2) OR dberror($dblink,$query2);

			$message .= "+ Updated miner $address account balance to $newbalance $coin.\r\n";

			// delete pendingtx record
			$query = "DELETE FROM tblpendingtx WHERE address='$address' AND coin='$coin' AND coinaddress='$coinaddress' AND network=$network";
			$dblink->query($query) OR dberror($dblink,$query);

			$message .= "+ Deleted pending transaction record for miner $address.\r\n";
		}

		$query = "SELECT * FROM tblpendingtx WHERE coin='$coin' AND network=$network";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		if($result->num_rows==0)
		{
			$message .= "* All pending payouts completed. Changing payout state to `OPEN`.\r\n";
			$query = "UPDATE tblmergedcoins SET payoutstate='OPEN' WHERE symbol='$coin' AND network=$network";
		}
		else
		{
			$message .= "- Some pending payouts remain. Changing payout state to `ERROR`.\r\n";
			$query = "UPDATE tblmergedcoins SET payoutstate='ERROR' WHERE symbol='$coin' AND network=$network";
		}
		$dblink->query($query) OR dberror($dblink,$query);


		return $message;
	}
?>