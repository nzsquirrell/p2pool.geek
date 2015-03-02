<?php
	require_once('../include/database.inc.php');
	require_once('../include/functions.inc.php');

	$dblink = dbinit();

	$algos[0] = 'SHA256';
	$algos[1] = 'Scrypt';
	$algos[2] = 'Myr-Groestl';
	$algos[3] = 'Skein';
	$algos[4] = 'Qubit';

	$info = array();

	$query = "SELECT lastupdate,info FROM tblcoininfo WHERE coin='MYR'";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();

	$updated = strtotime($row[0]);
	$data = json_decode($row[1]);

	$info['MYR'] = new stdClass;
	$info['MYR']->Updated = $updated;
	$info['MYR']->AuxPoW = false;

	$info['MYR']->Algorithms = array();
	$info['MYR']->Algorithms[0] = new stdClass;
	$info['MYR']->Algorithms[0]->Name = $algos[0];
	$info['MYR']->Algorithms[0]->Difficulty = $data->difficulty_sha256d;
	$info['MYR']->Algorithms[0]->Hashrate = round((pow(2,32) * $data->difficulty_sha256d) / 150,0);
	$info['MYR']->Algorithms[1] = new stdClass;
	$info['MYR']->Algorithms[1]->Name = $algos[1];
	$info['MYR']->Algorithms[1]->Difficulty = $data->difficulty_scrypt;
	$info['MYR']->Algorithms[1]->Hashrate = round((pow(2,32) * $data->difficulty_scrypt) / 150,0);
	$info['MYR']->Algorithms[2] = new stdClass;
	$info['MYR']->Algorithms[2]->Name = $algos[2];
	$info['MYR']->Algorithms[2]->Difficulty = $data->difficulty_groestl;
	$info['MYR']->Algorithms[2]->Hashrate = round((pow(2,32) * $data->difficulty_groestl) / 150,0);
	$info['MYR']->Algorithms[3] = new stdClass;
	$info['MYR']->Algorithms[3]->Name = $algos[3];
	$info['MYR']->Algorithms[3]->Difficulty = $data->difficulty_skein;
	$info['MYR']->Algorithms[3]->Hashrate = round((pow(2,32) * $data->difficulty_skein) / 150,0);
	$info['MYR']->Algorithms[4] = new stdClass;
	$info['MYR']->Algorithms[4]->Name = $algos[4];
	$info['MYR']->Algorithms[4]->Difficulty = $data->difficulty_qubit;
	$info['MYR']->Algorithms[4]->Hashrate = round((pow(2,32) * $data->difficulty_qubit) / 150,0);



	$query = "SELECT tblmergedcoins.symbol,tblmergedcoins.network,tblcoininfo.lastupdate,tblcoininfo.info FROM tblmergedcoins,tblcoininfo WHERE tblmergedcoins.symbol=tblcoininfo.coin AND tblmergedcoins.enabled=1";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		$coin = $row[0];
		$network = $row[1];
		$data = json_decode($row[3]);

		$info[$coin] = new stdClass;
		$info[$coin]->Updated = strtotime($row[2]);
		$info[$coin]->AuxPoW = true;
		$info[$coin]->Algorithms = array();


		if($coin=='HUC')
		{
			$info[$coin]->Algorithms[0] = new stdClass;
			$info[$coin]->Algorithms[0]->Name = $algos[0];
			$info[$coin]->Algorithms[0]->Difficulty = $data->difficulty_sha256d;
			$info[$coin]->Algorithms[0]->Hashrate = round((pow(2,32) * $data->difficulty_sha256d) / 120,0);;
			$info[$coin]->Algorithms[1] = new stdClass;
			$info[$coin]->Algorithms[1]->Name = $algos[1];
			$info[$coin]->Algorithms[1]->Difficulty = $data->difficulty_scrypt;
			$info[$coin]->Algorithms[1]->Hashrate = round((pow(2,32) * $data->difficulty_scrypt) / 120,0);;

		}
		else
		{
			$info[$coin]->Algorithms[0] = new stdClass;
			$info[$coin]->Algorithms[0]->Name = $algos[$network];
			$info[$coin]->Algorithms[0]->Difficulty = $data->difficulty;
			$info[$coin]->Algorithms[0]->Hashrate = $data->networkhashps;
		}
	}


	header('Content-type: application/json');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

	echo json_encode($info);

?>
