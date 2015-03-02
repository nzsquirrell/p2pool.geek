<?php

	require_once('config.inc.php');

	function dbinit()
	{
		global $config;

		$dblink = mysqli_init();
		if (!$dblink)
		{
	    	die('mysqli_init failed');
		}
		if(!$dblink->real_connect($config['database_host'],$config['database_user'],$config['database_password'],$config['database_name']))
		{
			die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
		}
		return $dblink;
	}

	function dberror($dblink,$query)
	{
		die('Database Error: ' . $dblink->error . '. Query: ' . $query);
	}

?>