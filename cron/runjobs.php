<?php
	@chdir(dirname(__FILE__).'/'); //Change dir.

	require_once('config.inc.php');
	require_once('database.inc.php');

	require_once('update_blocks.php');
	require_once('update_xrates.php');
	require_once('update_p2pinfo.php');
	require_once('logstats.php');
	require_once('run_payments.php');
	require_once('merged_coins.php');
	require_once('validate.php');
	require_once('donation.php');


	set_time_limit(600);

	$dblink = dbinit();



	$query = "SELECT * FROM tbljobs WHERE enabled='Y' ORDER BY priority DESC,lastrun";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		if(time()-strtotime($row[4])>$row[3])
		{
			$message = '';
			$t = time();
			$t0 = date('M d H:i:s',$t);
			switch($row[0])
			{
				case 'logdiff':
					startjob($row[0],$dblink);
					$message = log_diff($dblink);
					jobdone($row[0],$message,(time()-$t),$dblink);
					break;
				case 'loghash':
					startjob($row[0],$dblink);
					$message = log_hashrate($dblink);
					$message .= log_nodehashrate($dblink);
					jobdone($row[0],$message,(time()-$t),$dblink);
					break;
				case 'blocks':
					startjob($row[0],$dblink);
					$message = update_blocks($dblink);
					jobdone($row[0],$message,(time()-$t),$dblink);
					break;
				case 'xrates':
					startjob($row[0],$dblink);
					$message = update_xrates($dblink);
					jobdone($row[0],$message,(time()-$t),$dblink);
					break;
				case 'mergedtx':
					startjob($row[0],$dblink);
					$message = update_mergedtx($dblink);
					jobdone($row[0],$message,(time()-$t),$dblink);
					break;
				case 'p2pinfo':
					startjob($row[0],$dblink);
					$message = update_p2pinfo($dblink);
					jobdone($row[0],$message,(time()-$t),$dblink);
					break;
				case 'dbmaint':
					startjob($row[0],$dblink);
					database_maintenance($dblink);
					jobdone($row[0],'Maintenance Complete.',(time()-$t),$dblink);
					break;
				case 'estimates':
					startjob($row[0],$dblink);
					$message = update_mergedestimates($dblink);
					jobdone($row[0],$message,(time()-$t),$dblink);
					break;
				case 'validate':
					startjob($row[0],$dblink);
					$message = verify_changes($dblink);
					$message .= process_changes($dblink);
					jobdone($row[0],$message,(time()-$t),$dblink);
					break;
				case 'blockpayments':
					startjob($row[0],$dblink);
					$message = payout_blocks($dblink);
					jobdone($row[0],$message,(time()-$t),$dblink);
					break;
				case 'queuepayouts':
					startjob($row[0],$dblink);
					$message = queue_payouts($dblink);
					jobdone($row[0],$message,(time()-$t),$dblink);
					break;
				case 'settlements':
					startjob($row[0],$dblink);
					$message = queue_settlements($dblink);
					jobdone($row[0],$message,(time()-$t),$dblink);
					break;
				case 'runpayouts':
					startjob($row[0],$dblink);
					$message = run_payouts($dblink);
					jobdone($row[0],$message,(time()-$t),$dblink);
					break;
				case 'donation':
					startjob($row[0],$dblink);
					$message = donations($dblink);
					jobdone($row[0],$message,(time()-$t),$dblink);
					break;



			}
		}
	}

	$result->close();
	$dblink->close();


	function startjob($job,$dblink)
	{
		print date('M d H:i:s') . " Starting $job...";
		$query = "UPDATE tbljobs SET active=1 WHERE name='$job'";
		$dblink->query($query) OR dberror($dblink,$query);
	}

	function jobdone($job,$message,$duration,$dblink)
	{
		print "... Complete, duration: $duration seconds.\r\n";
		$query = "UPDATE tbljobs SET lastrun=NOW(),active=0 WHERE name='$job'";
		$result2 = $dblink->query($query) OR dberror($dblink,$query);
		$query = "INSERT INTO tbljoblog SET moment=NOW(),job='$job',message='$message',duration=$duration";
		$result2 = $dblink->query($query) OR dberror($dblink,$query);
	}

	function database_maintenance($dblink)
	{
		// calculate times & dates.
		$query = "SELECT DATE_ADD(NOW(),INTERVAL -2 DAY),DATE_ADD(NOW(),INTERVAL -7 DAY),DATE_ADD(NOW(),INTERVAL -30 DAY)";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$dates = $result->fetch_row();
		// dates[0] = 2 days ago
		// dates[1] = 7 days ago
		// dates[2] = 30 days ago

		$query = "DELETE FROM tblxratehistory WHERE moment<'$dates[0]'"; // delete exchange rate history older than 2 days
		$dblink->query($query) OR dberror($dblink,$query);
		$query = "ALTER TABLE `tblxratehistory` ORDER BY `id`"; // reorder Exchange Rate History table
		$dblink->query($query) OR dberror($dblink,$query);

		$query = "DELETE FROM tbljoblog WHERE moment<'$dates[1]'"; // delete job history older than 7 days
		$dblink->query($query) OR dberror($dblink,$query);
		$query = "ALTER TABLE `tbljoblog` ORDER BY `id`"; // reorder Job History table
		$dblink->query($query) OR dberror($dblink,$query);

		$query = "DELETE FROM tbldifficulty WHERE moment<'$dates[2]'"; // delete difficulty history older than 30 days
		$dblink->query($query) OR dberror($dblink,$query);
		$query = "ALTER TABLE `tbldifficulty` ORDER BY `moment`"; // reorder difficulty History table
		$dblink->query($query) OR dberror($dblink,$query);

		$query = "DELETE FROM tblpoolhashrate WHERE moment<'$dates[2]'"; // delete pool hashrate history older than 30 days
		$dblink->query($query) OR dberror($dblink,$query);
		$query = "ALTER TABLE `tblpoolhashrate` ORDER BY `moment`"; // reorder pool hashrate History table
		$dblink->query($query) OR dberror($dblink,$query);

		$query = "DELETE FROM tblnodehashrate WHERE moment<'$dates[2]'"; // delete node hashrate history older than 30 days
		$dblink->query($query) OR dberror($dblink,$query);
		$query = "ALTER TABLE `tblnodehashrate` ORDER BY `moment`"; // reorder node hashrate History table
		$dblink->query($query) OR dberror($dblink,$query);

		$query = "DELETE FROM tblshares WHERE lastupdate<='$dates[1]' AND type='Invalid'";
		$dblink->query($query) OR dberror($dblink,$query);

		$query = "DELETE FROM tblminerdiff WHERE moment<='$dates[0]'"; // delete miner difficulty older than 2 days.
		$dblink->query($query) OR dberror($dblink,$query);

		$query = "DELETE FROM tblnodeavailability WHERE moment<'$dates[1]'"; // delete node availability older than 7 days
		$dblink->query($query) OR dberror($dblink,$query);
		$query = "ALTER TABLE `tblnodeavailability` ORDER BY `moment`"; // reorder node availability table
		$dblink->query($query) OR dberror($dblink,$query);

		$query = "DELETE FROM tblminerpayout WHERE moment<'$dates[1]'"; // delete miner payout older than 7 days
		$dblink->query($query) OR dberror($dblink,$query);
		$query = "ALTER TABLE `tblminerpayout` ORDER BY `moment`"; // reorder miner payout table
		$dblink->query($query) OR dberror($dblink,$query);

		$query = "DELETE FROM tblrpcmonitor WHERE moment<'$dates[0]'"; // delete tblrpcmonitor older than 2 days
		$dblink->query($query) OR dberror($dblink,$query);
		$query = "ALTER TABLE `tblrpcmonitor` ORDER BY `moment`"; // reorder tblrpcmonitor table
		$dblink->query($query) OR dberror($dblink,$query);
	}
?>