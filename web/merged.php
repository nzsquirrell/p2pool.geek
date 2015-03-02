<?php
	require_once('include/config.inc.php');
	require_once('include/database.inc.php');
	require_once('include/header.php');
	$dblink = dbinit();
?>


<div class="container">

<?php
	require_once('include/pageheader.php');
?>

	<div id="alerts"></div>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">P2Pool Merged Mining Statistics</h3>
		</div>
		<div class="panel-body table-responsive">
			<table class="table table-condensed table-hover small">
				<thead>
					<tr>
						<th>Coin</th>
						<th>Algorithm</th>
						<th>Pool Hashrate (% of network)</th>
						<th>Last Block</th>
						<th>Est. Time to Block</th>
						<th>Time Since Last</th>
						<th>Est. Blocks / Day</th>
						<th>Blocks Today</th>
						<th>Value Today</th>
					</tr>
				</thead>
				<tbody id="mergedstats">
				</tbody>
			</table>
			<div id="launchstats"></div>
		</div>
	</div>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">P2Pool Merged Mining Information</h3>
		</div>
		<div class="panel-body table-responsive">

			<p>We are now merge mining the coins listed below. To ensure you are paid out your share, you must register your payout addresses
			for each of the coins - otherwise your earnings will be dontated to p2pool.geek.nz.</p>
			<p>To register your payout addresses, please go the <a href="/miners">miners tab</a> above, locate your miner, then click on 'More Information'. This will
			take you to a page with information about your estimated earnings + your account settings. Click on 'Change Account Settings'.
			You will then be given a message that you need to sign with your Myriadcoin Wallet - this is to verify that you are the owner of this miner.
			Copy the message provided, sign it in your wallet and copy the resulting signature into the signature box. Then enter your payout addresses
			for the coins you'd like to be paid for.</p>
			<p>Once you have completed the form and submitted it, our systems will confirm the validity of the information supplied and will update your account to reflect this.</p>

			<table class="table table-condensed table-hover small">
				<thead>
					<tr>
						<th>Coin</th>
						<th>Symbol</th>
						<th>Algorithm</th>
						<th>Block Maturity</th>
						<th>Minimum Payout</th>
						<th>Maximum Payout</th>
						<th>Fee</th>
						<th>RPC Port</th>
						<th>P2P Port</th>
						<th>Wallet Download</th>
						<th>Links</th>
					</tr>
				</thead>
				<tbody>
<?php

	$algos[0] = 'SHA256';
	$algos[1] = 'Scrypt';
	$algos[2] = 'Myr-Groestl';
	$algos[3] = 'Skein';
	$algos[4] = 'Qubit';


	$query = "SELECT symbol,name,network,mature,payout_min,payout_max,fee,website,bitcointalk,rpcport,p2pport,download,exchange,exchangeurl,reddit FROM tblmergedcoins WHERE enabled=1 ORDER BY symbol";
	$result = $dblink->query($query) OR dberror($dblink,$query);
	while($row = $result->fetch_row())
	{
		print "<tr>\r\n";

		print "<td>$row[1]</td><td>$row[0]</td>";

		print '<td>' . $algos["$row[2]"] . '</td>';

		print "<td>$row[3]</td><td>$row[4]</td><td>$row[5]</td><td>$row[6]%</td>";

		print "<td>$row[9]</td><td>$row[10]</td>";

		if($row[11]!='')
		{
			print '<td><a href="' . $row[11] . '" target="_blank">Download</td>';
		}
		else
		{
			print '<td>&nbsp;</td>';
		}

		if($row[7]=='' && $row[8]=='' && $row[13]=='')
		{
			print '<td>&nbsp;</td>';
		}
		else
		{
			print '<td>';
			if($row[7]!='')
			{
				print '<a href="' . $row[7] . '" target="_blank">Website</a> ';
			}
			if($row[8]!='')
			{
				print '. <a href="' . $row[8] . '" target="_blank">BitcoinTalk</a> ';
			}
			if($row[13]!='')
			{
				print '. <a href="' . $row[13] . '" target="_blank">Trade @ ' . $row[12] . '</a>';
			}
			if($row[14]!='')
			{
				print '. <a href="' . $row[14] . '" target="_blank">Reddit</a>';
			}

			print '</td>';
		}

		print "</tr>\r\n";
	}

?>
				</tbody>
			</table>

			<ul>
				<li><strong>Block Maturity</strong> - How many confirmations a mined block requires before it is paid out.</li>
				<li><strong>Minimum Payout</strong> - The smallest value of this coin that must reside in your account before payment is made to your wallet.</li>
				<li><strong>Maximum Payout</strong> - The largest value of this coin that we allow you to store in your account before payment is made to your wallet.</li>
				<li><strong>Fee</strong> - A percentage of earnings of merge mined coins that goes towards development and maintenance of p2pool.geek.nz.</li>
			</ul>

			<p>If you have problems syncing any of these coins, please add this to your .conf file:
			<pre>addnode=nz.p2pool.geek.nz</pre>
			By doing this you will be able to sync directly with our wallets that are kept online and available.</p>


			<p><strong>Remember</strong> - Merged Mining has zero effect on your Myriadcoin earnings, all of the shares you submit for Myriadcoin still keep working. Merged Mining is a total
			bonus on top...</p>
		</div>
	</div>

	<script type="text/javascript" src="/js/mergedstats.js"></script>

<?php
	require_once('include/footer.php');
?>
