<?php
	require_once('../include/database.inc.php');

	$dblink = dbinit();
	$i=0;

	$query = "SELECT * FROM tblxrates WHERE visible=1 ORDER BY displayorder";
	$result = $dblink->query($query) or dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		$i++;
		$rates[$i] = new ExchangeRate();
		$rates[$i]->name = $row[0] . "/" . $row[1];
		$rates[$i]->src = $row[0];
		$rates[$i]->dst = $row[1];
		$symbol = $row[8];
		if($row[1]=='NZD' || $row[1]=='USD' || $row[1]=='GBP' || $row[1]=='EUR')
		{
			if($row[0] =='MYR')
			{
				$rates[$i]->quantity = 1000;
			}
			else
			{
				$rates[$i]->quantity = 1;
			}
			if($row[2]<1)
			{
				$rates[$i]->value = $symbol . sprintf("%00.04f",round($rates[$i]->quantity * $row[2],4));
			}
			else
			{
				$rates[$i]->value = $symbol . sprintf("%00.02f",round($rates[$i]->quantity * $row[2],2));
			}

		}
		else
		{
			$rates[$i]->value = sprintf("%00.08f",round($row[2],8));
			$rates[$i]->quantity = 1;
		}
		$rates[$i]->valueraw = $row[2];
		$rates[$i]->source = $row[3];
		$rates[$i]->url = $row[4];
		$rates[$i]->lastupdate = $row[5];

		$oldtime = date('Y-m-d H:i:s',time()-(24*3600));
		$query2 = "SELECT value FROM tblxratehistory WHERE src='$row[0]' AND dst='$row[1]' AND moment<='$oldtime' ORDER BY moment DESC";
		$result2 = $dblink->query($query2) or dberror($dblink,$query2);
		if($result2->num_rows>0)
		{
			$row2 = $result2->fetch_row();
			$oldvalue = $row2[0];
			$rates[$i]->change = sprintf("%00.02f%%",round(($row[2]-$oldvalue)/$oldvalue*100,2));
			$rates[$i]->changeraw = ($row[2]-$oldvalue)/$oldvalue;
		}
		else
		{
			$rates[$i]->change = "0.00%";
			$rates[$i]->changeraw = 0;
		}
		$result2->close();
	}
	$result->close();
	$dblink->close();

	header('Content-type: application/json');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

	echo json_encode($rates);



	class ExchangeRate
	{
		public $name;
		public $src;
		public $dst;
		public $value;
		public $quantity;
		public $valueraw;
		public $source;
		public $url;
		public $lastupdate;
		public $change;
		public $changeraw;
	}

?>
