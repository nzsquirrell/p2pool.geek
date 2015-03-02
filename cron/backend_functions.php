<?php


	function get_p2pinfo($location,$nodeinfo) // get info from p2pool node specified in $nodeinfo
	{
		$msg = file_get_contents('http://' . $nodeinfo->hostname . ':' . $nodeinfo->port . "/$location");
		return json_decode($msg);
	}


	function get_p2p_info_local($location) // get info from p2pool local node as per $config
	{
		global $config;

		$msg = file_get_contents('http://' . $config['p2pool2_host'] . ':' . $config['p2pool2_port'] . "/$location");
		return json_decode($msg);
	}


	function get_p2pinfo_curl($location,$nodeinfo)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://' . $nodeinfo->hostname . ':' . $nodeinfo->port . "/$location");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$msg = curl_exec($ch);
		return json_decode($msg);
	}

	function get_p2p_info_db($node,$stat,$dblink)
	{
		$query = "SELECT data FROM tblp2poolinfo WHERE node='$node' AND stat='$stat'";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();
		return json_decode($row[0]);
	}


?>