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
			log_adminaction($dblink,'JobEnable',"Enabled job $id.");
		}
		else
		{
			$state='N';
			log_adminaction($dblink,'JobDisable',"Disabled job $id.");
		}
 		$query = "UPDATE tbljobs SET enabled='$state' WHERE name='$id'";
 		$dblink->query($query) OR dberror($dblink,$query);

		$header = 'Location: ' . $config['vcp_proto'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
		header($header);
		exit;
	}

	if($action=='viewlogs')
	{
		$display = $action;
	}
	else
	{
		$display = '';
	}

	require_once('../include/functions.inc.php');
	require_once('../include/header.php');

	$dblink = dbinit();
?>
<div class="container">

<?php
	require_once('../include/pageheader.php');
?>

	<ul class="nav nav-tabs">
		<li><a href="/home">Pool</a></li>
		<li><a href="/vcp">Control Panel Home</a></li>
		<li class="active"><a href="/vcp/jobs.php">Job Management</a></li>
		<li><a href="/vcp/alerts.php">Alerts</a></li>
		<li><a href="/vcp/miners.php">Miner Management</a></li>
		<li><a href="/vcp/blocks.php">Recent Blocks</a></li>
		<li><a href="/vcp/dashboard.php">Dashboard</a></li>
	</ul>

	<p></p>
	<p>System Time: <?php echo gmdate('M d H:i:s e'); ?></p>

<?php
	if($display=='')
	{
		log_adminaction($dblink,'Job',"Viewed Jobs Page.");
?>
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">System Jobs</h3>
		</div>
		<div class="panel-body">
			<table class="table table-condensed table-striped">
				<thead>
					<tr>
						<th>Name</th>
						<th>Description</th>
						<th>Enabled</th>
						<th>Interval</th>
						<th>Last Run</th>
						<th>Active</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
<?php
		$query = "SELECT * FROM tbljobs ORDER BY name";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$d = gmdate('M d H:i:s e',strtotime($row[4]));
			print '<tr>';
			print "<td>$row[0]</td><td>$row[1]</td><td>$row[2]</td><td>$row[3]</td><td>$d</td>";
			if($row[6]==1)
			{
				print '<td><span class="label label-danger">Yes</label></td>';
			}
			else
			{
				print '<td><span class="label label-primary">No</span></td>';
			}

			if($row[2]=='N')
			{
				$clink = "jobs.php?action=enable&id=$row[0]";
				print "<td><a href=\"$clink\"><button type=\"button\" class=\"btn btn-success btn-xs\">Enable</button></a>";
			}
			else
			{
				$clink = "jobs.php?action=disable&id=$row[0]";
				print "<td><a href=\"$clink\"><button type=\"button\" class=\"btn btn-warning btn-xs\">Disable</button></a>";
			}

			$vlink = "jobs.php?action=viewlogs&id=$row[0]";
			print " <a href=\"$vlink\"><button type=\"button\" class=\"btn btn-primary btn-xs\">View Logs</button></a></td>";
			print '</tr>';
		}
?>
				</tbody>
			</table>
		</div>
	</div>

<?php
	}
	elseif($display=='viewlogs')
	{
		$job = $dblink->real_escape_string($_GET['id']);
		log_adminaction($dblink,'JobLogs',"Viewed logs for job $job.");
?>
	<p><button class="btn btn-primary" onclick="window.location='<?php echo $_SERVER['PHP_SELF']; ?>';">Return to Job Management</button></p>
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Last 30 logs for <?php echo $job; ?></h3>
		</div>
		<div class="panel-body">
			<table class="table table-condensed table-striped">
				<thead>
					<tr>
						<th>Date & Time</th>
						<th>Duration</th>
						<th>Log</th>
					</tr>
				</thead>
				<tbody>


<?php
		$query = "SELECT * FROM tbljoblog WHERE job='$job' ORDER BY moment DESC LIMIT 0,20";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			print '<tr>';
			$d = gmdate('M d H:i:s e',strtotime($row[1]));
			if($row[4]!=1)
			{
				$t = "$row[4] seconds";
			}
			else
			{
				$t = "$row[4] second";
			}
			print "<td>$d</td><td>$t</td><td><pre>$row[3]</pre></td>";
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
