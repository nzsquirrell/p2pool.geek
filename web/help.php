<?php
	require_once('include/config.inc.php');
	require_once('include/database.inc.php');
	require_once('include/header.php');
?>
<a id="top"></a>
<div class="container">
<?php
	require_once('include/pageheader.php');
?>


	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Getting started guide to mining with p2pool.geek.nz</h3>
		</div>
		<div class="panel-body">
			<p>Welcome to myriad.p2pool.geek.nz, the home of Myriad merge mining, via the Myriad p2pool networks.</p>
			<p>This guide is intended to help newcomers to get up and running with us as a miner.</p>
			<ul style="padding-left: 20px;">
				<li><a href="#wallets">Wallets</a></li>
				<li><a href="#miners">Miners</a></li>
				<li><a href="#tweaking">GPU Tweaking</a></li>
				<li><a href="#overclocking">Overclocking, Work Utility &amp; Reject Rates</a></li>
				<li><a href="#p2pool">How p2pool mining works &amp; what to expect</a></li>
				<li><a href="#resources">Other useful tools &amp; resources</a></li>
			</ul>
		</div>
	</div>

	<a id="wallets"></a>
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Wallets</h3>
		</div>
		<div class="panel-body">
			<p>The very first step is to download wallets for all of the coins that you wish to mine.
			First up, you will need a <a href="http://myriadplatform.org/myriadcoinwallets/" target="_blank">Myriad wallet</a>.</p>
			<p>Myriad has 5 different algorithms all working towards finding blocks on the same blockchain.  Wallets for the other merged coins currently available to mine are:</p>
			<dl class="dl-horizontal">
				<dt>SHA256</dt><dd>Huntercoin (HUC)</dd>
				<dt>Scrypt</dt><dd>Tacocoin (TCO), Syscoin (SYS), Pesetacoin (PTC), Umbrella-LTC (ULTC), Dogecoin (DOGE)</dd>
				<dt>Skien</dt><dd><em>Merge mining not available yet.</em></dd>
				<dt>Myr-Groestl</dt><dd><em>Merge mining not available yet.</em></dd>
				<dt>Qubit</dt><dd><em>Merge mining not available yet.</em></dd>
			</dl>

			<p>Links to wallet downloads and other information for each merge mined coin can be found <a href="/merge">here</a>.</p>
			<p>Whichever algorithm(s) and coins you choose to mine, MYR will always be the main coin, with the others being merged mined at the same time.
			This means that the work your miner does can count towards finding blocks for all coins on the algorithm of your choice.
			This is the same work your miner would be doing to mine MYR anyway, so the merged coins are really just a bonus!</p>
			<p>Once you've downloaded wallets for the coins you wish to mine, you'll need to open each one of them and allow them to download the blockchain in order to sync with the rest of the network.
			This may take some time. If you already have a miner installed, you can start mining immediately, and then register your merged payout addresses via your miners page
			(click the more information button to the right of your MYR address from the <a href="/miners">Miners Page</a>).</p>
			<p>Please be sure to encrypt your wallets with a strong passphrase and back them up in more than one place.</p>

			<p><a href="#top">Back to top</a></p>
		</div>
	</div>

	<a id="miners"></a>
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Miners</h3>
		</div>
		<div class="panel-body">
			<p>While you're waiting for the blockchain to sync, you can download and install the appropriate miner.
			You can find a guide for setting up a miner for each algorithm <a href="https://bitcointalk.org/index.php?topic=545805.0" target="_blank">here</a>.</p>
			<h4>Which Algorithm?</h4>
			<p>The 5 Myriad algorithms are all suited to different mining hardware. It roughly breaks down as follows:
			<dl class="dl-horizontal">
				<dt>SHA256</dt><dd>Only possible to mine profitably with SHA ASICs.</dd>
				<dt>Scrypt</dt><dd>Only possible to mine profitably with Scrypt ASICs.</dd>
				<dt>Skein</dt><dd>Can be mined with GPUs, best suited to AMD/ATI graphics cards.</dd>
				<dt>Myr-Groestl</dt><dd>Can be mined with GPUs and CPUs, best suited to nVidia graphics cards.  Also works well with older graphics cards.</dd>
				<dt>Qubit</dt><dd>Can be mined with GPUs and CPUs.  Said to be the low heat/power consumption option.</dd>
			</dl>
			You will notice that, depending on your hardware, you may have the option to mine 2 - 3 different algorithms.
			The difficulty on each algorithm is continuously adjusted to ensure that all algorithms have an equal probability of finding the next block.
			This opens up the possibility of being able to switch between algorithms according to relative difficulty, for maximum profitability.
			An auto-switcher for this can be found <a href="https://bitbucket.org/iopq/automatic-algo-switcher-for-myriadcoin" target="_blank">here</a>.</p>

			<p><a href="#top">Back to top</a></p>
		</div>
	</div>

	<a id="tweaking"></a>
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">GPU Tweaking</h3>
		</div>
		<div class="panel-body">
			<p>To get you started, example configurations for your GPU can be found <a href="http://myriad.theblockexplorer.com/help.php" target="_blank">here</a>.
			In addition to these general settings, there are some additional factors that are specific to p2pool mining:</p>
			<dl>
				<dt>GPU Threads</dt><dd>If possible this wants to be set to 2 for best results. Running 2 threads rather than 1 usually yields a lower reject rate.</dd>
				<dt>Intensity</dt><dd>In order to run 2 GPU threads, this may have to be set lower. A good starting point for most GPUs is 13. *</dd>
				<dt>Queue, Scan Time and Expiry</dt><dd>For best results on p2pool, set <code>"query" : "0"</code>, <code>"scan-time" : "1"</code> and <code>"expiry" : "1"</code></dd>
				<dt>No-submit-stale</dt><dd>Please set <code>"no-submit-stale" : true</code>. This helps to prevent stale shares from being submitted unnecessarily, and is good practice to help reduce the load on your node.</dd>
				<dt>Difficulty adjustment</dt><dd>It's a good idea to set a minimum share difficulty for your miner which is tuned to the miner's hashrate.
				To do this, you need to append your Myriad address in the username field with a figure that is 0.00000116 multiplied by the hashrate of the miner.
				So, for example, if your rig is capable of 750 kh/s, 750 * 0.00000116 = 0.00087. So your username would become <code>"YOURMYRIADCOINADDRESS+0.00087"</code>.
				Using this setting may help if you're experiencing hardware errors, and it also reduces traffic to and from the node.</dd>
				<dt>Backup pool</dt><dd>To avoid any downtime, it's always best to set up another node as your backup pool.
				Naturally, we'd suggest you setup the next nearest myriad.p2pool.geek.nz node as your backup,
				but if we only have one node with an acceptable latency for you, we'd suggest you pick another one on the same p2pool.
				That way, if your preferred node ever falls over, you'll keep mining with no loss of payout.</dd>
			</dl>
			<p><em>* Some miners use a slightly more sophisticated version of intensity, called raw intensity.
			For a full explanation of how this compares to regular intensity, please see <a href="https://bitcointalk.org/index.php?topic=466867.0" target="_blank">here</a>.</em></p>

			<p><a href="#top">Back to top</a></p>
			</p>
		</div>
	</div>

	<a id="overclocking"></a>
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Overclocking, Work Utility &amp; Reject Rates</h3>
		</div>
		<div class="panel-body">
		<p>Many of the settings you will find listed online for various GPUs will include settings for core and memory clock that are higher than the factory settings for the card.
		This is known as Overclocking, and it's done to work your GPU slightly harder and achieve a higher hashrate.</p>
		<p>Work Utility (WU) is the amount of useful work that your GPU does.  WU is important because this is what you actually get paid for in terms of mining.</p>
		<p>Your miner will submit pseudo-shares to your node. A certain percentage of these will be DOA.  This is known as your reject rate.
		Obviously the lower this number is, the better. You can gauge how well a node is doing relative to the rest of the network by looking at efficiency on the node page.
		Anything over 100% means that the node is achieving a lower reject rate that the network average.
		A comparison with other miners on your node will also tell you whether your reject rate is low enough.</p>
		<p>The reason that we lump these three together is that overclocking tends to have a negative impact on both WU and reject rate.
		Be aware that if you do overclock, your cards will use more power and they'll run hotter. Overclocking can also cause reliability issues.
		So it's always worth evaluating whether it's really worth Overclocking to squeeze out a few extra kh/s?
		Bottom line: unless you have access to free electricity, it's probably very marginal.
		Always remember to factor in power costs when <a href="/calculator" target="_blank">calculating</a> profitability.</p>

		<p><a href="#top">Back to top</a></p>
		</div>
	</div>

	<a id="p2pool"></a>
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">How p2pool mining works &amp; what to expect</h3>
		</div>
		<div class="panel-body">
		<p>Your miner will be contributing hashing power to the p2pool network as a whole.  Periodically, your miner will find a share.
		This happens in much the same way as the pool periodically finding blocks, in that the average frequency with which you find shares is a function of the share difficulty and your hashrate.
		Once found, a share is valid for the next 12 blocks that get found by the pool.
		So each time a block gets found, your payout will be proportional to the total number of valid share that you currently have.</p>
		<p>Consequently, when you first start mining, payouts start small and generally ramp up over the course of the next 12 - 24 hours as you accumulate shares.</p>
		<p>Why mention this? Well, if you've previously been used to mining on a traditional pool, the small initial payments can be a
		disappointment and have caused many a newcomer to cry foul and claim that p2pool payouts "aren't as good", or "just don't work".
		Some patience is definitely advisable. It's worth remembering that this works both ways, and if your miner stops mining for whatever reason,
		the valid shares that you hold will still count for the purpose of receiving payments for (up to) the next 12 blocks (though the payments will gradually tail off as your shares expire).
		Rest assured, when you get it right, p2pool is the most profitable way to mine.</p>
		<p>Additionally, when you mine with myriad.p2pool.geek.nz, you will also be pooling your efforts with all our other miners to mine merged coins.
		Payouts for these coins will also be proportional to the MYR payout.</p>

		<p><a href="#top">Back to top</a></p>
		</div>
	</div>

	<a id="resources"></a>
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Other useful tools &amp; resources</h3>
		</div>
		<div class="panel-body">
		<p><a href="http://cgminermonitor.com/" target="_blank">CGminer monitor</a> is a really powerful tool for monitoring your miner from anywhere.
		You can even receive "miner is down" notification to help minimise downtime.</p>
		<p>If your miner is linux based, <a href="https://github.com/starlilyth/Linux-PoolManager" target="_blank">Poolmanager</a> is another really useful tool for monitoring your rigs,
		and rebooting them remotely if one of your GPUs "gets sick".</p>

		<p><a href="#top">Back to top</a></p>
		</div>
	</div>

<?php

	require_once('include/footer.php');
?>
