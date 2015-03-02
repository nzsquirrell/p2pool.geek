<?php
	require_once('../include/database.inc.php');
	require_once('../include/functions.inc.php');

	$dblink = dbinit();


	if(isset($_GET['hashrate']))
	{
		$hashrate = $_GET['hashrate'];
	}
	else
	{
		$hashrate = 3000;
	}

	if(isset($_GET['power']))
	{
		$power = $_GET['power'];
	}
	else
	{
		$power = 250;
	}

	if(isset($_GET['price']))
	{
		$price = $_GET['price'];
	}
	else
	{
		$price = 0.1;
	}

	if(isset($_GET['unit']))
	{
		$unit = $_GET['unit'];
	}
	else
	{
		$unit = 1;
	}

	if(isset($_GET['hashunit']))
	{
		$hashunit = $_GET['hashunit'];
	}
	else
	{
		$hashunit = 1;
	}

	if(isset($_GET['algorithm']))
	{
		$algorithm = $dblink->real_escape_string($_GET['algorithm']);
	}
	else
	{
		$algorithm = 0;
	}

	if(isset($_GET['currency']))
	{
		$currency = $dblink->real_escape_string($_GET['currency']);
	}
	else
	{
		$currency = 'USD';
	}

	if($currency=='USD')
	{
		$curr_name = 'United States Dollar';
		$curr_sym = "$";
	}
	elseif($currency=='NZD')
	{
		$curr_name = 'New Zealand Dollar';
		$curr_sym = "$";
	}
	elseif($currency=='GBP')
	{
		$curr_name = 'Pound Sterling';
		$curr_sym = "&pound;";
	}
	elseif($currency=='EUR')
	{
		$curr_name = 'Euro';
		$curr_sym = "&euro;";
	}

	$output = new stdClass;


	$secperday = 60 * 60 * 24;
	$ghpshare = pow(2,32)/1000000000;
	$ghpsec = ($hashrate * $hashunit) / 1000000;
	$sharesday = $ghpsec * (1 / $ghpshare) * $secperday;


	$query = "SELECT data FROM tblp2poolinfo WHERE node=1 AND stat='local_stats'";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();
	$blockval = json_decode($row[0])->block_value;

	$query = "SELECT AVG(difficulty) FROM tbldifficulty WHERE coin='MYR' AND network='$algorithm' AND moment<=DATE_ADD(NOW(),INTERVAL -1 DAY)";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();
	$diff = $row[0];

	$query = "SELECT value FROM tblxrates WHERE src='MYR' AND dst='BTC'";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();
	$myrbtc = $row[0];

	$query = "SELECT value FROM tblxrates WHERE src='BTC' AND dst='$currency'";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	$row = $result->fetch_row();
	$btcfiat = $row[0];

	$output->Difficulty = $diff;
	$output->ExchangeRates = array();
	$output->ExchangeRates['MYR-BTC'] = $myrbtc;
	$output->ExchangeRates["BTC-$currency"] = $btcfiat;

	$coinday = (1 / $diff) * $blockval * $sharesday;

	$solotime = pow(2,32) * $diff / (1000 * $hashrate * $hashunit);

	$coins = array();

	$i=1;
	$coins[$i] = new stdClass;
	$coins[$i]->Currency = 'MyriadCoin - MYR';
	$coins[$i]->EarningsPerDay = sprintf("%00.08f",round($coinday,8));
	$coins[$i]->EarningsPerWeek = sprintf("%00.08f",round($coinday * 7,8));
	$coins[$i]->EarningsPerMonth = sprintf("%00.08f",round($coinday * 365.25 / 12,8));
	$coins[$i]->EarningsPerYear = sprintf("%00.08f",round($coinday * 365.25,8));
	$coins[$i]->SoloMining = seconds2time($solotime);

	$btcday = $coinday * $myrbtc;







	$i++;
	$coins[$i] = new stdClass;
	$coins[$i]->Currency = 'Total - Bitcoin (BTC)';

	$coins[$i]->EarningsPerDay = sprintf("%00.08f",round($btcday,8));
	$coins[$i]->EarningsPerWeek = sprintf("%00.08f",round($btcday * 7,8));
	$coins[$i]->EarningsPerMonth = sprintf("%00.08f",round($btcday * 365.25 / 12,8));
	$coins[$i]->EarningsPerYear = sprintf("%00.08f",round($btcday * 365.25,8));

	$i++;
	$coins[$i] = new stdClass;
	$coins[$i]->Currency = "Total - $curr_name ($currency)";

	$curday = $btcday * $btcfiat;
	$coins[$i]->EarningsPerDay = $curr_sym . sprintf("%00.02f",round($curday,2));
	$coins[$i]->EarningsPerWeek = $curr_sym . sprintf("%00.02f",round($curday * 7,2));
	$coins[$i]->EarningsPerMonth = $curr_sym . sprintf("%00.02f",round($curday * 365.25 / 12,2));
	$coins[$i]->EarningsPerYear = $curr_sym . sprintf("%00.02f",round($curday * 365.25,2));

	$i++;
	$coins[$i] = new stdClass;
	$coins[$i]->Currency = "Costs - $curr_name ($currency)";

	$costday = 24 * $power / $unit * $price;
	$coins[$i]->EarningsPerDay = $curr_sym . sprintf("%00.02f",round($costday,2));
	$coins[$i]->EarningsPerWeek = $curr_sym . sprintf("%00.02f",round($costday * 7,2));
	$coins[$i]->EarningsPerMonth = $curr_sym . sprintf("%00.02f",round($costday * 365.25 / 12,2));
	$coins[$i]->EarningsPerYear = $curr_sym . sprintf("%00.02f",round($costday * 365.25,2));

	$i++;
	$coins[$i] = new stdClass;
	$coins[$i]->Currency = "Profit - $curr_name ($currency)";

	$profit = $curday - $costday;
	$coins[$i]->EarningsPerDay = $curr_sym . sprintf("%00.02f",round($profit,2));
	$coins[$i]->EarningsPerWeek = $curr_sym . sprintf("%00.02f",round($profit * 7,2));
	$coins[$i]->EarningsPerMonth = $curr_sym . sprintf("%00.02f",round($profit * 365.25 / 12,2));
	$coins[$i]->EarningsPerYear = $curr_sym . sprintf("%00.02f",round($profit * 365.25,2));


	$output->coins = $coins;





	echo json_encode($output);
?>