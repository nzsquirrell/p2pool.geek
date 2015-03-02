$(document).ready(function() {
	$(document).trigger('init');
});


var intervalFigures = 60;
var nodecount = 8;
var xrcount = 0;

$(document).on('init', function(e, eventInfo) {
	$(document).trigger('update_display');
	$(document).trigger('update_alerts');
	$(document).trigger('update_xrates');
	//$(document).trigger('update_latency');
});

setInterval(function() {
	$(document).trigger('update_display');
	$(document).trigger('update_alerts');
	xrcount++;
	if((xrcount % 10)==0) {
		$(document).trigger('update_xrates');
		//$(document).trigger('update_latency');
	};
}, intervalFigures * 1000);

$(document).on('update_display', function(e, eventInfo)
{
	$.getJSON('json/gethome.php', function(data) {
		$('#last_updated').text("Information last updated @ " + data.Updated);

		$i=0; // SHA256 p2pool network
		$('#pool4_hashrate').text(data.Pools[$i].HashRate + " (" + data.Pools[$i].StaleProp + " Stale)");
		$('#pool4_nonstale').text(data.Pools[$i].NonStaleHashRate + " (" + data.Pools[$i].NonStaleHashRateProp + " of " + data.Coins[1].Name + " " + data.Pools[$i].Algorithm + "  network)");
		$('#pool4_sharediff').text(data.Pools[$i].ShareDifficulty);
		$('#pool4_timesince').text(data.Pools[$i].TimeSinceLastBlock + " (" + data.Pools[$i].Progress + " of estimate)");
		$('#pool4_timetoblock').text(data.Pools[$i].EstTimePerBlock);
		$('#pool4_blocksperday').text(data.Pools[$i].EstBlocksPerDay);

		$i=1; // Scrypt p2pool network
		$('#pool5_hashrate').text(data.Pools[$i].HashRate + " (" + data.Pools[$i].StaleProp + " Stale)");
		$('#pool5_nonstale').text(data.Pools[$i].NonStaleHashRate + " (" + data.Pools[$i].NonStaleHashRateProp + " of " + data.Coins[1].Name + " " + data.Pools[$i].Algorithm + "  network)");
		$('#pool5_sharediff').text(data.Pools[$i].ShareDifficulty);
		$('#pool5_timesince').text(data.Pools[$i].TimeSinceLastBlock + " (" + data.Pools[$i].Progress + " of estimate)");
		$('#pool5_timetoblock').text(data.Pools[$i].EstTimePerBlock);
		$('#pool5_blocksperday').text(data.Pools[$i].EstBlocksPerDay);

		$i=2; // Groestl p2pool network
		$('#pool1_hashrate').text(data.Pools[$i].HashRate + " (" + data.Pools[$i].StaleProp + " Stale)");
		$('#pool1_nonstale').text(data.Pools[$i].NonStaleHashRate + " (" + data.Pools[$i].NonStaleHashRateProp + " of " + data.Coins[1].Name + " " + data.Pools[$i].Algorithm + "  network)");
		$('#pool1_sharediff').text(data.Pools[$i].ShareDifficulty);
		$('#pool1_timesince').text(data.Pools[$i].TimeSinceLastBlock + " (" + data.Pools[$i].Progress + " of estimate)");
		$('#pool1_timetoblock').text(data.Pools[$i].EstTimePerBlock);
		$('#pool1_blocksperday').text(data.Pools[$i].EstBlocksPerDay);

		$i=3; // Skein p2pool network
		$('#pool2_hashrate').text(data.Pools[$i].HashRate + " (" + data.Pools[$i].StaleProp + " Stale)");
		$('#pool2_nonstale').text(data.Pools[$i].NonStaleHashRate + " (" + data.Pools[$i].NonStaleHashRateProp + " of " + data.Coins[1].Name + " " + data.Pools[$i].Algorithm + "  network)");
		$('#pool2_sharediff').text(data.Pools[$i].ShareDifficulty);
		$('#pool2_timesince').text(data.Pools[$i].TimeSinceLastBlock + " (" + data.Pools[$i].Progress + " of estimate)");
		$('#pool2_timetoblock').text(data.Pools[$i].EstTimePerBlock);
		$('#pool2_blocksperday').text(data.Pools[$i].EstBlocksPerDay);

		$i=4; // Qubit p2pool network
		$('#pool3_hashrate').text(data.Pools[$i].HashRate + " (" + data.Pools[$i].StaleProp + " Stale)");
		$('#pool3_nonstale').text(data.Pools[$i].NonStaleHashRate + " (" + data.Pools[$i].NonStaleHashRateProp + " of " + data.Coins[1].Name + " " + data.Pools[$i].Algorithm + "  network)");
		$('#pool3_sharediff').text(data.Pools[$i].ShareDifficulty);
		$('#pool3_timesince').text(data.Pools[$i].TimeSinceLastBlock + " (" + data.Pools[$i].Progress + " of estimate)");
		$('#pool3_timetoblock').text(data.Pools[$i].EstTimePerBlock);
		$('#pool3_blocksperday').text(data.Pools[$i].EstBlocksPerDay);


		$('#sha256_hashrate').text(data.Algorithms[0].HashRate);
		$('#sha256_difficulty').text(data.Algorithms[0].Difficulty);
		$('#scrypt_hashrate').text(data.Algorithms[1].HashRate);
		$('#scrypt_difficulty').text(data.Algorithms[1].Difficulty);
		$('#groestl_hashrate').text(data.Algorithms[2].HashRate);
		$('#groestl_difficulty').text(data.Algorithms[2].Difficulty);
		$('#skein_hashrate').text(data.Algorithms[3].HashRate);
		$('#skein_difficulty').text(data.Algorithms[3].Difficulty);
		$('#qubit_hashrate').text(data.Algorithms[4].HashRate);
		$('#qubit_difficulty').text(data.Algorithms[4].Difficulty);

		$i=1;
		$('#net_block').text(data.Coins[$i].LastBlock);
		$('#net_value').text(data.Coins[$i].BlockValue + " " + data.Coins[$i].CurrencySymbol);
		$('#net_blockstillhalf').text(data.Coins[$i].BlocksTillHalf);
		$('#net_halfdate').text(data.Coins[$i].HalvingDate);


		$('#mergedcoins').empty();

		$.each(data.Coins, function(i, coin)
		{
			if(coin.Merged)
			{
				var tr = $('<tr/>').append($('<td/>').text(coin.CurrencySymbol));
				tr.append($('<td/>').text(coin.Name));
				tr.append($('<td/>').text(coin.Algorithm));
				tr.append($('<td/>').text(coin.Difficulty));
				tr.append($('<td/>').text(coin.HashRate));
				tr.append($('<td/>').text(coin.TimeToBlock));
				tr.append($('<td/>').text(coin.LastBlock));
				tr.append($('<td/>').text(coin.BlockValue));


				$('#mergedcoins').append(tr);
			}
		});

	});
});

