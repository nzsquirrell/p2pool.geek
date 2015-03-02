<?php
	require_once('config.inc.php');
 	require_once('database.inc.php');
	require_once('rpc.php');


	function process_changes($dblink)
	{
	 	$msg = "Starting to process changes to accounts.\r\n";

	 	$query = "SELECT id,address,changes,network FROM tblchangerequests WHERE state='VERIFIED' ORDER BY id";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$address = $row[1];
			$network = $row[3];

			$msg .= "Processing changes for address $address on network $network.\r\n";

			$changes = json_decode($row[2]);

			foreach($changes as $coin=>$settings)
			{
				$complete = TRUE;

				if($settings->OldAddress != $settings->NewAddress)
				{
					$complete = FALSE;

					if($settings->NewAddress=='')
					{
						$query = "UPDATE tblminercoins SET coinaddress='' WHERE address='$address' AND coin='$coin' AND network=$network";
						$dblink->query($query) OR dberror($dblink,$query);
						$msg .= log_account_history($dblink,$address,"Removed $coin address from account.");
						$complete = TRUE;
					}
					else
					{
						// validate new address.
						$query2 = "SELECT * FROM tblmergedcoins WHERE symbol='$coin' AND network=$network";
						$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
						$row2 = $result2->fetch_row();

						$wallet['rpcscheme'] = $row2[5];
						$wallet['rpchost'] = $row2[6];
						$wallet['rpcport'] = $row2[7];
						$wallet['rpcuser'] = $row2[8];
						$wallet['rpcpass'] = $row2[9];
						$wallet['rpckey'] = '';

						if($row2[24])
						{
							$valid = rpc_validate_address($wallet,$settings->NewAddress);

							if($valid->status=='SUCCESS')// || true)  // HACK!!!
							{
								if($valid->json_output->result->isvalid)// || true) // HACK !!!
								{
									$query = "UPDATE tblminercoins SET coinaddress='" . $settings->NewAddress . "' WHERE address='$address' AND coin='$coin' AND network=$network";
									$dblink->query($query) OR dberror($dblink,$query);
									$msg .= log_account_history($dblink,$address,"Changed $coin address to $settings->NewAddress.");
								}
								else
								{
									$msg .= log_account_history($dblink,$address,"Unable to change $coin address to $settings->NewAddress as this is not a valid address.");
								}
								$complete = TRUE;
							}
							else
							{
								$msg .= "Error validating $coin address: " . $valid->statusmsg . "\r\n";
								$complete = FALSE;
							}
						}
						else
						{
							$msg .= "RPC for $coin is currently disabled. Attempting again later.\r\n";
							$complete = FALSE;
						}
					}
				}

				if(floatval($settings->OldPayout) != floatval($settings->NewPayout))
				{
					$query2 = "SELECT payout_min,payout_max FROM tblmergedcoins WHERE symbol='$coin' AND network=$network";
					$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
					$row2 = $result2->fetch_row();

					$payout = floatval($settings->NewPayout);
					if($payout>$row2[1])
					{
						$payout = $row2[1];
					}
					if($payout<$row2[0])
					{
						$payout = $row2[0];
					}
					$query = "UPDATE tblminercoins SET payout_min='$payout' WHERE address='$address' AND coin='$coin' AND network=$network";
					$dblink->query($query) OR dberror($dblink,$query);
					$msg .= log_account_history($dblink,$address,"Changed $coin minimum payout to $payout.");
				}

				if(floatval($settings->OldDonation) != floatval($settings->NewDonation))
				{
					$donation = floatval($settings->NewDonation);
					if($donation>=0 && $donation<=100)
					{
						$query = "UPDATE tblminercoins SET donation='$donation' WHERE address='$address' AND coin='$coin' AND network=$network";
						$dblink->query($query) OR dberror($dblink,$query);
						$msg .= log_account_history($dblink,$address,"Changed $coin donation to $donation%.");
					}
				}

				if($complete)
				{
					unset($changes->$coin);
				}
			}

			$remaining = json_encode($changes);
			if($remaining=='{}')
			{
				$msg .= "All changes complete for address $address.\r\n";
				$query = "UPDATE tblchangerequests SET state='COMPLETE' WHERE id=$row[0]";
				$dblink->query($query) OR dberror($dblink,$query);
			}
			else
			{
				$msg .= "Remaining changes for $address: $remaining \r\n";
				$query = "UPDATE tblchangerequests SET changes='$remaining' WHERE id=$row[0]";
				$dblink->query($query) OR dberror($dblink,$query);
			}
		}
		$msg .= "Changes completed.\r\n";
		return $msg;
	}



	function log_account_history($dblink,$address,$message)
	{
		$message = $dblink->real_escape_string($message);

		$query = "INSERT INTO tblaccounthistory SET moment=NOW(),address='$address',message='$message'";
		$dblink->query($query) OR dberror($dblink,$query);

		return $message . "\r\n";
	}


 	function verify_changes($dblink)
 	{
	 	$msg = "Starting to validate changes to accounts.\r\n";

	 	$query = "SELECT id,address,message,sig FROM tblchangerequests WHERE state='NEW'";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$address = $row[1];
			$msg .= "Verifying change $row[0] for address $address.\r\n";
			$status = '';

			if((!base64_decode($row[3],true)) || $row[3]=='' || (strlen($row[3]) % 4)!=0)
			{
				$msg .= "Signature '$row[3]' is not valid Base64.\r\n";
				$status = 'INVALID';
				log_account_history($dblink,$address,'Change request failed due to a badly formatted signature.');
			}
			else
			{
				$verify = vtc_verifymessage($row[1], $row[2], $row[3]);
				if($verify->status=='SUCCESS')
				{
					if($verify->json_output->result)
					{
						$msg .= "Verification Successful.\r\n";
						$status = 'VERIFIED';
						log_account_history($dblink,$address,'Change request verification succeeded.');
					}
					else
					{
						$msg .= "Verification Failed.\r\n";
						$status = 'VFAILED';
						log_account_history($dblink,$address,'Change request failed as the signature could not be verified.');
					}
				}
				else
				{
					if($verify->statusmsg=='Invalid address')
					{
						$msg .= "Invalid MYR Address.\r\n";
						$status = 'INVALID';
						log_account_history($dblink,$address,'Change request failed due to an invalid address.');
					}
					else
					{
						$msg .= "Error verifying change: " . $verify->statusmsg . "\r\n";
						$status = '';
					}
				}
			}

			if($status!='')
			{
				$query = "UPDATE tblchangerequests SET state='$status' WHERE id=$row[0]";
				$dblink->query($query) OR dberror($dblink,$query);
			}

		}
		$msg .= "Validations complete.\r\n";
	 	return $msg;
 	}


?>