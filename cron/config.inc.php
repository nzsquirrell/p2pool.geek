<?php
	// Site Configuration file.

	$config['database_host'] = 'dbhost'; 	// hostname of MySQL / Maria Database server
	$config['database_name'] = 'myriad'; 		// name of the database
	$config['database_user'] = 'myriadweb';		// SQL username with rights to database - NOT root!
	$config['database_password'] = 'Password';		// SQL Password for user.


	$config['wallet_rpchost'] = '172.16.55.200';		// hostname where wallet daemon is running
	$config['wallet_rpcport'] = '10889';					// which port to connect to for RPC data
	$config['wallet_rpcuser'] = 'memememe';			// RPC user
	$config['wallet_rpcpassword'] = 'blablabla';	// RPC password - ensure it is long and secure!!!
	$config['wallet_rpcprotocol'] = 'http';
	$config['wallet_password'] = '';


?>