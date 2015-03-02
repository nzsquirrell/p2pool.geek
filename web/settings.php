<?php
	require_once('include/config.inc.php');
	require_once('include/database.inc.php');

	$dblink = dbinit();

	if(isset($_GET['action']))
	{
		$action = $_GET['action'];
	}
	elseif(isset($_POST['action']))
	{
		$action = $_POST['action'];
	}
	else
	{
		$action = '';
	}

	function generate_key()
	{
		return bin2hex(openssl_random_pseudo_bytes(48));
	}

	function get_checkbox($name)
	{
		if($_POST[$name]==1)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	$valid = FALSE;


	if($action=='changesettings')
	{

		$address = $dblink->real_escape_string($_POST['address']);
		$network = $dblink->real_escape_string($_POST['network']);
		$message = $dblink->real_escape_string($_POST['message']);
		$sig = trim($dblink->real_escape_string($_POST['sig']));

		$changes = array();

		$query = "SELECT tblmergedcoins.symbol,tblmergedcoins.name,tblminercoins.coinaddress,tblminercoins.payout_min,tblminercoins.donation FROM tblmergedcoins,tblminercoins WHERE tblmergedcoins.enabled=1 AND tblminercoins.address='$address' AND tblminercoins.coin=tblmergedcoins.symbol AND tblmergedcoins.network='$network' ORDER BY tblmergedcoins.symbol";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{
			$coin = $row[0];
			$changes[$coin] = new stdClass;
			$changes[$coin]->OldAddress = $row[2];
			$changes[$coin]->OldPayout = $row[3];
			$changes[$coin]->OldDonation = $row[4];
			$changes[$coin]->NewAddress = $dblink->real_escape_string($_POST[$coin . "_address"]);
			$changes[$coin]->NewPayout = $dblink->real_escape_string($_POST[$coin . "_minimum"]);
			$changes[$coin]->NewDonation = $dblink->real_escape_string($_POST[$coin . "_donation"]);
		}

		$changest = json_encode($changes);

		$query = "INSERT INTO tblchangerequests SET moment=NOW(),address='$address',network='$network',message='$message',sig='$sig',changes='$changest'";
		$dblink->query($query) OR dberror($dblink,$query);

		$query = "INSERT INTO tblaccounthistory SET moment=NOW(),address='$address',message='Request to change of account settings submitted.'";
		$dblink->query($query) OR dberror($dblink,$query);

		$action = 'complete';
		$valid = TRUE;
	}

	if(isset($_GET['address']))
	{
		$address = $dblink->real_escape_string($_GET['address']);
		$query = "SELECT address_vtc,address_mon,address_plx,mon_minpayment,plx_minpayment,mon_donation,plx_donation FROM tblminers WHERE address_vtc='$address'";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		if($result->num_rows>0)
		{
			$row = $result->fetch_row();
			$valid = TRUE;
		}
	}
	if(isset($_GET['network']))
	{
		$network = $dblink->real_escape_string($_GET['network']);
	}
	else if(!isset($network))
	{
		$valid = FALSE;
	}

	if(!$valid)
	{
		$header = 'Location: http://' . $_SERVER['HTTP_HOST'] . '/miners';
		header($header);
		exit;
	}


	require_once('include/header.php');
?>

<div class="container">

<?php
	require_once('include/pageheader.php');
?>

<?php
	if($action=='')
	{
?>

	<script type="text/javascript">

	function validateForm()
	{
		var sig = $('#sig').val();
		if(sig==null || sig=="")
		{
			$('#alert').text("You have not entered a valid signature. Please try again");
			$('#fgsig').attr('class','form-group has-error has-feedback');
			$('#spsig').attr('class','glyphicon glyphicon-remove form-control-feedback');
			return false;
		}
		else
		{
			return true;
		}
	}

	function sigchange()
	{
		var sig = $('#sig').val();
		if(sig==null || sig=="")
		{
			$('#fgsig').attr('class','form-group has-error has-feedback');
			$('#spsig').attr('class','glyphicon glyphicon-remove form-control-feedback');
		}
		else
		{
			$('#fgsig').attr('class','form-group has-success has-feedback');
			$('#spsig').attr('class','glyphicon glyphicon-ok form-control-feedback');
		}
	}

	</script>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Change Account Settings</h3>
		</div>
		<div class="panel-body">

			<form method="POST" action="/settings.php" onSubmit="return validateForm();">
				<input type="hidden" name="action" value="changesettings">
				<input type="hidden" name="address" value="<?php echo $address; ?>">
				<input type="hidden" name="network" value="<?php echo $network; ?>">
				<div class="row">
					<div class="col-xs-10">
						<p>To confirm your ownership of this account, we need you to sign this message using your Myriadcoin Wallet.</p>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-5">
						<div class="form-group has-feedback">
							<label for="myr" class="control-label">Myriadcoin address to sign message with</label>
							<input class="form-control" id="myr" name="myr" value="<?php echo $address; ?>" readonly style="cursor: text;">
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-10">
						<div class="form-group has-feedback">
							<label for="message" class="control-label">Message to sign - Select and copy this to your clipboard.</label>
							<input class="form-control" id="message" name="message" maxlength="48" value="<?php echo generate_key(); ?>" readonly style="cursor: text;">
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-10">
						<div class="form-group has-feedback">
							<label for="sign1" class="control-label">From within your Myriadcoin Wallet, Choose the 'File' Menu then 'Sign Message...'</label>
							<br><img class="img-rounded" src="/images/sign1.png" width="258" height="228" id="sign1" />
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-10">
						<div class="form-group has-feedback">
							<label for="sign2" class="control-label">Paste the copied message into the large text box - ensure the you do
							not add any extra spaces or characters.<br>Choose your address from the address book. Click on the 'Sign Message' box.</label>
							<br><img class="img-rounded" src="/images/sign2.png" width="724" height="422" id="sign2" />
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-10">
						<div class="form-group has-feedback">
							<label for="sign1" class="control-label">You will need to enter the passphrase to your wallet, as this requires the use of your private key to sign the message.
							<br>Enter your passphrase and click 'OK'</label>
							<br><img class="img-rounded" src="/images/sign3.png" width="748" height="437" id="sign3" />
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-10">
						<div class="form-group has-feedback">
							<label for="sign1" class="control-label">Your wallet will then generate the signature. Copy this using the copy button (at right-hand end of signature).</label>
							<br><img class="img-rounded" src="/images/sign4.png" width="723" height="421" id="sign3" />
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-10">
						<div class="form-group has-feedback" id="fgsig">
							<label for="sig" class="control-label">Paste the signature from your Wallet here.</label>
							<input class="form-control" id="sig" name="sig" maxlength="128" value="" placeholder="Paste Signature Here" onChange="sigchange();">
							<span class="glyphicon form-control-feedback" id="spsig"></span>
						</div>
					</div>
				</div>
				<p>&nbsp;</p>
				<p><strong>IMPORTANT:</strong> You need to sign the above message and paste the signature here. Without this we cannot verify that you are the owner.</p>
				<p>&nbsp;</p>

				<div class="row">
					<div class="col-xs-10">
						<p>For each merged coin please enter your payout address, minimum payout amount, and a donation percentage (if you would like to donate).</p>
						<p><strong>Please note:</strong> If you do not supply a payout address then you will be donating 100% of your earnings for that coin.</p>
					</div>
				</div>
				<p>&nbsp;</p>

<?php

//		$query = "SELECT tblmergedcoins.symbol,tblmergedcoins.name,tblminercoins.coinaddress,tblminercoins.payout_min,tblminercoins.donation FROM tblmergedcoins,tblminercoins WHERE tblmergedcoins.enabled=1 AND tblminercoins.address='$address' AND tblminercoins.coin=tblmergedcoins.symbol AND tblmergedcoins.network='$network' ORDER BY tblmergedcoins.symbol";
		$query = "SELECT tblmergedcoins.symbol,tblmergedcoins.name,tblminercoins.coinaddress,tblminercoins.payout_min,tblminercoins.donation FROM tblmergedcoins,tblminercoins WHERE tblmergedcoins.enabled=1 AND tblminercoins.address='$address' AND tblminercoins.coin=tblmergedcoins.symbol AND tblmergedcoins.network=tblminercoins.network AND tblmergedcoins.network='$network' ORDER BY tblmergedcoins.symbol";
		$result = $dblink->query($query) OR dberror($dblink,$query);
		while($row = $result->fetch_row())
		{

?>

				<div class="row">
					<div class="col-xs-5">
						<div class="form-group">
							<label for="<?php echo $row[0]; ?>_add" class="control-label"><?php echo $row[1];?> Address</label>
							<input class="form-control" id="<?php echo $row[0]; ?>_add" name="<?php echo $row[0]; ?>_address" placeholder="<?php echo $row[1];?> Address - 34 Characters" maxlength="34" value="<?php echo $row[2]; ?>">
						</div>
					</div>
					<div class="col-xs-3">
						<div class="form-group">
							<label for="<?php echo $row[0]; ?>_min" class="control-label"><?php echo $row[1];?> Payout Balance</label>
							<input class="form-control" id="<?php echo $row[0]; ?>_min" name="<?php echo $row[0]; ?>_minimum" placeholder="1.00000000" maxlength="12" value="<?php echo $row[3]; ?>" >
						</div>
					</div>
					<div class="col-xs-3">
						<div class="form-group">
							<label for="<?php echo $row[0]; ?>_don" class="control-label"><?php echo $row[1];?> Donation %</label>
							<input class="form-control" id="<?php echo $row[0]; ?>_don" name="<?php echo $row[0]; ?>_donation" placeholder="0%" maxlength="5" value="<?php echo $row[4]; ?>" >
						</div>
					</div>
				</div>

<?php
		}
?>


				<div class="col-xs-6">
					<input type="submit" class="btn btn-success" value="Save Changes" id="butsubmit">
					<input type="reset" class="btn btn-warning" value="Reset">
					<input type="button" class="btn btn-danger" value="Cancel" onClick="window.location='/miner/<?php echo $network; ?>/<?php echo $address; ?>';">
					<br><span id="alert" class="text-danger"/>
				</div>
			</form>
		</div>
	</div>

<?php

	}
	elseif($action=='complete')
	{
?>
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Change Request Submitted</h3>
		</div>
		<div class="panel-body">
		<p>Thank you. Our systems will now start validating your changes. A message will be posted in your account history when this has been completed.</p>
		<input type="button" class="btn btn-primary" value="Back to Miner" onClick="window.location='/miner/<?php echo $network; ?>/<?php echo $address; ?>';">
		</div>
	</div>



<?php
	}
	require_once('include/footer.php');
?>