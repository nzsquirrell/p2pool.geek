<?php
	require_once('../include/database.inc.php');
	require_once('../include/functions.inc.php');

	$algos[0] = 'SHA256d';
	$algos[1] = 'Scrypt';
	$algos[2] = 'Myr-Groestl';
	$algos[3] = 'Skein';
	$algos[4] = 'Qubit';

	$dblink = dbinit();
	$info = new stdClass;


	$info->Peers = array();

	$query = "SELECT address,direction,version,INET_ATON(address) as ip FROM tblmyrpeers WHERE connected=1 ORDER BY ip";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$i=0;
	while($row = $result->fetch_row())
	{
		$info->Peers[$i] = new stdClass;
		$info->Peers[$i]->address = $row[0];
		$info->Peers[$i]->direction = $row[1];
		$info->Peers[$i]->version = $row[2];
		$info->Peers[$i]->versionid = wallet_versionid($row[2]);
		$i++;
	}

	$info->PeerCount = $i;

	function wallet_versionid($version)
	{
		$parts = explode('.',$version);
		$id = $parts[3] + 100 * $parts[2] + 10000 * $parts[1] + 1000000 * $parts[0];
		return $id;
	}

	function wallet_version($versionid)
	{
		$p3 = ($versionid % 100);
		$versionid = floor($versionid/100);
		$p2 = ($versionid % 100);
		$versionid = floor($versionid/100);
		$p1 = ($versionid % 100);
		$versionid = floor($versionid/100);
		$p0 = ($versionid % 100);

		$v = $p0 . '.' . $p1 . '.' . $p2 . '.' . $p3;

		return $v;
	}

	$query = "SELECT (3600*24*7)/COUNT(hash) FROM tblnetworkblocks WHERE moment>=DATE_ADD(NOW(),INTERVAL (-3600*24*7) SECOND)";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();
	$secsperblock = $row[0];

	$query = "SELECT updated,data FROM tblmiscdata WHERE name='myrinfo'";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();

	$info->Updated = gmdate('D jS M H:i:s e',strtotime($row[0]));

	$data = json_decode($row[1]);

	$info->VersionID = $data->version;
	$info->Version = wallet_version($data->version);
	$info->Blocks = $data->blocks;
	$info->SHA_diff = $data->difficulty_sha256d;
	$info->SCR_diff = $data->difficulty_scrypt;
	$info->MGR_diff = $data->difficulty_groestl;
	$info->SKN_diff = $data->difficulty_skein;
	$info->QUB_diff = $data->difficulty_qubit;

	$info->SHA_hash = scale_rate($data->difficulty_sha256d * pow(2,32) / 150);
	$info->SCR_hash = scale_rate($data->difficulty_scrypt * pow(2,32) / 150);
	$info->MGR_hash = scale_rate($data->difficulty_groestl * pow(2,32) / 150);
	$info->SKN_hash = scale_rate($data->difficulty_skein * pow(2,32) / 150);
	$info->QUB_hash = scale_rate($data->difficulty_qubit * pow(2,32) / 150);

 	$BlocksTillHalf = 967680 - ($data->blocks % 967680);
 	$info->BlocksTillHalf = $BlocksTillHalf . ' (' . sprintf("%01.01f%%",round(100*($data->blocks % 967680)/967680,1)) . ')';
 	$info->HalvingDate = gmdate('g:ia l, jS F Y e',time() + $secsperblock * $BlocksTillHalf);
	$info->BlockValue = sprintf("%01.08f MYR",round(1000 >> floor($data->blocks/967680),8));

	$dblink->close();

	header('Content-type: application/json');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

	echo json_encode($info);


?>