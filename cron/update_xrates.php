<?php

	function update_xrates($dblink)
	{
		global $config;

		$myrusd = 0;
		$myrnzd = 0;
		$myrgbp = 0;
		$myreur = 0;

		$message = '';

		$data = json_decode(file_get_contents("http://api.coindesk.com/v1/bpi/currentprice.json"));

		if($data!='')
		{
			$query = "SELECT value FROM tblxrates WHERE src='BTC' AND dst='USD'";
			$result = $dblink->query($query) OR dberror($dblink,$query);
			$row = $result->fetch_row();
			$rate = floatval($data->bpi->USD->rate);
			if($rate!=floatval($row[0]))
			{
				$query = "UPDATE tblxrates SET value='$rate',updated=NOW(6),source='CoinDesk' WHERE src='BTC' AND dst='USD'";
				$result = $dblink->query($query) OR dberror($dblink,$query);
				$drkusd = -1;
				log_change($dblink,'BTC','USD',$rate);
				$message .= "Updated BTC/USD: $rate\r\n";
			}

			$query = "SELECT value FROM tblxrates WHERE src='BTC' AND dst='GBP'";
			$result = $dblink->query($query) OR dberror($dblink,$query);
			$row = $result->fetch_row();
			$rate = floatval($data->bpi->GBP->rate);
			if($rate!=floatval($row[0]))
			{
				$query = "UPDATE tblxrates SET value='$rate',updated=NOW(6),source='CoinDesk' WHERE src='BTC' AND dst='GBP'";
				$result = $dblink->query($query) OR dberror($dblink,$query);
				$drkgbp = -1;
				log_change($dblink,'BTC','GBP',$rate);
				$message .= "Updated BTC/GBP: $rate\r\n";
			}

			$query = "SELECT value FROM tblxrates WHERE src='BTC' AND dst='EUR'";
			$result = $dblink->query($query) OR dberror($dblink,$query);
			$row = $result->fetch_row();
			$rate = floatval($data->bpi->EUR->rate);
			if($rate!=floatval($row[0]))
			{
				$query = "UPDATE tblxrates SET value='$rate',updated=NOW(6),source='CoinDesk' WHERE src='BTC' AND dst='EUR'";
				$result = $dblink->query($query) OR dberror($dblink,$query);
				$drkeur = -1;
				log_change($dblink,'BTC','EUR',$rate);
				$message .= "Updated BTC/EUR: $rate\r\n";
			}
		}


		//$myrbtc = json_decode(file_get_contents("https://api.mintpal.com/v1/market/stats/MYR/BTC"));
		//$myrbtc = $myrbtc[0]->last_price;
		$myrbtc = json_decode(file_get_contents("http://pubapi1.cryptsy.com/api.php?method=singlemarketdata&marketid=200"))->return->markets->MYR->lasttradeprice;

		$query = "SELECT value FROM tblxrates WHERE src='MYR' AND dst='BTC'";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();

		if(floatval($myrbtc)!=floatval($row[0]) && $myrbtc!='')
		{
			$query = "UPDATE tblxrates SET value='$myrbtc',updated=NOW(6),source='Cryptsy' WHERE src='MYR' AND dst='BTC'";
			$result = $dblink->query($query) OR dberror($dblink,$query);
			$myrnzd = -1;
			$myrusd = -1;
			$myrgbp = -1;
			$myreur = -1;
			log_change($dblink,'MYR','BTC',$myrbtc);
			$message .= "Updated MYR/BTC: $myrbtc\r\n";
		}




		$t = (time() - 86400) * 1000;
		$msg = json_decode(file_get_contents("https://bitnz.com/api/0/market_history/$t"));
		$c = count($msg);
		$btcnzd = $msg[$c-1][1];

		$query = "SELECT value FROM tblxrates WHERE src='BTC' AND dst='NZD'";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();

		if($btcnzd!=floatval($row[0]) && $btcnzd!='')
		{
			$query = "UPDATE tblxrates SET value='$btcnzd',updated=NOW(6),source='bitNZ' WHERE src='BTC' AND dst='NZD'";
			$result = $dblink->query($query) OR dberror($dblink,$query);
			$drknzd = -1;
			log_change($dblink,'BTC','NZD',$btcnzd);
			$message .= "Updated BTC/NZD: $btcnzd\r\n";
		}


		if($myrusd==-1)
		{
			$message .= derive_rate($dblink,'MYR','BTC','USD');
		}

		if($myrnzd==-1)
		{
			$message .= derive_rate($dblink,'MYR','BTC','NZD');
		}
		if($myrgbp==-1)
		{
			$message .= derive_rate($dblink,'MYR','BTC','GBP');
		}
		if($myreur==-1)
		{
			$message .= derive_rate($dblink,'MYR','BTC','EUR');
		}

		if($message=='')
		{
			$message = 'No Exchange Rate changes.';
		}

//		$message .= bittrex($dblink,'SYS','BTC');
		$message .= bittrex($dblink,'UIS','BTC');
//		$message .= bittrex($dblink,'ULTC','BTC');
//		$message .= bittrex($dblink,'DOGE','BTC');

		return $message;
	}

	function derive_rate($dblink,$src,$int,$dst)
	{
		$rate1 = get_rate($dblink,$src,$int);
		$rate2 = get_rate($dblink,$int,$dst);

		$final = round($rate1 * $rate2,8);

		$query = "UPDATE tblxrates SET value='$final',updated=NOW(6) WHERE src='$src' AND dst='$dst'";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		log_change($dblink,$src,$dst,$final);
		$message = "Updated $src/$dst: $final\r\n";
		return $message;
	}


	function log_change($dblink,$src,$dst,$value)
	{
			$query = "INSERT INTO tblxratehistory SET src='$src',dst='$dst',value='$value',moment=NOW(6)";
			$result = $dblink->query($query) OR dberror($dblink,$query);
	}

	function get_rate($dblink,$src,$dst)
	{
		$query = "SELECT value FROM tblxrates WHERE src='$src' AND dst='$dst'";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		$row = $result->fetch_row();
		return $row[0];
	}



	function bittrex($dblink,$src,$dst)
	{
		$message = '';

		$market = strtoupper("$dst-$src");
		$data = json_decode(file_get_contents("https://bittrex.com/api/v1.1/public/getticker?market=$market"));
		if(isset($data->success))
		{
			if($data->success)
			{
				$rate = floatval($data->result->Last);

				$oldrate = floatval(get_rate($dblink,$src,$dst));

				if($rate != $oldrate)
				{
					$query = "UPDATE tblxrates SET value='$rate',updated=NOW(6),source='Bittrex' WHERE src='$src' AND dst='$dst'";
					$dblink->query($query) OR dberror($dblink,$query);
					log_change($dblink,$src,$dst,$rate);
					$message = "Updated $src/$dst: " . sprintf("%02.08f",$rate) . "\r\n";
				}
			}
		}
		return $message;
	}
?>