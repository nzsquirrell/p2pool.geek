$(document).ready(function() {
	$(document).trigger('init');
});

var intervalFigures = 60;

$(document).on('init', function(e, eventInfo) {
	$(document).trigger('update_display');
	$(document).trigger('update_alerts');
});

setInterval(function() {
	$(document).trigger('update_display');
	$(document).trigger('update_alerts');
}, intervalFigures * 1000);

$(document).on('update_display', function(e, eventInfo)
{
	$.getJSON('/json/mergedstats.php', function(data) {
		if(data)
		{
			$('#last_updated').text("Information last updated @ " + data.Updated);

			$('#mergedstats').empty();
			$.each(data.MergedCoins, function(id, coin)
			{
				var tr = $('<tr/>').append($('<td/>').text(coin.Symbol)).append($('<td/>').text(coin.Algorithm));
				tr.append($('<td/>').text(coin.PoolHashrate + " (" + coin.PoolHashrateProp + ")"));
				tr.append($('<td/>').append(coin.LastBlock)).append($('<td/>').text(coin.TimeToBlock)).append($('<td/>').text(coin.TimeSince));
				tr.append($('<td/>').text(coin.BlocksPerDay)).append($('<td/>').text(coin.BlocksToday)).append($('<td/>').text(coin.ValueToday));
				$('#mergedstats').append(tr);
			});


		}
	});
});

$(document).on('update_alerts', function(e, eventInfo) {
	$.getJSON('json/getalerts.php', function(data) {
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