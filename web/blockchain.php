<?php
	require_once('include/config.inc.php');
	require_once('include/database.inc.php');
	require_once('include/header.php');
?>

<div class="container">
<?php
	require_once('include/pageheader.php');
?>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">MyriadCoin Blockchain bootstrap.dat</h3>
		</div>
		<div class="panel-body">
			<p>MyriadCoin Blockchain - bootstrap.dat, last updated January 4th, 2015.</p>
			<p>Block height 903700</p>
			<p>Uncompressed Size 1.20 GB</p>
			<p>Download Size 731 MB</p>
			<p>
				Download Links:
				<ul>
					<li><a href="/chain/myriad-bootstrap-20150104.zip.torrent">myriad-bootstrap-20150104.zip.torrent</a></li>
					<li><a href="magnet:?xt=urn:btih:8BC82ADC032850E1C8DE33D44C4B209021A28160&dn=myriad-bootstrap-20150104.zip&tr=udp%3a%2f%2ftracker.publicbt.com%3a80%2fannounce&tr=udp%3a%2f%2ftracker.openbittorrent.com%3a80%2fannounce&tr=udp%3a%2f%2ftracker.ccc.de%3a80%2fannounce">myriad-bootstrap-20150104.zip.torrent magnet</a></li>
					<li><a href="/chain/myriad-bootstrap-20150104.zip">myriad-bootstrap-20150104.zip</a></li>
				</ul>
			</p>
			<p>bootstrap.dat checksums (included in .zip file):
				<ul>
					<li>MD5: a56af48f020b0caebb737c7518b3f9ab bootstrap.dat</li>
					<li>SHA1: dd1a28d80aefe63de5cab0cf5f668156b1fea54a bootstrap.dat</li>
					<li>SHA256: 578a6a93444330c6ceae2a9f6517e4cfa9af9a6384d8ad7a4b31c8acfcb1085d bootstrap.dat</li>
				</ul>
			</p>
		</div>
	</div>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">How To Use</h3>
		</div>
		<div class="panel-body">
			<p>
				The bootstrap.dat file is used to speed up your initial setup of the Myriadcoin wallet. The file contains all of the transaction data
				up to the date it was published, and saves your client from having to contact other nodes to sync this information.
			</p>
			<p>
				After you have installed your wallet, but before you start it the first time, you need to go into the application data folder:
				<ul>
					<li>For Windows this will be 'C:\Users\{username}\AppData\Roaming\Myriadcoin' - create the Myriadcoin folder if it does not already exist.</li>
					<li>For Linux this will be '~/.myriadcoin' - create this if it does not already exist.</li>
				</ul>
				Extract bootstrap.dat from the file you downloaded and place it into the appication data folder. Then start your wallet - it will import the transactions
				from the bootstrap.dat file into your local blockchain database (the wallet will have a status of 'Loading blocks from disk'). Once the wallet has completed this process,
				it will then download any remaining data from other nodes. At this point the bootstrap.dat file will be renamed to 'bootstrap.dat.old' and can be safely removed from your computer
				(you do not need to close the wallet to do this).
			</p>
		</div>
	</div>


<?php

	require_once('include/footer.php');
?>
