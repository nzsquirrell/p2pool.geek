<?php

//	require_once('config.inc.php');
//	require_once('database.inc.php');
	require_once('rpc.php');


//	set_time_limit(600);

//	$dblink = dbinit();


	function donations($dblink)
	{
		$message = "";
		$query = "SELECT value FROM tbldonationsettings WHERE name='minpayout'";
	 	$result = $dblink->query($query) OR dberror($dblink,$query);
	 	$row = $result->fetch_row();
		$minpayout = $row[0];

		$message .= donation_estimates($dblink, $minpayout);
		$message .= donation_check($dblink, $minpayout);

		$query = "SELECT value FROM tbldonationsettings WHERE name='payout'";
	 	$result = $dblink->query($query) OR dberror($dblink,$query);
	 	$row = $result->fetch_row();
	 	if($row[0]=='Y')
	 	{
			$message .= donation_pay($dblink);
		}
		else
		{
			$message .= "- Payments disabled.\r\n";
		}

		return $message;
	}
	//echo donation_getbalance();


	function donation_pay($dblink)
	{
		$wallet['rpcscheme'] = 'http';
		$wallet['rpchost'] = '172.16.55.200';
		$wallet['rpcport'] = 10899;
		$wallet['rpcuser'] = 'nzsquirrell';
		$wallet['rpcpass'] = '8TjvHdoNnHwr9Ua6RDfk6dDtTkaFAkFwHndjPAgQbG9ahPNmek';
		$wallet['rpckey'] = '';

		$message = "";

		$query = "SELECT count(complete) FROM tbldonationpayouts WHERE complete=0";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();
		if($row[0]>0)
		{
			$message = "+ Donations pending to payout.\r\n";

			$payments = array();

			$query = "SELECT address,SUM(amount) FROM tbldonationpayouts WHERE complete=0 GROUP BY address";
			$result = $dblink->query($query) OR dberror($dblink,$query);
			while($row = $result->fetch_row())
			{
				$address = $row[0];
				$amount = round($row[1],8);
				if($amount>0.0001)
				{
					$payments[$address] = floatval($amount);
				}
			}
//var_dump($payments);
			if(count($payments)>0)
			{
				$message .= "+ " . count($payments) . " payments ready be made.\r\n";

				$walletunlocked = FALSE;

				if($wallet['rpckey']=='')
				{
					$walletunlocked = TRUE;
					$message .= "* Wallet unlocked.\r\n";
				}
				else
				{
					// unlock wallet for 125s.
					$u = rpc_unlock($wallet,125);

					if($u->status=='SUCCESS')
					{
						$walletunlocked = TRUE; // great, wallet is unlocked.
						$message .= "* Wallet unlocked.\r\n";
					}
					else
					{
						$walletunlocked = FALSE;
						$message .= "- Error unlocking wallet: " . $u->statusmsg . "\r\n";
					}
				}

				if($walletunlocked) // wallet is unlocked, proceed to payout.
				{
					$tx = rpc_sendmany($wallet,$payments,120); // long timeout to cater for many transactions.

					if($tx->status=='SUCCESS') // was is successful?
					{
						$txid = $tx->json_output->result;
						$message .= "* Payout successful, transaction ID: $txid \r\n";

						$query = "UPDATE tbldonationsettings SET value=NOW() WHERE name='lastpayout'";
						$dblink->query($query) OR dberror($dblink,$query);

						foreach($payments as $address=>$amount)
						{
							$query = "UPDATE tbldonationpayouts SET complete=1 WHERE address='$address'";
							$dblink->query($query) OR dberror($dblink,$query);
						}
					}
					else
					{
						$message .= "- payout failed: " . $tx->statusmsg . "\r\n";
					}

				}

				// lock wallet
				if($wallet['rpckey']=='')
				{
					$message .= "* Wallet Locked\r\n";
				}
				else
				{
					rpc_lock($wallet);
					$message .= "* Wallet Locked\r\n";
				}

				$message .= "~ Checking donation wallet balance... \r\n";
				$balance = donation_getbalance();
				if($balance>=0)
				{
					$message .= "~ Balance is $balance MYR.\r\n";
					$query = "SELECT balance FROM tbldonationbalance ORDER BY moment DESC LIMIT 0,1";
					$result = $dblink->query($query) OR dberror($dblink,$query);
					$row = $result->fetch_row();

					if(floatval($row[0]) != floatval($balance))
					{
						$change = round(floatval($balance) - floatval($row[0]),8);
						$query = "INSERT INTO tbldonationbalance VALUES(NOW(),$balance,$change)";
						$dblink->query($query) OR dberror($dblink,$query);
						$message .= "+ Balance has changed by $change MYR.\r\n";
					}
				}

			}


		}
		else
		{
			$message = "- No donations pending to payout.\r\n";
		}

		return $message;
	}


	function donation_check($dblink, $minpayout)
	{
		$message = "~ Checking donation wallet balance... \r\n";
		$balance = donation_getbalance();

		if($balance>=0)
		{
			$message .= "~ Balance is $balance MYR.\r\n";
			$query = "SELECT balance FROM tbldonationbalance ORDER BY moment DESC LIMIT 0,1";
			$result = $dblink->query($query) OR dberror($dblink,$query);
			$row = $result->fetch_row();

			if(floatval($row[0]) != floatval($balance))
			{
				$change = round(floatval($balance) - floatval($row[0]),8);
				$query = "INSERT INTO tbldonationbalance VALUES(NOW(),$balance,$change)";
				$dblink->query($query) OR dberror($dblink,$query);
				$message .= "+ Balance has changed by $change MYR.\r\n";
			}

			$query = "SELECT count(complete) FROM tbldonationpayouts WHERE complete=0";
			$result = $dblink->query($query) OR dberror($dblink,$query);
			$row = $result->fetch_row();

			if($row[0]==0)
			{
				if($balance>=$minpayout)
				{
					$message .= "+ Balance is greater than minimum payout amount of $minpayout MYR. Generating Payout!\r\n";

					$query = "SELECT value FROM tbldonationsettings WHERE name='address'";
				 	$result = $dblink->query($query) OR dberror($dblink,$query);
				 	$row = $result->fetch_row();
					$donation_address = $row[0];

					$query = "SELECT network,weight FROM tbldonationest";
					$result = $dblink->query($query) OR dberror($dblink,$query);
					while($row = $result->fetch_row())
					{
						$network = $row[0];
						$weight = $row[1];

						$query2 = "SELECT data FROM tblminerpayout WHERE network=$network ORDER BY moment DESC LIMIT 0,1";
						$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
						$row2 = $result2->fetch_row();

						$payouts = json_decode($row2[0]);

						foreach($payouts as $address=>$amount)
						{
							$minerpay = round($amount/100 * $weight * ($balance - 0.5),8);
							if($minerpay>0.0001 && $address!=$donation_address)
							{
								$query = "INSERT INTO tbldonationpayouts VALUES(NOW(),'$address',$network,$minerpay,0)";
								$dblink->query($query) OR dberror($dblink,$query);
							}
						}

					}
				}
			}
			else
			{
				$message .= "- Donation payments are pending, not queuing more now.\r\n";
			}
		}
		else
		{
			$message .= "- Failed to get wallet balance!\r\n";
		}

		return $message;
	}




	function donation_getbalance()
	{
		$balance = -1;
		$wallet['rpcscheme'] = 'http';
		$wallet['rpchost'] = '172.16.55.200';
		$wallet['rpcport'] = 10899;
		$wallet['rpcuser'] = 'nzsquirrell';
		$wallet['rpcpass'] = '8TjvHdoNnHwr9Ua6RDfk6dDtTkaFAkFwHndjPAgQbG9ahPNmek';
		$wallet['rpckey'] = '';


		$info = rpc_getinfo($wallet);
		//$command = '{"jsonrpc": "1.0", "id":"curltest", "method": "getbalance", "params": ["", 6] }';
		//$info = rpc_base($wallet,$command);

		if($info->status == 'SUCCESS')
		{
			$balance = floatval($info->json_output->result->balance);
		}

		return $balance;
	}


	function donation_estimates($dblink, $minpayout)
	{
		$message = '';

		$algos[0] = 'SHA256';
		$algos[1] = 'Scrypt';
		$algos[2] = 'Myr-Groestl';
		$algos[3] = 'Skein';
		$algos[4] = 'Qubit';

		$diff = array();

		$query = "SELECT network,AVG(difficulty) FROM tbldifficulty WHERE moment>=DATE_ADD(NOW(),INTERVAL -1 HOUR) AND coin='MYR' GROUP BY network";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$network = $row[0];
			$diff[$network] = $row[1];
		}

		$hash = array();

		$query = "SELECT network,AVG(hashrate) FROM tblpoolhashrate WHERE moment>=DATE_ADD(NOW(),INTERVAL -1 HOUR) GROUP BY network";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$network = $row[0];
			$hash[$network] = $row[1];
		}


		$time = array();
		$totaltime = 0;

		for($i=0; $i<=4; $i++)
		{
			$time[$i] = pow(2,32) * $diff[$i] / $hash[$i];
			$totaltime += $time[$i];
		}

		$weight = array();

		for($i=0; $i<=4; $i++)
		{
			$weight[$i] = (0.75 * $time[$i] / $totaltime) + 0.05;
			$message .= "Algorithm " . $algos[$i] . " : Difficulty = " . $diff[$i] . ", Hashrate = " . $hash[$i] . ", Time to Block = " . $time[$i] . " seconds, Payout Weight = " . sprintf("%02.01f%%",round(100*$weight[$i],1)) . ".\r\n";

			$payout = ($minpayout - 0.5) * $weight[$i];
			$query = "UPDATE tbldonationest SET difficulty='$diff[$i]',hashrate='$hash[$i]',time='$time[$i]',weight='$weight[$i]',payout='$payout' WHERE network=$i";
			$dblink->query($query) OR dberror($dblink,$query);
		}

		return $message;
	}


?>