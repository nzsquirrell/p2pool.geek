<?php
	require_once('../include/database.inc.php');
	require_once('../include/functions.inc.php');
	$dblink = dbinit();

	$algos[0] = 'SHA256';
	$algos[1] = 'Scrypt';
	$algos[2] = 'Myr-Groestl';
	$algos[3] = 'Skein';
	$algos[4] = 'Qubit';


	$info = new stdClass;

	$info->MergedCoins = array();

	$i = 0;
	$query = "SELECT symbol,name,network,payoutstate FROM tblmergedcoins WHERE enabled=1 ORDER BY symbol";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		$coin = $row[0];
		$info->MergedCoins[$i] = new stdClass;
		$info->MergedCoins[$i]->Symbol = $row[0];
		$info->MergedCoins[$i]->Name = $row[1];
		$info->MergedCoins[$i]->Algorithm = $algos["$row[2]"];
		$info->MergedCoins[$i]->PayoutState = $row[3];

		$query2 = "SELECT height,moment,value FROM tblblocks WHERE coin='$coin' AND status!='orphan' AND network=$row[2] ORDER BY height DESC LIMIT 0,1";
		$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
		$row2 = $result2->fetch_row();
		$info->MergedCoins[$i]->LastBlock = "$row2[0] @ " . gmdate('H:i:s',strtotime($row2[1]));
		$info->MergedCoins[$i]->LastBlockValue = sprintf("%01.08f",$row2[2]);

		$query2 = "SELECT COUNT(height),SUM(value) FROM tblblocks WHERE coin='$coin' AND status!='orphan' AND network=$row[2] AND moment>=DATE_ADD(NOW(),INTERVAL -1 DAY) ORDER BY moment";
		$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
		$row2 = $result2->fetch_row();
		$info->MergedCoins[$i]->BlocksToday = $row2[0];
		$info->MergedCoins[$i]->ValueToday = sprintf("%01.08f",$row2[1]);

		$query2 = "SELECT COUNT(id),SUM(amount) FROM tblpayments WHERE coin='$coin' AND network=$row[2] AND moment>=DATE_ADD(NOW(),INTERVAL -1 DAY) AND message='Payment' ORDER BY moment";
		$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
		$row2 = $result2->fetch_row();
		$info->MergedCoins[$i]->PaymentsToday = sprintf("%01.08f",$row2[1]);

		$query2 = "SELECT COUNT(id),ABS(SUM(amount)) FROM tblpayments WHERE coin='$coin' AND network=$row[2] AND moment>=DATE_ADD(NOW(),INTERVAL -1 DAY) AND message='Donation' ORDER BY moment";
		$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
		$row2 = $result2->fetch_row();
		$info->MergedCoins[$i]->DonationsToday = sprintf("%01.08f",$row2[1]);

		$query2 = "SELECT COUNT(id),ABS(SUM(amount)) FROM tblpayments WHERE coin='$coin' AND network=$row[2] AND moment>=DATE_ADD(NOW(),INTERVAL -1 DAY) AND message='Pay Out' ORDER BY moment";
		$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
		$row2 = $result2->fetch_row();
		$info->MergedCoins[$i]->PayoutsToday = sprintf("%01.08f",$row2[1]);
		
		$i++;
	}


	$n = 0;
	$info->Payouts = array();
	$query = "SELECT * FROM tblpendingtx ORDER BY moment DESC LIMIT 0,30";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		$info->Payouts[$n] = new stdClass;
		$info->Payouts[$n]->Coin = $row[2];
		$info->Payouts[$n]->Miner = $row[0];
		$info->Payouts[$n]->Amount = sprintf("%01.08f $row[2]",$row[3]);
		$info->Payouts[$n]->Moment = 'Pending';

		$n++;
	}

	if($n<30)
	{
		$query = "SELECT address,coin,ABS(amount),moment FROM tblpayments WHERE message='Pay Out' ORDER BY moment DESC LIMIT 0," . (30-$n);
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$info->Payouts[$n] = new stdClass;
			$info->Payouts[$n]->Coin = $row[1];
			$info->Payouts[$n]->Miner = $row[0];
			$info->Payouts[$n]->Amount = sprintf("%01.08f $row[1]",$row[2]);
			$info->Payouts[$n]->Moment = gmdate('H:i:s',strtotime($row[3]));

			$n++;
		}
	}

	$n = 0;
	$info->Payments = array();
	$query = "SELECT tblminercoins.address,tblminercoins.coin,ABS(amount),moment FROM tblpayments,tblminercoins WHERE message='Payment'";
	$query .= " AND tblminercoins.coin=tblpayments.coin AND tblminercoins.network=tblpayments.network AND tblminercoins.address=tblpayments.address";
	$query .= " AND tblminercoins.coinaddress!='' ORDER BY moment DESC LIMIT 0,30";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		$info->Payments[$n] = new stdClass;
		$info->Payments[$n]->Coin = $row[1];
		$info->Payments[$n]->Miner = $row[0];
		$info->Payments[$n]->Amount = sprintf("%01.08f $row[1]",$row[2]);
		$info->Payments[$n]->Moment = gmdate('H:i:s',strtotime($row[3]));

		$n++;
	}


	$n = 0;
	$info->Nodes = array();
	$query = "SELECT * FROM tblnodes WHERE enabled=1 AND merged=1 ORDER BY abbr";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		$info->Nodes[$n] = new stdClass;
		$info->Nodes[$n]->ID = $row[1];
		$info->Nodes[$n]->Name = $row[2];
		$info->Nodes[$n]->Algorithm = $algos["$row[21]"];
		if($row[17]=='Online')
		{
			$info->Nodes[$n]->Status = '<span class="label label-success">Online</span>';
		}
		elseif($row[17]=='Unknown')
		{
			$info->Nodes[$n]->Status = '<span class="label label-warning">Unknown</span>';
		}
		else
		{
			$info->Nodes[$n]->Status = '<span class="label label-danger">Offline</span>';
		}
		$info->Nodes[$n]->LastUpdate = gmdate('M d H:i:s e',strtotime($row[5])) . ', ' . (time()-strtotime($row[5])) . ' seconds ago.';
		$info->Nodes[$n]->Miners = $row[6];
		$info->Nodes[$n]->NonStaleRate = scale_rate($row[9]);
		$info->Nodes[$n]->RPC = '';

		foreach($info->MergedCoins as $index=>$blah)
		{
			$coin = $blah->Symbol;
			if($blah->Algorithm==$info->Nodes[$n]->Algorithm)
			{
				$query2 = "SELECT packets FROM tblrpcmonitor WHERE source='$row[22]' AND coin='$coin' ORDER BY moment DESC LIMIT 0,1";
				$result2 = $dblink->query($query2) OR dberror($dblink,$query2);
				if($row2 = $result2->fetch_row())
				{
					if($row2[0]>0)
					{
						$info->Nodes[$n]->RPC .= '<span class="label label-success">' . $coin . '</span> ';
					}
					else
					{
						$info->Nodes[$n]->RPC .= '<span class="label label-danger">' . $coin . '</span> ';
					}
				}
				else
				{
					$info->Nodes[$n]->RPC .= '<span class="label label-danger">' . $coin . '</span> ';
				}
			}

		}

		$n++;
	}


	$dblink->close();

	header('Content-type: application/json');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

	$info->Updated = gmdate('D jS M H:i:s e');

	echo json_encode($info);

?>