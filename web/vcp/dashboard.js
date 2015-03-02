$(document).ready(function() {
	$(document).trigger('init');
});

var intervalFigures = 15;

$(document).on('init', function(e, eventInfo) {
	$(document).trigger('update_display');
//	$(document).trigger('update_alerts');
//	$(document).trigger('update_xrates');
	//$(document).trigger('update_latency');
});

setInterval(function() {
	$(document).trigger('update_display');
//	$(document).trigger('update_alerts');
//	xrcount++;
// 	if((xrcount % 10)==0) {
// 		$(document).trigger('update_xrates');
		//$(document).trigger('update_latency');
//	};
}, intervalFigures * 1000);

$(document).on('update_display', function(e, eventInfo)
{
	$.getJSON('dashfeed.php', function(data) {
		if(data)
		{
			$('#systemtime').text(data.Updated);

			$('#mergedcoins').empty();
			$.each(data.MergedCoins, function(id, coin)
			{
				var tr = $('<tr/>').append($('<td/>').text(coin.Symbol));
				tr.append($('<td/>').text(coin.Name)).append($('<td/>').text(coin.Algorithm)).append($('<td/>').text(coin.PayoutState));
				tr.append($('<td/>').text(coin.LastBlock)).append($('<td/>').text(coin.LastBlockValue));
				tr.append($('<td/>').text(coin.BlocksToday)).append($('<td/>').text(coin.ValueToday));
				tr.append($('<td/>').text(coin.PaymentsToday)).append($('<td/>').text(coin.DonationsToday)).append($('<td/>').text(coin.PayoutsToday));
				$('#mergedcoins').append(tr);
			});

			$('#payouts').empty();
			$.each(data.Payouts, function(id, payout)
			{
				var tr = $('<tr/>').append($('<td/>').text(payout.Coin));
				tr.append($('<td/>').text(payout.Miner)).append($('<td/>').text(payout.Amount));
				tr.append($('<td/>').text(payout.Moment));
				$('#payouts').append(tr);
			});

			$('#payments').empty();
			$.each(data.Payments, function(id, payout)
			{
				var tr = $('<tr/>').append($('<td/>').text(payout.Coin));
				tr.append($('<td/>').text(payout.Miner)).append($('<td/>').text(payout.Amount));
				tr.append($('<td/>').text(payout.Moment));
				$('#payments').append(tr);
			});

			$('#mergednodes').empty();
			$.each(data.Nodes, function(id, node)
			{
				var tr = $('<tr/>').append($('<td/>').text(node.ID)).append($('<td/>').text(node.Name));
				tr.append($('<td/>').text(node.Algorithm)).append($('<td/>').append(node.Status));
				tr.append($('<td/>').text(node.LastUpdate)).append($('<td/>').append(node.Miners)).append($('<td/>').append(node.NonStaleRate));
				tr.append($('<td/>').append(node.RPC));
				$('#mergednodes').append(tr);
			});

		}
	});
});

