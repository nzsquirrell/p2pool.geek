
	var valid_mon = false;
	var valid_plx = false;
	var valid_monmin = false;
	var valid_mondon = false;
	var valid_plxmin = false;
	var valid_plxdon = false;

	function validateForm()
	{
		var vtc = $('#vtc').val();
		var mon = $('#mon').val();
		var plx = $('#plx').val();
		var contact = $('#contact').val();

		if(vtc==null || vtc=="" || vtc.substring(0,1)!='V' || vtc.length!=34)
		{
			$('#alert').text("Sorry, you have entered an invalid Vertcoin address. Please try again.");
			$('#fgvtc').attr('class','form-group has-error has-feedback');
			$('#spvtc').attr('class','glyphicon glyphicon-remove form-control-feedback');
			return false;
		}
		else if(mon==null || mon=="" || mon.substring(0,1)!='M' || mon.length!=34)
		{
			$('#alert').text("Sorry, you have entered an invalid Monocle address. Please try again.");
			$('#fgmon').attr('class','form-group has-error has-feedback');
			$('#spmon').attr('class','glyphicon glyphicon-remove form-control-feedback');
			return false;
		}
		else if(plx==null || plx=="" || plx.substring(0,1)!='P' || plx.length!=34)
		{
			$('#alert').text("Sorry, you have entered an invalid ParallaxCoin address. Please try again.");
			$('#fgplx').attr('class','form-group has-error has-feedback');
			$('#spplx').attr('class','glyphicon glyphicon-remove form-control-feedback');
			return false;
		}
		else if(contact==null || !validateEmail(contact))
		{
			$('#alert').text("Please provide a valid email address. Please try again.");
			return false;
		}
		else
		{
			return true;
		}
	}

	function validateEmail(email)
	{
    	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    	return re.test(email);
	}

	function validatePayout(amount)
	{
		var n = /^(\d{1,2})\.?(\d{0,8})$/;
		return n.test(amount);
	}

	function validateDonation(amount)
	{
		var n = /^(\d{1,2})\.?(\d{0,1})$/;
		return n.test(amount);
	}

	function payoutChange(coin)
	{
		var res = false;
		var amount = $('#'+coin).val();
		if(amount=='')
		{
			if(coin.substring(0,3)=='mon')
			{
				$('#'+coin).val('1.00000000');
			}
			else if(coin.substring(0,3)=='plx')
			{
				$('#'+coin).val('10.00000000');
			}
			amount = $('#'+coin).val();
		}
		var fg = '#fg' + coin;
		var sp = '#sp' + coin;
		if(validatePayout(amount) && ( ( coin.substring(0,3)=='mon' && amount>=1 && amount<=10 ) || ( coin.substring(0,3)=='plx' && amount>=10 && amount<=100 ) ))
		{
			$(fg).attr('class','form-group has-success has-feedback');
			$(sp).attr('class','glyphicon glyphicon-ok form-control-feedback');
			res = true;
		}
		else
		{
			$(fg).attr('class','form-group has-error has-feedback');
			$(sp).attr('class','glyphicon glyphicon-remove form-control-feedback');
		}
		return res;
	}

	function donationChange(coin)
	{
		var res = false;
		var amount = $('#'+coin).val();
		if(amount=='')
		{
			$('#'+coin).val('0.0');
			amount = $('#'+coin).val();
		}
		var fg = '#fg' + coin;
		var sp = '#sp' + coin;
		if(validateDonation(amount) && amount>=0 && amount<=100)
		{
			$(fg).attr('class','form-group has-success has-feedback');
			$(sp).attr('class','glyphicon glyphicon-ok form-control-feedback');
			res = true;
		}
		else
		{
			$(fg).attr('class','form-group has-error has-feedback');
			$(sp).attr('class','glyphicon glyphicon-remove form-control-feedback');
		}
		return res;
	}

	function vtcchange(coin)
	{
		var res = false;
		var vtc = $('#'+coin).val();
		var fg = '#fg' + coin;
		var sp = '#sp' + coin;
		if(vtc==null | vtc=="")
		{
			$(fg).attr('class','form-group has-feedback');
			$(sp).attr('class','glyphicon form-control-feedback');
		}
		else
		{
			var l = coin.substring(0,1).toUpperCase();
			if(vtc.substring(0,1)==l && vtc.length==34)
			{
				$(fg).attr('class','form-group has-success has-feedback');
				$(sp).attr('class','glyphicon glyphicon-ok form-control-feedback');
				res = true;
			}
			else
			{
				$(fg).attr('class','form-group has-error has-feedback');
				$(sp).attr('class','glyphicon glyphicon-remove form-control-feedback');
			}
		}
		return res;
	}

	function validateAll()
	{
		valid_mon = vtcchange('mon');
		valid_plx = vtcchange('plx');
		valid_monmin = payoutChange('monmin');
		valid_plxmin = payoutChange('plxmin');
		valid_mondon = donationChange('mondon');
		valid_plxdon = donationChange('plxdon');

		if(valid_mon && valid_plx && valid_monmin && valid_mondon && valid_plxmin && valid_plxdon)
		{
			$('#butsubmit').removeAttr('disabled');
		}
		else
		{
			$('#butsubmit').attr('disabled','disabled');
		}
	}