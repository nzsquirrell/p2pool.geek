<?php
	require_once('../include/config.inc.php');
	require_once('vcp.config.php');
	require_once('../include/database.inc.php');
	require_once('../include/functions.inc.php');

	$dblink = dbinit();

	if(isset($_GET['action']))
	{
		$action = $_GET['action'];
	}
	elseif(isset($_POST['action']))
	{
		$action = $_POST['action'];
	}
	else
	{
		$action = '';
	}

	if($action=='saveedit' || $action=='savenew')
	{
		$vtc = $dblink->real_escape_string($_POST['vtc']);
		$mon = $dblink->real_escape_string($_POST['mon']);
		$plx = $dblink->real_escape_string($_POST['plx']);
		$notes = $dblink->real_escape_string($_POST['notes']);
		$monmin = round($dblink->real_escape_string($_POST['monmin']),8);
		$plxmin = round($dblink->real_escape_string($_POST['plxmin']),8);
		$mondon = round($dblink->real_escape_string($_POST['mondon']),1);
		$plxdon = round($dblink->real_escape_string($_POST['plxdon']),1);
		if($action=='saveedit')
		{
			$address = $dblink->real_escape_string($_POST['address']);
			$query = "UPDATE tblminers SET address_vtc='$vtc',address_mon='$mon',address_plx='$plx',notes='$notes',mon_minpayment='$monmin',plx_minpayment='$plxmin',mon_donation='$mondon',plx_donation='$plxdon' WHERE address_vtc='$address'";
			log_adminaction($dblink,'SaveMiner',"Saved changes to miner $notes.\r\nVTC=$vtc\r\nMON=$mon\r\nPLX=$plx.");
		}
		else
		{
			$query = "INSERT INTO tblminers SET address_vtc='$vtc',address_mon='$mon',address_plx='$plx',notes='$notes',lastseen='0000-00-00',lastupdate=NOW(),mon_minpayment='$monmin',plx_minpayment='$plxmin',mon_donation='$mondon',plx_donation='$plxdon'";
			log_adminaction($dblink,'AddMiner',"Added a new miner - $notes.\r\nVTC=$vtc\r\nMON=$mon\r\nPLX=$plx.");
		}
		$dblink->query($query) OR dberror($dblink,$query);

		$header = 'Location: ' . $config['vcp_proto'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
		header($header);
		exit;

	}

	if($action=='deletereg' || $action=='markdone')
	{
		$regid = $dblink->real_escape_string($_GET['regid']);
		if($action=='markdone')
		{
			$state='DONE';
			log_adminaction($dblink,'RegDone',"Marked registration for $regid as Done.");
		}
		elseif($action=='deletereg')
		{
			$state='DELETE';
			log_adminaction($dblink,'RegDelete',"Marked registration for $regid as Deleted.");
		}
		else
		{
			$state='NEW';
		}
		$query = "UPDATE tblregistrations SET state='$state' WHERE id='$regid'";
		$dblink->query($query) OR dberror($dblink,$query);

		$header = 'Location: ' . $config['vcp_proto'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
		header($header);
		exit;
	}

	if($action=='delete' || $action=='edit' || $action=='add')
	{
		$display = $action;
	}
	else
	{
		$display = '';
	}

	require_once('../include/functions.inc.php');
	require_once('../include/header.php');

?>
<div class="container">

<?php
	require_once('../include/pageheader.php');
?>

	<ul class="nav nav-tabs">
		<li><a href="/home">Pool</a></li>
		<li><a href="/vcp">Control Panel Home</a></li>
		<li><a href="/vcp/jobs.php">Job Management</a></li>
		<li><a href="/vcp/alerts.php">Alerts</a></li>
		<li class="active"><a href="/vcp/miners.php">Miner Management</a></li>
		<li><a href="/vcp/blocks.php">Recent Blocks</a></li>
		<li><a href="/vcp/dashboard.php">Dashboard</a></li>
	</ul>

	<p></p>
	<p>System Time: <?php echo gmdate('M d H:i:s e'); ?></p>

<?php
	if($display=='delete')
	{
		log_adminaction($dblink,'MinerDelete','Delete Confirmation.');
?>

	<div class="panel panel-info">
		<div class="panel-heading">
			<h3 class="panel-title">Confirm Deletion</h3>
		</div>
		<div class="panel-body">
		</div>
	</div>

<?php
	}
	elseif($display=='edit')
	{
		$address = $dblink->real_escape_string($_GET['address']);
		$query = "SELECT * FROM tblminers WHERE address_vtc='$address'";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();
		log_adminaction($dblink,'MinerEdit',"View details for miner $row[0].");
?>

	<div class="panel panel-info">
		<div class="panel-heading">
			<h3 class="panel-title">Edit Miner</h3>
		</div>
		<div class="panel-body">
			<form action="miners.php" method="POST">
				<input type="hidden" name="action" value="saveedit">
				<input type="hidden" name="address" value="<?php echo $address; ?>">
				<div class="form-group">
					<label for="vtc">Vertcoin Address</label>
					<input class="form-control" id="vtc" name="vtc" placeholder="Enter Address of 34 Characters" value="<?php echo $row[0]; ?>" maxlength="34">
				</div>
				<div class="form-group">
					<label for="mon">Monocle Address</label>
					<input class="form-control" id="mon" name="mon" placeholder="Enter Address of 34 Characters" value="<?php echo $row[1]; ?>" maxlength="34">
				</div>
				<div class="form-group">
					<label for="plx">ParallaxCoin Address</label>
					<input class="form-control" id="plx" name="plx" placeholder="Enter Address of 34 Characters" value="<?php echo $row[2]; ?>" maxlength="34">
				</div>
				<div class="form-group">
					<label for="notes">Information &amp; Contact Details</label>
					<input class="form-control" id="notes" name="notes" placeholder="Enter Email Address or Reddit Handle" value="<?php echo $row[11]; ?>" maxlength="256">
				</div>
				<div class="form-group">
					<label for="monmin">Minimum MON Payout</label>
					<input class="form-control" id="monmin" name="monmin" placeholder="1.00000000" value="<?php echo $row[12]; ?>" maxlength="15">
				</div>
				<div class="form-group">
					<label for="plxmin">Minimum PLX Payout</label>
					<input class="form-control" id="plxmin" name="plxmin" placeholder="0.00000000" value="<?php echo $row[14]; ?>" maxlength="15">
				</div>
				<div class="form-group">
					<label for="mondon">MON Donation Percentage</label>
					<input class="form-control" id="mondon" name="mondon" placeholder="0.0" value="<?php echo $row[16]; ?>" maxlength="15">
				</div>
				<div class="form-group">
					<label for="plxdon">PLX Donation Percentage</label>
					<input class="form-control" id="plxdon" name="plxdon" placeholder="0.0" value="<?php echo $row[17]; ?>" maxlength="15">
				</div>
				<input type="submit" class="btn btn-success" value="Yes, Save Changes">
				<input type="button" class="btn btn-info" value="No, Forget It" onclick="window.location='<?php echo $_SERVER['PHP_SELF']; ?>';">
			</form>
		</div>
	</div>

<?php
	}
	elseif($display=='add')
	{
		log_adminaction($dblink,'MinerAdd','Adding new miner.');
?>

	<div class="panel panel-info">
		<div class="panel-heading">
			<h3 class="panel-title">Add New Miner</h3>
		</div>
		<div class="panel-body">
			<form action="miners.php" method="POST">
				<input type="hidden" name="action" value="savenew">
				<div class="form-group">
					<label for="vtc">Vertcoin Address</label>
					<input class="form-control" id="vtc" name="vtc" placeholder="Enter Address of 34 Characters" maxlength="34">
				</div>
				<div class="form-group">
					<label for="mon">Monocle Address</label>
					<input class="form-control" id="mon" name="mon" placeholder="Enter Address of 34 Characters" maxlength="34">
				</div>
				<div class="form-group">
					<label for="plx">ParallaxCoin Address</label>
					<input class="form-control" id="plx" name="plx" placeholder="Enter Address of 34 Characters" maxlength="34">
				</div>
				<div class="form-group">
					<label for="notes">Information &amp; Contact Details</label>
					<input class="form-control" id="notes" name="notes" placeholder="Enter Email Address or Reddit Handle" maxlength="256">
				</div>
				<div class="form-group">
					<label for="monmin">Minimum MON Payout</label>
					<input class="form-control" id="monmin" name="monmin" placeholder="1.00000000" value="1.00000000" maxlength="15">
				</div>
				<div class="form-group">
					<label for="plxmin">Minimum PLX Payout</label>
					<input class="form-control" id="plxmin" name="plxmin" placeholder="0.00000000" value="10.00000000" maxlength="15">
				</div>
				<div class="form-group">
					<label for="mondon">MON Donation Percentage</label>
					<input class="form-control" id="mondon" name="mondon" placeholder="0.0" value="0.0" maxlength="15">
				</div>
				<div class="form-group">
					<label for="plxdon">PLX Donation Percentage</label>
					<input class="form-control" id="plxdon" name="plxdon" placeholder="0.0" value="0.0" maxlength="15">
				</div>
				<input type="submit" class="btn btn-success" value="Yes, Save It">
				<input type="button" class="btn btn-info" value="No, Forget It" onclick="window.location='<?php echo $_SERVER['PHP_SELF']; ?>';">
			</form>
		</div>
	</div>

<?php
	}
	elseif($display=='')
	{
		log_adminaction($dblink,'Miner','View Miners Page.');
?>
	<p><button class="btn btn-info" onclick="window.location='<?php echo $_SERVER['PHP_SELF']; ?>?action=add';">Add New Miner</button></p>

	<div class="panel panel-info">
		<div class="panel-heading">
			<h3 class="panel-title">Pending Registrations</h3>
		</div>
		<div class="panel-body">
			<table class="table table-condensed table-hover small">
				<thead>
					<tr>
						<th>Registration Date</th>
						<th>State</th>
						<th>Vertcoin Address</th>
						<th>Monocle Address</th>
						<th>ParallaxCoin Address</th>
						<th>Contact Details</th>
						<th>IP Address</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
<?php
		$query = "SELECT * FROM tblregistrations WHERE state!='DONE' ORDER BY moment ASC";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		if($result->num_rows>0)
		{
			while($row = $result->fetch_row())
			{
				print '<tr>';
				$time = gmdate('M d H:i:s e',strtotime($row[1]));
				print "<td>$time</td>";
				print "<td>$row[4]</td>";
				print "<td>$row[5]</td><td>$row[6]</td><td>$row[7]</td>";
				print "<td>$row[2]</td><td>$row[3]</td>";
				$alink = "miners.php?action=acceptnew&regid=$row[0]";
				$mlink = "miners.php?action=markdone&regid=$row[0]";
				$dlink = "miners.php?action=deletereg&regid=$row[0]";
				//print "<td><a href=\"$alink\"><button type=\"button\" class=\"btn btn-success btn-xs\">Accept</button></a> ";
				print "<td><a href=\"$mlink\"><button type=\"button\" class=\"btn btn-success btn-xs\">Completed</button></a> ";
				print "<a href=\"$dlink\"><button type=\"button\" class=\"btn btn-danger btn-xs\">Delete</button></a></td";
				print '</tr>';
			}
		}
		else
		{
			print '<tr><td colspan="3"><em>No pending registrations.</em></td></tr>';
		}
?>
				</tbody>
			</table>
		</div>
	</div>


	<div class="panel panel-info">
		<div class="panel-heading">
			<h3 class="panel-title">Miners seen in last 7 days</h3>
		</div>
		<div class="panel-body">
			<table class="table table-condensed table-hover small">
				<thead>
					<tr>
						<th>Vertcoin Address</th>
						<th>Last Seen</th>
						<th>Notes</th>
						<th>MON Balance</th>
						<th>PLX Balance</th>
						<th>Validity</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
<?php
		$query = "SELECT address_vtc,lastseen,notes,LENGTH(address_vtc),LENGTH(address_mon),LENGTH(address_plx),mon_balance,plx_balance FROM tblminers WHERE lastseen>=DATE_ADD(NOW(),INTERVAL -1 WEEK) ORDER BY address_vtc";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			print '<tr>';
			$vlink = "http://vert.geek.nz/miner/$row[0]";
			$elink = "miners.php?action=edit&address=$row[0]";
			$dlink = "miners.php?action=delete&address=$row[0]";
			if($row[1]=='0000-00-00 00:00:00')
			{
				$time = 'Never';
			}
			else
			{
				$time = gmdate('M d H:i:s e',strtotime($row[1]));
			}
			print "<td><a href=\"$vlink\" target=\"_blank\">$row[0]</a></td><td>$time</td><td>$row[2]</td>";
			$mclass = "";
			$pclass = "";
			if($row[6]>0)
			{
				$mclass = " class='danger'";
			}
			if($row[7]>0)
			{
				$pclass = " class='danger'";
			}
			print "<td $mclass>" . sprintf("%01.08f MON",$row[6]) . '</td>';
			print "<td $pclass>" . sprintf("%01.08f PLX",$row[7]) . '</td>';
			print '<td>';
			if($row[3]==34)
			{
				print '<button type="button" class="btn btn-success btn-sm">VTC</button>';
			}
			else
			{
				print '<button type="button" class="btn btn-danger btn-sm">VTC</button>';
			}
			if($row[4]==34)
			{
				print '<button type="button" class="btn btn-success btn-sm">MON</button>';
			}
			else
			{
				print '<button type="button" class="btn btn-danger btn-sm">MON</button>';
			}
			if($row[5]==34)
			{
				print '<button type="button" class="btn btn-success btn-sm">PLX</button>';
			}
			else
			{
				print '<button type="button" class="btn btn-danger btn-sm">PLX</button>	';
			}

			print '</td>';
			print "<td><a href=\"$elink\"><button type=\"button\" class=\"btn btn-primary btn-xs\">Edit</button></a> ";
			//print "<a href=\"$dlink\"><button type=\"button\" class=\"btn btn-danger btn-xs\">Delete</button></a></td";
			print '</td></tr>';
		}

?>
				</tbody>
			</table>
		</div>
	</div>

	<div class="panel panel-info">
		<div class="panel-heading">
			<h3 class="panel-title">Miners last seen more than 7 days ago</h3>
		</div>
		<div class="panel-body">
			<table class="table table-condensed table-hover small">
				<thead>
					<tr>
						<th>Vertcoin Address</th>
						<th>Last Seen</th>
						<th>Notes</th>
						<th>MON Balance</th>
						<th>PLX Balance</th>
						<th>Validity</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
<?php
		$query = "SELECT address_vtc,lastseen,notes,LENGTH(address_vtc),LENGTH(address_mon),LENGTH(address_plx),mon_balance,plx_balance FROM tblminers WHERE lastseen<DATE_ADD(NOW(),INTERVAL -1 WEEK) ORDER BY address_vtc";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			print '<tr>';
			$vlink = "http://vert.geek.nz/miner/$row[0]";
			$elink = "miners.php?action=edit&address=$row[0]";
			$dlink = "miners.php?action=delete&address=$row[0]";
			if($row[1]=='0000-00-00 00:00:00')
			{
				$time = 'Never';
			}
			else
			{
				$time = gmdate('M d H:i:s e',strtotime($row[1]));
			}
			print "<td><a href=\"$vlink\" target=\"_blank\">$row[0]</a></td><td>$time</td><td>$row[2]</td>";
			$mclass = "";
			$pclass = "";
			if($row[6]>0)
			{
				$mclass = " class='danger'";
			}
			if($row[7]>0)
			{
				$pclass = " class='danger'";
			}
			print "<td $mclass>" . sprintf("%01.08f MON",$row[6]) . '</td>';
			print "<td $pclass>" . sprintf("%01.08f PLX",$row[7]) . '</td>';
			print '<td>';
			if($row[3]==34)
			{
				print '<button type="button" class="btn btn-success btn-sm">VTC</button>';
			}
			else
			{
				print '<button type="button" class="btn btn-danger btn-sm">VTC</button>';
			}
			if($row[4]==34)
			{
				print '<button type="button" class="btn btn-success btn-sm">MON</button>';
			}
			else
			{
				print '<button type="button" class="btn btn-danger btn-sm">MON</button>';
			}
			if($row[5]==34)
			{
				print '<button type="button" class="btn btn-success btn-sm">PLX</button>';
			}
			else
			{
				print '<button type="button" class="btn btn-danger btn-sm">PLX</button>	';
			}

			print '</td>';
			print "<td><a href=\"$elink\"><button type=\"button\" class=\"btn btn-primary btn-xs\">Edit</button></a> ";
			//print "<a href=\"$dlink\"><button type=\"button\" class=\"btn btn-danger btn-xs\">Delete</button></a></td";
			print '</td></tr>';
		}

?>
				</tbody>
			</table>
		</div>
	</div>

<?php
	}
	require_once('../include/footer.php');
?>
