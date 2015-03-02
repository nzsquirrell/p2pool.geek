<?php



// ------------------ VTC RPC ------------------------- // ------------------ VTC RPC ------------------------- //

	function vtc_rpc($command)
	{
		global $config;
		$result = new stdClass;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $config['wallet_rpcprotocol'] . '://' . $config['wallet_rpchost'] . ':' . $config['wallet_rpcport']);
		curl_setopt($ch, CURLOPT_POST, 1);
		$result->command = $command;
		curl_setopt($ch, CURLOPT_POSTFIELDS, $result->command);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_USERPWD, $config['wallet_rpcuser'] . ':' . $config['wallet_rpcpassword']);

		$result->raw_output = curl_exec($ch);
		$result->json_output = json_decode($result->raw_output);

		$result->curl_error = curl_error($ch);
		$result->curl_errno = curl_errno($ch);

		if($result->curl_errno==0)
		{
			if(isset($result->json_output->id))
			{
				if($result->json_output->error==null)
				{
					$result->status = 'SUCCESS';
					$result->statusmsg = '';
				}
				else
				{
					$result->status = 'FAIL';
					$result->statusmsg = $result->json_output->error->message;
				}
			}
			else
			{
				$result->status = 'FAIL';
				$result->statusmsg = $result->raw_output;
			}
		}
		else
		{
			$result->status = 'FAIL';
			$result->statusmsg = $result->curl_error;
		}
		return $result;

	}

	function vtc_validate_address($address)
	{
		global $config;
		$command = '{"jsonrpc": "1.0", "id":"curltest", "method": "validateaddress", "params": ["' . $address . '"] }';
		$result = vtc_rpc($command);
		return $result;
	}

	function vtc_mininginfo()
	{
		global $config;
		$command = '{"jsonrpc": "1.0", "id":"curltest", "method": "getmininginfo", "params": [  ] }';
		$result = vtc_rpc($command);
		return $result;
	}

	function vtc_verifymessage($address, $message, $signature)
	{
		global $config;
		$command = '{"jsonrpc": "1.0", "id":"curltest", "method": "verifymessage", "params": [ "' . $address . '","' . $signature . '","' . $message . '" ] }';
		//print "\r\n JSON Command:  $command \r\n";
		$result = vtc_rpc($command);
		//print " Raw Output: $result->raw_output \r\n\r\n";
		return $result;
	}



// ------------------ RPC Generic ------------------------- // ------------------ RPC Generic ------------------------- //



	function rpc_base($wallet,$command, $timeout=10)
	{
		$result = new stdClass;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $wallet['rpcscheme'] . '://' . $wallet['rpchost'] . ':' . $wallet['rpcport']);
		curl_setopt($ch, CURLOPT_POST, 1);
		$result->command = $command;
		curl_setopt($ch, CURLOPT_POSTFIELDS, $result->command);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_USERPWD, $wallet['rpcuser'] . ':' . $wallet['rpcpass']);

		$result->raw_output = curl_exec($ch);
		$result->json_output = json_decode($result->raw_output);

		$result->curl_error = curl_error($ch);
		$result->curl_errno = curl_errno($ch);

		if($result->curl_errno==0)
		{
			if(isset($result->json_output->id))
			{
				if($result->json_output->error==null)
				{
					$result->status = 'SUCCESS';
					$result->statusmsg = '';
				}
				else
				{
					$result->status = 'FAIL';
					$result->statusmsg = $result->json_output->error->message;
				}
			}
			else
			{
				$result->status = 'FAIL';
				$result->statusmsg = $result->raw_output;
			}
		}
		else
		{
			$result->status = 'FAIL';
			$result->statusmsg = $result->curl_error;
		}
		return $result;

	}

	function rpc_send($wallet,$address,$amount,$timeout=10)
	{
		global $config;
		$command = '{"jsonrpc": "1.0", "id":"curltest", "method": "sendtoaddress", "params": [ "' . $address . '", ' . $amount . '] }';
		$result = rpc_base($wallet,$command,$timeout);
		return $result;
	}

	function rpc_lock($wallet)
	{
		global $config;
		$command = '{"jsonrpc": "1.0", "id":"curltest", "method": "walletlock", "params": [ ] }';
		$result = rpc_base($wallet,$command);
		return $result;
	}

	function rpc_mininginfo($wallet)
	{
		global $config;
		$command = '{"jsonrpc": "1.0", "id":"curltest", "method": "getmininginfo", "params": [  ] }';
		$result = rpc_base($wallet,$command);
		return $result;
	}

	function rpc_getinfo($wallet)
	{
		global $config;
		$command = '{"jsonrpc": "1.0", "id":"curltest", "method": "getinfo", "params": [  ] }';
		$result = rpc_base($wallet,$command);
		return $result;
	}

	function rpc_unlock($wallet,$time)
	{
		global $config;
		$command = '{"jsonrpc": "1.0", "id":"curltest", "method": "walletpassphrase", "params": [ "' . $wallet['rpckey'] . '", ' . $time . '] }';
		$result = rpc_base($wallet,$command);
		return $result;
	}

	function rpc_transactions($wallet,$count)
	{
		global $config;
		$command = '{"jsonrpc": "1.0", "id":"curltest", "method": "listtransactions", "params": ["", ' . $count . '] }';
		$result = rpc_base($wallet,$command);
		return $result;
	}

	function rpc_sendmany($wallet,$payments,$timeout=10)
	{
		global $config;
		if(is_array($payments))
		{
			$jpay = json_encode($payments);
			$command = '{"jsonrpc": "1.0", "id":"curltest", "method": "sendmany", "params": [ "", ' . $jpay . '] }';
			$result = rpc_base($wallet,$command,$timeout);
			return $result;
		}
	}

	function rpc_validate_address($wallet,$address)
	{
		global $config;
		$command = '{"jsonrpc": "1.0", "id":"curltest", "method": "validateaddress", "params": ["' . $address . '"] }';
		$result = rpc_base($wallet,$command);
		return $result;
	}

	function rpc_gettransaction($wallet,$txid)
	{
		global $config;
		$command = '{"jsonrpc": "1.0", "id":"curltest", "method": "gettransaction", "params": ["' . $txid . '"] }';
		$result = rpc_base($wallet,$command);
		return $result;
	}

	function rpc_getblockhash($wallet,$index)
	{
		$command = '{"jsonrpc": "1.0", "id":"curltest", "method": "getblockhash", "params": [ ' . $index . ' ] }';
		$result = rpc_base($wallet,$command);
		return $result;
	}

?>
