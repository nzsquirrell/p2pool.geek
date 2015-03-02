
var hashunit = 1;

function calculate() {
	var hashrate = $('#hashrate').val();
	var power = $('#power').val();
	var powerprice = $('#powerprice').val();
	var unit = $('#unit').val();
	var hashunit = $('#hashunit').val();
	var currency = $('#currency').val();
	var algorithm = $('#algorithm').val();

	$.getJSON('json/calculator.php?hashrate='+hashrate+'&power='+power+'&price='+powerprice+'&unit='+unit+'&hashunit='+hashunit+'&currency='+currency+'&algorithm='+algorithm, function(data) {
		if(data) {
			$('#earnings').empty();

			$.each(data.coins, function(key, currency) {
				tr = $('<tr/>');
				tr.append($('<td/>').text(currency.Currency));
				tr.append($('<td/>').append(currency.EarningsPerDay));
				tr.append($('<td/>').append(currency.EarningsPerWeek));
				tr.append($('<td/>').append(currency.EarningsPerMonth));
				tr.append($('<td/>').append(currency.EarningsPerYear));
				tr.append($('<td/>').text(currency.SoloMining));
				$('#earnings').append(tr);
			});
		};
	});
}

function changeUnit() {
	var unit = $('#unit').val();
	var power = $('#power').val();
	if(unit==1)
	{
		$('#power').val(power/1000);
	}
	else
	{
		$('#power').val(1000*power);
	}
}

function changeHashUnit() {
	var newunit = $('#hashunit').val();
	var hashrate = $('#hashrate').val();
	var hashreal = 1000 * hashrate * hashunit;
	$('#hashrate').val(hashreal / (newunit * 1000));
	hashunit = newunit;
}

function changeAlgo() {
	switch ($('#algorithm').val()) {
		case '0':
			$('#algo').text('SHA256d Hashrate');
			break;
		case '1':
			$('#algo').text('Scrypt Hashrate');
			break;
		case '2':
			$('#algo').text('Myr-Groestl Hashrate');
			break;
		case '3':
			$('#algo').text('Skein Hashrate');
			break;
		case '4':
			$('#algo').text('Qubit Hashrate');
			break;
	}

}