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

	if($action=='enable' || $action=='disable')
	{
		$id = $dblink->real_escape_string($_GET['id']);
		if($action=='enable')
		{
			$state='Y';
			log_adminaction($dblink,'AlertEnable',"Enabled Alert $id.");
		}
		else
		{
			$state='N';
			log_adminaction($dblink,'AlertDisable',"Disabled Alert $id.");
		}
		$query = "UPDATE tblalerts SET enabled='$state' WHERE id='$id'";
		$dblink->query($query) OR dberror($dblink,$query);

		$header = 'Location: ' . $config['vcp_proto'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
		header($header);
		exit;
	}

	if($action=='deletealert')
	{
		$id = $dblink->real_escape_string($_POST['id']);
		$query = "DELETE FROM tblalerts WHERE id='$id'";
		$dblink->query($query) OR dberror($dblink,$query);

		log_adminaction($dblink,'AlertDelete',"Deleted Alert $id.");
		$header = 'Location: ' . $config['vcp_proto'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
		header($header);
		exit;

	}

	if($action=='saveedit' || $action=='savenew')
	{
		$message = $dblink->real_escape_string($_POST['message']);
		$level = $dblink->real_escape_string($_POST['level']);
		$enabled = $dblink->real_escape_string($_POST['enabled']);

		if($action=='saveedit')
		{
			$id = $dblink->real_escape_string($_POST['id']);
			$query = "UPDATE tblalerts SET message='$message',level='$level',enabled='$enabled' WHERE id='$id'";
			log_adminaction($dblink,'AlertSaveEdit',"Save Changes to Alert $id.");
		}
		else
		{
			$query = "INSERT INTO tblalerts SET message='$message',level='$level',enabled='$enabled',displayorder='1'";
			log_adminaction($dblink,'AlertSaveAdd',"Saved new alert.");
		}
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
		<li class="active"><a href="/vcp/alerts.php">Alerts</a></li>
		<li><a href="/vcp/miners.php">Miner Management</a></li>
		<li><a href="/vcp/blocks.php">Recent Blocks</a></li>
		<li><a href="/vcp/dashboard.php">Dashboard</a></li>
	</ul>

	<p></p>
	<p>System Time: <?php echo gmdate('M d H:i:s e'); ?></p>

<?php
	if($display=='delete')
	{
		$id = $dblink->real_escape_string($_GET['id']);
		$query = "SELECT * FROM tblalerts WHERE id='$id'";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();
		log_adminaction($dblink,'AlertDelete',"Confirm Deletion for alert: $row[1].");
?>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Confirm Deletion</h3>
		</div>
		<div class="panel-body">
			<p class="text-center"><?php echo $row[1]; ?></p>
			<p class="text-center"><strong>Please confirm that you wish to delete this alert</strong></p>
			<form method="POST" action="alerts.php">
			<input type="hidden" name="action" value="deletealert">
			<input type="hidden" name="id" value="<?php echo $row[0]; ?>">
			<p class="text-center"><input type="submit" value="Yes, Delete" class="btn btn-danger">
			<input type="button" value="No, Don't Delete" onclick="window.location='<?php echo $_SERVER['PHP_SELF']; ?>';" class="btn btn-info"></p>
			</form>
		</div>
	</div>

<?php
	}
	elseif($display=='edit')
	{
		$id = $dblink->real_escape_string($_GET['id']);
		$query = "SELECT * FROM tblalerts WHERE id='$id'";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();
		log_adminaction($dblink,'AlertEdit',"Editing alert: $row[1].");
?>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Edit Alert</h3>
		</div>
		<div class="panel-body">
			<form action="alerts.php" method="POST">
				<input type="hidden" name="action" value="saveedit">
				<input type="hidden" name="id" value="<?php echo $row[0]; ?>">
				<div class="form-group">
					<label for="message">Message</label>
					<textarea class="form-control" rows="3" name="message" id="message"><?php echo $row[1]; ?></textarea>
				</div>
				<div class="form-group">
					<label for="severity">Severity</label>
					<select class="form-control" name="level" id="severity">
<?php
		if($row[3]=='info')
		{
			print '<option value="info" selected>Information</option><option value="warning">Warning</option><option value="critical">Critical</option>';
		}
		elseif($row[3]=='warning')
		{
			print '<option value="info">Information</option><option value="warning" selected>Warning</option><option value="critical">Critical</option>';
		}
		else
		{
			print '<option value="info">Information</option><option value="warning">Warning</option><option value="critical" selected>Critical</option>';
		}
?>
					</select>
				</div>
				<div class="form-group">
					<label for="enabled">Enabled</label>
					<select class="form-control" name="enabled" id="enabled">
<?php
		if($row[2]=='Y')
		{
			print '<option value="Y" selected>Yes</option><option value="N">No</option>';
		}
		else
		{
			print '<option value="Y">Yes</option><option value="N" selected>No</option>';
		}
?>
					</select>
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
		log_adminaction($dblink,'AlertAdd',"Adding new alert.");
?>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Add New Alert</h3>
		</div>
		<div class="panel-body">
			<form action="alerts.php" method="POST">
				<input type="hidden" name="action" value="savenew">
				<div class="form-group">
					<label for="message">Message</label>
					<textarea class="form-control" rows="3" name="message" id="message"><?php echo $row[1]; ?></textarea>
				</div>
				<div class="form-group">
					<label for="severity">Severity</label>
					<select class="form-control" name="level" id="severity">
							<option value='info'>Information</option>
							<option value='warning'>Warning</option>
							<option value='critical'>Critical</option>
					</select>
				</div>
				<div class="form-group">
					<label for="enabled">Enabled</label>
					<select class="form-control" name="enabled" id="enabled">
						<option value='Y'>Yes</option>
						<option value='N'>No</option>
					</select>
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
		log_adminaction($dblink,'Alert',"Accessed Alerts Page.");
?>
	<p><button class="btn btn-primary" onclick="window.location='<?php echo $_SERVER['PHP_SELF']; ?>?action=add';">Add New Alert</button></p>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Alerts</h3>
		</div>
		<div class="panel-body">
			<table class="table table-condensed table-hover">
				<thead>
					<tr>
						<th>Message</th>
						<th>Enabled</th>
						<th>Severity</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
<?php
		$query = "SELECT * FROM tblalerts ORDER BY id";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			print '<tr>';
			$elink = "alerts.php?action=edit&id=$row[0]";
			$dlink = "alerts.php?action=delete&id=$row[0]";
			print "<td>$row[1]</td><td>$row[2]</td><td>$row[3]</td><td>";
			if($row[2]=='N')
			{
				$clink = "alerts.php?action=enable&id=$row[0]";
				print "<a href=\"$clink\"><button type=\"button\" class=\"btn btn-success btn-xs\">Enable</button></a>";
			}
			else
			{
				$clink = "alerts.php?action=disable&id=$row[0]";
				print "<a href=\"$clink\"><button type=\"button\" class=\"btn btn-warning btn-xs\">Disable</button></a>";
			}
			print "<a href=\"$elink\"><button type=\"button\" class=\"btn btn-primary btn-xs\">Edit</button></a>";
			print "<a href=\"$dlink\"><button type=\"button\" class=\"btn btn-danger btn-xs\">Delete</button></a></td";
			print '</tr>';
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
