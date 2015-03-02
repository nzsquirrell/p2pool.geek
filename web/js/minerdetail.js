//google.load('visualization', '1', {'packages':['corechart']});

//google.setOnLoadCallback(drawChart);

$(document).ready(function() {
	$(document).trigger('init');
});


var intervalFigures = 60;
var interval = 'hour';

$(document).on('init', function(e, eventInfo) {
	$(document).trigger('update_display');
	$(document).trigger('update_alerts');
});

setInterval(function() {
	$(document).trigger('update_display');
	$(document).trigger('update_alerts');
}, intervalFigures * 1000);


$(document).on('update_display', function(e, eventInfo) {
	var address = $('#address').text();
	var network = $('#network').text();

	$.getJSON('/json/getminerdetail.php?address=' + address + '&network=' + network, function(data) {
		if(data)
		{
			$('#last_updated').text("Information last updated @ " + data.Updated);
			//$('#network').text(data.Network);
			$('#node').text(data.Node);
			$('#algorithm').text(data.Algorithm);
			$('#hashrate').text(data.HashRate + " (" + data.StaleRateProp + " Stale)");
			$('#nonstalerate').text(data.NonStaleHashRate + " (" + data.NonStaleHashRateProp + " of P2Pool)");

			$('#earnings').empty();
			$('#merged').empty();

			var mc = 0;

			$.each(data.Coins, function(coin,item) {
				var tr = $('<tr/>').append($('<td/>').attr('class','name').text(coin + " per block")).append($('<td/>').text(item.PerBlock)).append($('<td/>').text(item.PerBlockBTC));
				$('#earnings').append(tr);
				var tr = $('<tr/>').append($('<td/>').attr('class','name').text(coin + " per day")).append($('<td/>').text(item.PerDay)).append($('<td/>').text(item.PerDayBTC));
				$('#earnings').append(tr);

				if(item.Merged)
				{
					mc++;
					var tr = $('<tr/>').append($('<td/>').attr('class','name').text(coin + " Address")).append($('<td/>').text(item.PayoutAddress));
					$('#merged').append(tr);
					var tr = $('<tr/>').append($('<td/>').attr('class','name').text(coin + " Minimum Payout")).append($('<td/>').text(item.PayoutMinimum));
					$('#merged').append(tr);
					var tr = $('<tr/>').append($('<td/>').attr('class','name').text(coin + " Balance")).append($('<td/>').text(item.Balance));
					$('#merged').append(tr);
					var tr = $('<tr/>').append($('<td/>').attr('class','name').text(coin + " Donation")).append($('<td/>').text(item.Donation));
					$('#merged').append(tr);

				}
			});

			if(mc==0)
			{
				$('#mergedaccounts').attr('hidden','hidden');
				$('#mergedtx').attr('hidden','hidden');
				$('#mergedhistory').attr('hidden','hidden');
			}
			else
			{
				$('#earnings').append($('<tr/>').append($('<td/>').attr('class','name').append($('<strong/>').text("BTC per day"))).append($('<td/>')).append($('<td/>').append($('<strong/>').text(data.BTCPerDay))));

				$('#transactions').empty();
				$.each(data.TransactionHistory, function (id, tx) {
					var tr = $('<tr/>').append($('<td/>').text(tx.Date)).append($('<td/>').text(tx.Coin));
					tr.append($('<td/>').text(tx.Message)).append($('<td/>').text(tx.Amount)).append($('<td/>').text(tx.Block));
					$('#transactions').append(tr);
				});

				$('#history').empty();
				$.each(data.AccountHistory, function (id, tx) {
					var tr = $('<tr/>').append($('<td/>').text(tx.Date)).append($('<td/>').text(tx.Message));
					$('#history').append(tr);
				});

				$('#earnings2date').empty();
				$.each(data.EarningsHistory, function (coin, stat) {
					var tr = $('<tr/>').append($('<td/>').text(coin));
					tr.append($('<td/>').text(stat.Payments)).append($('<td/>').text(stat.Fees));
					tr.append($('<td/>').text(stat.Donations)).append($('<td/>').text(stat.Payouts));
					$('#earnings2date').append(tr);
				});
			}


		};
	});
});


$(document).on('update_alerts', function(e, eventInfo) {
	$.getJSON('/json/getalerts.php', function(data) {
		if(data) {
			$('#alerts').empty();
			$.each(data, function(key, item) {
				div = $('<div/>');
				if(item.level=='critical') {
					div.attr('class','panel panel-danger');
				}
				else if(item.level=='warning') {
					div.attr('class','panel panel-warning');
				}
				else
				{
					div.attr('class','panel panel-info');
				}
				div.append($('<div/>').attr('class','panel-heading').append($('<h3/>').attr('class','panel-title center').append(item.message)));
				$('#alerts').append(div);
			});
		}
	});

});


// function drawChart() {
// 	var address = $('#drk_address').text();
// 	$.getJSON('/json/chart.php?interval='+interval+'&miner='+address, function(hdata) {
// 		if(hdata) {
// 			$('#hashchart').empty();
// 			var data = new google.visualization.arrayToDataTable(hdata);
// 			var options = {curveType: 'function',legend: { position: 'right' }, hAxis: { title: 'Date & Time'}, vAxis: { title: 'MH/s', minValue: 0, viewWindowMode: 'maximized'}};
// 			var chart = new google.visualization.LineChart(document.getElementById('hashchart'));
// 			chart.draw(data, options);
// 		}
// 	});
// };

// function changeInterval() {
// 	interval = $('#chartinterval').val();
// 	drawChart();
// };