$(document).on('update_xrates', function(e, eventInfo)
{
	$.getJSON('json/rates.php', function(rates) {
		if(rates) {
			$('#xrates').empty();
			$.each(rates, function(key, rate) {
				tr = $('<tr/>');
				tr.append($('<td/>').append(rate.name));
				td = $('<td/>');
				td.append(rate.quantity +' ' + rate.src + ' = ' + rate.value + ' ' + rate.dst);
				tr.append(td);
				td = $('<td/>');
				if(rate.source!=null) {
					a = $('<a/>').attr('href',rate.url).attr('target', '_blank').text(rate.source);
					td.append(a);
				};
				tr.append(td);
				td = $('<td/>');
				if(rate.changeraw<0) {
					td.append(' ').append($('<span/>').attr('class','text-danger').text(rate.change));
				}
				else {
					td.append(' +' + rate.change);
				};
				tr.append(td);
				$('#xrates').append(tr);
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

$(document).on('update_latency', function(e, eventInfo) {
	var startTime = new Date();
	$.getJSON('json/time.php', function(t) {
		if(t>-1) {
			var endTime = new Date();
			var ping = Math.round((endTime.getTime() - startTime.getTime())/1,0);
			//console.log(ping);
			$('#latency_node1').text('(' + ping + ' ms)');
		};
	});
	$.getJSON('http://us-w.vert.geek.nz:9172/fee', function(t) {
		if(t>-1) {
			var endTime = new Date();
			var ping = Math.round((endTime.getTime() - startTime.getTime())/1,0);
			//console.log(ping);
			$('#latency_node2').text('(' + ping + ' ms)');
		};
	});
	$.getJSON('http://uk.vert.geek.nz:9172/fee', function(t) {
		if(t>-1) {
			var endTime = new Date();
			var ping = Math.round((endTime.getTime() - startTime.getTime())/1,0);
			//console.log(ping);
			$('#latency_node3').text('(' + ping + ' ms)');
		};
	});
	$.getJSON('http://eu.vert.geek.nz:9172/fee', function(t) {
		if(t>-1) {
			var endTime = new Date();
			var ping = Math.round((endTime.getTime() - startTime.getTime())/1,0);
			//console.log(ping);
			$('#latency_node4').text('(' + ping + ' ms)');
		};
	});
	$.getJSON('http://us-e.vert.geek.nz:9172/fee', function(t) {
		if(t>-1) {
			var endTime = new Date();
			var ping = Math.round((endTime.getTime() - startTime.getTime())/1,0);
			//console.log(ping);
			$('#latency_node5').text('(' + ping + ' ms)');
		};
	});
	$.getJSON('http://us-c.vert.geek.nz:9172/fee', function(t) {
		if(t>-1) {
			var endTime = new Date();
			var ping = Math.round((endTime.getTime() - startTime.getTime())/1,0);
			//console.log(ping);
			$('#latency_node6').text('(' + ping + ' ms)');
		};
	});
	$.getJSON('http://us-m.vert.geek.nz:9172/fee', function(t) {
		if(t>-1) {
			var endTime = new Date();
			var ping = Math.round((endTime.getTime() - startTime.getTime())/1,0);
			//console.log(ping);
			$('#latency_node7').text('(' + ping + ' ms)');
		};
	});
	$.getJSON('http://103.4.17.20:9172/fee', function(t) {
		if(t>-1) {
			var endTime = new Date();
			var ping = Math.round((endTime.getTime() - startTime.getTime())/1,0);
			//console.log(ping);
			$('#latency_node8').text('(' + ping + ' ms)');
		};
	});

});