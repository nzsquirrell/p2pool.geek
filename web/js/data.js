google.load('visualization', '1', {'packages':['corechart']});

google.setOnLoadCallback(drawChart);

$(document).ready(function() {
	$(document).trigger('init');

	tb_init('a.thickbox, area.thickbox, input.thickbox');//pass where to apply thickbox
	imgLoader = new Image();// preload image
	imgLoader.src = tb_pathToImage;

});

function drawChart() {
	$.getJSON('json/chart.php?interval='+interval+'&miner='+display, function(hdata) {
		if(hdata) {
			$('#hashchart').empty();
			var data = new google.visualization.arrayToDataTable(hdata);
			var options = {curveType: 'function',legend: { position: 'right' }, hAxis: { title: 'Date & Time'}, vAxis: { title: 'MH/s', minValue: 0, viewWindowMode: 'maximized'}};
			var chart = new google.visualization.LineChart(document.getElementById('hashchart'));
			chart.draw(data, options);
		}
	});
	blockChart();
};


function blockChart() {
	blockcoin = $("input:radio[name=history_display]:checked" ).val();
	$.getJSON('json/blocks.php?coin='+blockcoin, function(hdata) {
		if(hdata) {
			$('#blockchart').empty();
			var data = new google.visualization.arrayToDataTable(hdata.blocks);
			var options = {curveType: 'function', pointSize: 3,legend: { position: 'right' }, hAxis: { title: 'Block Height'}, vAxis: { title: 'Minutes', minValue: 0, viewWindowMode: 'maximized'}};
			var chart = new google.visualization.LineChart(document.getElementById('blockchart'));
			chart.draw(data, options);

			$('#block_stats').empty();
			$.each(hdata.history, function(key, stat) {
				tr = $('<tr/>');
				tr.append($('<td/>').append(stat.name));
				tr.append($('<td/>').append(stat.expected));
				tr.append($('<td/>').append(stat.count));
				tr.append($('<td/>').append(stat.difficulty));
				tr.append($('<td/>').append(stat.luck));
				$('#block_stats').append(tr);
			});
		};
	});
};

function changeInterval() {
	interval = $('#chartinterval').val();
	drawChart();
};

function changeMiner() {
	display = $('#chartdisplay').val();
	drawChart();
};

var intervalFigures = 60;
var info;
var graphcount = 0;
var interval = 'hour';
var display = 'node1';
var blockcoin = 'VTC';

$(document).on('init', function(e, eventInfo) {
	fetchdata();
	getXrates();
	//getLatency();
});

$(document).on('update', function(e, eventInfo) {
	fetchdata();
 	graphcount++;

 	if((graphcount % 10)==0) {
 		getXrates();
	 	drawChart();
		//getLatency();
	};
});


function getLatency() {
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

}


$(document).on('update_display', function(e, eventInfo) {
	$('#last_updated').text("Information last updated @ " + info.Updated);

	$i=1;
	if(info.Nodes[$i].Status=='Online')
	{
		$('#panel-nz').attr('class','panel panel-info');
	}
	else if(info.Nodes[$i].Status=='Unknown')
	{
		$('#panel-nz').attr('class','panel panel-warning');
	}
	else if(info.Nodes[$i].Status=='Offline')
	{
		$('#panel-nz').attr('class','panel panel-danger');
	}
	$('#local_status').text(info.Nodes[$i].Status);
	$('#local_time').text(info.Nodes[$i].LocalTime);
	$('#local_uptime').text(info.Nodes[$i].Uptime);
	$('#local_peers').text(info.Nodes[$i].PeersTotal + " (" + info.Nodes[$i].PeersOutbound + " out, " + info.Nodes[$i].PeersInbound + " in)");
	$('#local_fee').text(info.Nodes[$i].Fee);
	$('#local_version').text(info.Nodes[$i].Version);
	$('#local_shares').text(info.Nodes[$i].Shares.Valid + " valid, " + info.Nodes[$i].Shares.Orphan + " orphan, " + info.Nodes[$i].Shares.Dead + " dead");
	$('#local_efficiency').text(info.Nodes[$i].Efficiency);
	$('#local_miners').text(info.Nodes[$i].MinerCount);
	$('#local_hashrate').text(info.Nodes[$i].HashRate + " (" + info.Nodes[$i].HashRateProp + " of P2Pool)");

	$i++;
	if(info.Nodes[$i].Status=='Online')
	{
		$('#panel-usam').attr('class','panel panel-info');
	}
	else if(info.Nodes[$i].Status=='Unknown')
	{
		$('#panel-usam').attr('class','panel panel-warning');
	}
	else if(info.Nodes[$i].Status=='Offline')
	{
		$('#panel-usam').attr('class','panel panel-danger');
	}
	$('#usam_status').text(info.Nodes[$i].Status);
	$('#usam_time').text(info.Nodes[$i].LocalTime);
	$('#usam_uptime').text(info.Nodes[$i].Uptime);
	$('#usam_peers').text(info.Nodes[$i].PeersTotal + " (" + info.Nodes[$i].PeersOutbound + " out, " + info.Nodes[$i].PeersInbound + " in)");
	$('#usam_fee').text(info.Nodes[$i].Fee);
	$('#usam_version').text(info.Nodes[$i].Version);
	$('#usam_shares').text(info.Nodes[$i].Shares.Valid + " valid, " + info.Nodes[$i].Shares.Orphan + " orphan, " + info.Nodes[$i].Shares.Dead + " dead");
	$('#usam_efficiency').text(info.Nodes[$i].Efficiency);
	$('#usam_miners').text(info.Nodes[$i].MinerCount);
	$('#usam_hashrate').text(info.Nodes[$i].HashRate + " (" + info.Nodes[$i].HashRateProp + " of P2Pool)");

	$i++;
	if(info.Nodes[$i].Status=='Online')
	{
		$('#panel-uk').attr('class','panel panel-info');
	}
	else if(info.Nodes[$i].Status=='Unknown')
	{
		$('#panel-uk').attr('class','panel panel-warning');
	}
	else if(info.Nodes[$i].Status=='Offline')
	{
		$('#panel-uk').attr('class','panel panel-danger');
	}
	$('#uk_status').text(info.Nodes[$i].Status);
	$('#uk_time').text(info.Nodes[$i].LocalTime);
	$('#uk_uptime').text(info.Nodes[$i].Uptime);
	$('#uk_peers').text(info.Nodes[$i].PeersTotal + " (" + info.Nodes[$i].PeersOutbound + " out, " + info.Nodes[$i].PeersInbound + " in)");
	$('#uk_fee').text(info.Nodes[$i].Fee);
	$('#uk_version').text(info.Nodes[$i].Version);
	$('#uk_shares').text(info.Nodes[$i].Shares.Valid + " valid, " + info.Nodes[$i].Shares.Orphan + " orphan, " + info.Nodes[$i].Shares.Dead + " dead");
	$('#uk_efficiency').text(info.Nodes[$i].Efficiency);
	$('#uk_miners').text(info.Nodes[$i].MinerCount);
	$('#uk_hashrate').text(info.Nodes[$i].HashRate + " (" + info.Nodes[$i].HashRateProp + " of P2Pool)");

	$i++;
	if(info.Nodes[$i].Status=='Online')
	{
		$('#panel-eu').attr('class','panel panel-info');
	}
	else if(info.Nodes[$i].Status=='Unknown')
	{
		$('#panel-eu').attr('class','panel panel-warning');
	}
	else if(info.Nodes[$i].Status=='Offline')
	{
		$('#panel-eu').attr('class','panel panel-danger');
	}
	$('#eu_status').text(info.Nodes[$i].Status);
	$('#eu_time').text(info.Nodes[$i].LocalTime);
	$('#eu_uptime').text(info.Nodes[$i].Uptime);
	$('#eu_peers').text(info.Nodes[$i].PeersTotal + " (" + info.Nodes[$i].PeersOutbound + " out, " + info.Nodes[$i].PeersInbound + " in)");
	$('#eu_fee').text(info.Nodes[$i].Fee);
	$('#eu_version').text(info.Nodes[$i].Version);
	$('#eu_shares').text(info.Nodes[$i].Shares.Valid + " valid, " + info.Nodes[$i].Shares.Orphan + " orphan, " + info.Nodes[$i].Shares.Dead + " dead");
	$('#eu_efficiency').text(info.Nodes[$i].Efficiency);
	$('#eu_miners').text(info.Nodes[$i].MinerCount);
	$('#eu_hashrate').text(info.Nodes[$i].HashRate + " (" + info.Nodes[$i].HashRateProp + " of P2Pool)");

	$i++;
	if(info.Nodes[$i].Status=='Online')
	{
		$('#panel-usae').attr('class','panel panel-info');
	}
	else if(info.Nodes[$i].Status=='Unknown')
	{
		$('#panel-usae').attr('class','panel panel-warning');
	}
	else if(info.Nodes[$i].Status=='Offline')
	{
		$('#panel-usae').attr('class','panel panel-danger');
	}
	$('#usae_status').text(info.Nodes[$i].Status);
	$('#usae_time').text(info.Nodes[$i].LocalTime);
	$('#usae_uptime').text(info.Nodes[$i].Uptime);
	$('#usae_peers').text(info.Nodes[$i].PeersTotal + " (" + info.Nodes[$i].PeersOutbound + " out, " + info.Nodes[$i].PeersInbound + " in)");
	$('#usae_fee').text(info.Nodes[$i].Fee);
	$('#usae_version').text(info.Nodes[$i].Version);
	$('#usae_shares').text(info.Nodes[$i].Shares.Valid + " valid, " + info.Nodes[$i].Shares.Orphan + " orphan, " + info.Nodes[$i].Shares.Dead + " dead");
	$('#usae_efficiency').text(info.Nodes[$i].Efficiency);
	$('#usae_miners').text(info.Nodes[$i].MinerCount);
	$('#usae_hashrate').text(info.Nodes[$i].HashRate + " (" + info.Nodes[$i].HashRateProp + " of P2Pool)");

	$i++;
	if(info.Nodes[$i].Status=='Online')
	{
		$('#panel-usac').attr('class','panel panel-info');
	}
	else if(info.Nodes[$i].Status=='Unknown')
	{
		$('#panel-usac').attr('class','panel panel-warning');
	}
	else if(info.Nodes[$i].Status=='Offline')
	{
		$('#panel-usac').attr('class','panel panel-danger');
	}
	$('#usac_status').text(info.Nodes[$i].Status);
	$('#usac_time').text(info.Nodes[$i].LocalTime);
	$('#usac_uptime').text(info.Nodes[$i].Uptime);
	$('#usac_peers').text(info.Nodes[$i].PeersTotal + " (" + info.Nodes[$i].PeersOutbound + " out, " + info.Nodes[$i].PeersInbound + " in)");
	$('#usac_fee').text(info.Nodes[$i].Fee);
	$('#usac_version').text(info.Nodes[$i].Version);
	$('#usac_shares').text(info.Nodes[$i].Shares.Valid + " valid, " + info.Nodes[$i].Shares.Orphan + " orphan, " + info.Nodes[$i].Shares.Dead + " dead");
	$('#usac_efficiency').text(info.Nodes[$i].Efficiency);
	$('#usac_miners').text(info.Nodes[$i].MinerCount);
	$('#usac_hashrate').text(info.Nodes[$i].HashRate + " (" + info.Nodes[$i].HashRateProp + " of P2Pool)");

	$i++;
	if(info.Nodes[$i].Status=='Online')
	{
		$('#panel-usaw2').attr('class','panel panel-info');
	}
	else if(info.Nodes[$i].Status=='Unknown')
	{
		$('#panel-usaw2').attr('class','panel panel-warning');
	}
	else if(info.Nodes[$i].Status=='Offline')
	{
		$('#panel-usaw2').attr('class','panel panel-danger');
	}
	$('#usaw_status').text(info.Nodes[$i].Status);
	$('#usaw_time').text(info.Nodes[$i].LocalTime);
	$('#usaw_uptime').text(info.Nodes[$i].Uptime);
	$('#usaw_peers').text(info.Nodes[$i].PeersTotal + " (" + info.Nodes[$i].PeersOutbound + " out, " + info.Nodes[$i].PeersInbound + " in)");
	$('#usaw_fee').text(info.Nodes[$i].Fee);
	$('#usaw_version').text(info.Nodes[$i].Version);
	$('#usaw_shares').text(info.Nodes[$i].Shares.Valid + " valid, " + info.Nodes[$i].Shares.Orphan + " orphan, " + info.Nodes[$i].Shares.Dead + " dead");
	$('#usaw_efficiency').text(info.Nodes[$i].Efficiency);
	$('#usaw_miners').text(info.Nodes[$i].MinerCount);
	$('#usaw_hashrate').text(info.Nodes[$i].HashRate + " (" + info.Nodes[$i].HashRateProp + " of P2Pool)");

	$i++;
	if(info.Nodes[$i].Status=='Online')
	{
		$('#panel-au').attr('class','panel panel-info');
	}
	else if(info.Nodes[$i].Status=='Unknown')
	{
		$('#panel-au').attr('class','panel panel-warning');
	}
	else if(info.Nodes[$i].Status=='Offline')
	{
		$('#panel-au').attr('class','panel panel-danger');
	}
	$('#au_status').text(info.Nodes[$i].Status);
	$('#au_time').text(info.Nodes[$i].LocalTime);
	$('#au_uptime').text(info.Nodes[$i].Uptime);
	$('#au_peers').text(info.Nodes[$i].PeersTotal + " (" + info.Nodes[$i].PeersOutbound + " out, " + info.Nodes[$i].PeersInbound + " in)");
	$('#au_fee').text(info.Nodes[$i].Fee);
	$('#au_version').text(info.Nodes[$i].Version);
	$('#au_shares').text(info.Nodes[$i].Shares.Valid + " valid, " + info.Nodes[$i].Shares.Orphan + " orphan, " + info.Nodes[$i].Shares.Dead + " dead");
	$('#au_efficiency').text(info.Nodes[$i].Efficiency);
	$('#au_miners').text(info.Nodes[$i].MinerCount);
	$('#au_hashrate').text(info.Nodes[$i].HashRate + " (" + info.Nodes[$i].HashRateProp + " of P2Pool)");

	$i=1;
	$('#pool_hashrate').text(info.Pools[$i].HashRate + " (" + info.Pools[$i].StaleProp + " Stale)");
	$('#pool_nonstale').text(info.Pools[$i].NonStaleHashRate + " (" + info.Pools[$i].NonStaleHashRateProp + " of network)");
	$('#pool_sharediff').text(info.Pools[$i].ShareDifficulty);
	$('#pool_timesince').text(info.Pools[$i].TimeSinceLastBlock + " (" + info.Pools[$i].Progress + " of estimate)");
	$('#pool_timetoblock').text(info.Pools[$i].EstTimePerBlock);
	$('#pool_blocksperday').text(info.Pools[$i].EstBlocksPerDay);

	$i=1;
	$('#net_hashrate').text(info.Coins[$i].HashRate);
	$('#net_difficulty').text(info.Coins[$i].Difficulty);
	$('#net_block').text(info.Coins[$i].LastBlock);
	$('#net_value').text(info.Coins[$i].BlockValue + " " + info.Coins[$i].CurrencySymbol);

	$i++;
	$('#mon_hashrate').text(info.Coins[$i].HashRate);
	$('#mon_difficulty').text(info.Coins[$i].Difficulty);
	$('#mon_block').text(info.Coins[$i].LastBlock);
	$('#mon_poolhash').text(info.Pools[$i].NonStaleHashRate + " (" + info.Pools[$i].NonStaleHashRateProp + " of network)");
	$('#mon_timetoblock').text(info.Pools[$i].EstTimePerBlock);
	$('#mon_blocksperday').text(info.Pools[$i].EstBlocksPerDay);
	$('#mon_timesince').text(info.Pools[$i].TimeSinceLastBlock + " (" + info.Pools[$i].Progress + " of estimate)");
	$('#mon_immature').text(info.Pools[$i].Immature);
	$('#mon_mature').text(info.Pools[$i].Mature);

	$i++;
	$('#plx_hashrate').text(info.Coins[$i].HashRate);
	$('#plx_difficulty').text(info.Coins[$i].Difficulty);
	$('#plx_block').text(info.Coins[$i].LastBlock);
	$('#plx_poolhash').text(info.Pools[$i].NonStaleHashRate + " (" + info.Pools[$i].NonStaleHashRateProp + " of network)");
	$('#plx_timetoblock').text(info.Pools[$i].EstTimePerBlock);
	$('#plx_blocksperday').text(info.Pools[$i].EstBlocksPerDay);
 	$('#plx_timesince').text(info.Pools[$i].TimeSinceLastBlock + " (" + info.Pools[$i].Progress + " of estimate)");
	$('#plx_immature').text(info.Pools[$i].Immature);
	$('#plx_mature').text(info.Pools[$i].Mature);

	$('#alerts').empty();
	$.each(info.Alerts, function(key, item) {
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
});

$(document).on('update_miners', function(e, eventInfo) {
	$('#active_miners').empty();
	$('#chartdisplay').empty();
	if(display=='node1') {
		$('#chartdisplay').append($('<option/>').attr('value','node1').attr('selected', 'selected').text('New Zealand Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node7').text('U.S.A. West Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node6').text('U.S.A. Central Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node5').text('U.S.A. East Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node3').text('UK Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node4').text('EU Node'));
	}
	else if(display=='node7') {
		$('#chartdisplay').append($('<option/>').attr('value','node1').text('New Zealand Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node7').attr('selected', 'selected').text('U.S.A. West Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node6').text('U.S.A. Central Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node5').text('U.S.A. East Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node3').text('UK Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node4').text('EU Node'));
	}
	else if(display=='node3') {
		$('#chartdisplay').append($('<option/>').attr('value','node1').text('New Zealand Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node7').text('U.S.A. West Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node6').text('U.S.A. Central Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node5').text('U.S.A. East Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node3').attr('selected', 'selected').text('UK Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node4').text('EU Node'));
	}
	else if(display=='node4') {
		$('#chartdisplay').append($('<option/>').attr('value','node1').text('New Zealand Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node7').text('U.S.A. West Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node6').text('U.S.A. Central Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node5').text('U.S.A. East Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node3').text('UK Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node4').attr('selected', 'selected').text('EU Node'));
	}
	else if(display=='node5') {
		$('#chartdisplay').append($('<option/>').attr('value','node1').text('New Zealand Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node7').text('U.S.A. West Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node6').text('U.S.A. Central Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node5').attr('selected', 'selected').text('U.S.A. East Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node3').text('UK Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node4').text('EU Node'));
	}
	else if(display=='node6') {
		$('#chartdisplay').append($('<option/>').attr('value','node1').text('New Zealand Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node7').text('U.S.A. West Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node6').attr('selected', 'selected').text('U.S.A. Central Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node5').text('U.S.A. East Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node3').text('UK Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node4').text('EU Node'));
	}
	else
	{
		$('#chartdisplay').append($('<option/>').attr('value','node1').text('New Zealand Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node7').text('U.S.A. West Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node6').text('U.S.A. Central Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node5').text('U.S.A. East Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node3').text('UK Node'));
		$('#chartdisplay').append($('<option/>').attr('value','node4').text('EU Node'));
	}

	$.each(info.Miners, function(key, miner) {


		tr = $('<tr/>').attr('id', miner.Address);
		if($("input:radio[name=payout_display]:checked" ).val()=='vtc') {
			address_explorer = $('<a/>').attr('href',miner.Payouts['VTC'].Explorer).attr('target', '_blank').text(miner.Payouts['VTC'].Address);
			tr.append($('<td/>').append(address_explorer));
			$('#header_address').text('VTC Address');
		}
		else if($("input:radio[name=payout_display]:checked" ).val()=='mon') {
			address_explorer = $('<a/>').attr('href',miner.Payouts['MON'].Explorer).attr('target', '_blank').text(miner.Payouts['MON'].Address);
			//tr.append($('<td/>').text(miner.Payouts['MON'].Address));
			tr.append($('<td/>').append(address_explorer));
			$('#header_address').text('MON Address');
		}
		else if($("input:radio[name=payout_display]:checked" ).val()=='plx') {
			address_explorer = $('<a/>').attr('href',miner.Payouts['PLX'].Explorer).attr('target', '_blank').text(miner.Payouts['PLX'].Address);
			//tr.append($('<td/>').text(miner.Payouts['PLX'].Address));
			tr.append($('<td/>').append(address_explorer));
			$('#header_address').text('PLX Address');
		}
		tr.append($('<td/>').append(miner.HashRate + " (" + miner.NonStaleHashRateProp + " of pool)"));
		tr.append($('<td/>').append(miner.StaleRateProp));
		tr.append($('<td/>').append(miner.NodeAbbr));

		if($("input:radio[name=payout_display]:checked" ).val()=='vtc') {
			tr.append($('<td/>').append(miner.Payouts['VTC'].Expected));
			tr.append($('<td/>').append(miner.Payouts['VTC'].PerDay));
		}
		else if($("input:radio[name=payout_display]:checked" ).val()=='mon') {
			tr.append($('<td/>').append(miner.Payouts['MON'].Expected));
			tr.append($('<td/>').append(miner.Payouts['MON'].PerDay));
		}
		else if($("input:radio[name=payout_display]:checked" ).val()=='plx') {
			tr.append($('<td/>').append(miner.Payouts['PLX'].Expected));
			tr.append($('<td/>').append(miner.Payouts['PLX'].PerDay));
		}

		shares = $('<a/>').attr('href',"shares.php?address=" + miner.Payouts['VTC'].Address + "&KeepThis=true&TB_iframe=true&height=500&width=900")
			.attr('class','thickbox')
			.attr('title',"Share information for address "+miner.Payouts['VTC'].Address)
			.text("More info...");
		shares_span = $('<span/>').attr('class',"label label-warning")
			.append(shares);
		tr.append($('<td/>').append(miner.Shares.Valid + " valid. ").append(shares_span));
		$('#active_miners').append(tr);

		option = $('<option/>').attr('value',miner.Payouts['VTC'].Address).text(miner.Payouts['VTC'].Address.substring(0,10)+"...");
		if(display==miner.Payouts['VTC'].Address) {
			option = option.attr('selected', 'selected');
		}
		$('#chartdisplay').append(option);
	});
	tb_init('a.thickbox, area.thickbox, input.thickbox')
});

$(document).on('update_blocks', function(e, eventInfo) {
	$('#recent_blocks').empty();
	$.each(info.RecentBlocks, function(key, block) {
		block_explorer = $('<a/>').attr('href',block.BlockExplorer).attr('target', '_blank').text(block.Height);

		tr = $('<tr/>').attr('id', block.Height);
		if(block.Confirmations=='Orphan')
		{
			tr = tr.attr('class','danger');
			tr.append($('<td/>').append(block.Coin));
		}
		else
		{
			if(block.Coin=='VTC')
			{
				tr.append($('<td/>').attr('class','success').append(block.Coin));
			}
			else if(block.Coin=='MON')
			{
				tr.append($('<td/>').attr('class','info').append(block.Coin));
			}
			else if(block.Coin=='PLX')
			{
				tr.append($('<td/>').attr('class','warning').append(block.Coin));
			}
		}

		tr.append($('<td/>').append(block.TimeSince));
		tr.append($('<td/>').append(block.GenerationTime));
		tr.append($('<td/>').append(block_explorer));
		tr.append($('<td/>').append(block.Confirmations));
		tr.append($('<td/>').append(block.EstimatedTime));
		tr.append($('<td/>').append(block.ActualTime));
		if(block.LuckRaw>1)
		{
			tr.append($('<td/>').attr('class','danger').append(block.Luck));
		}
		else if(block.LuckRaw!=0)
		{
			tr.append($('<td/>').attr('class','info').append(block.Luck));
		}
		else
		{
			tr.append($('<td/>').append(block.Luck));
		}
		$('#recent_blocks').append(tr);
	});
});

function getXrates() {
	$.getJSON('json/rates.php', function(rates) {
		if(rates) {
			$('#xrates').empty();
			$.each(rates, function(key, rate) {
				tr = $('<tr/>');
				tr.append($('<td/>').append(rate.name));
				td = $('<td/>');
				td.append('1 ' + rate.src + ' = ' + rate.value + ' ' + rate.dst);
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
};

var fetchdata= function() {
	$.getJSON('json/p2pool.php', function(data) {
		if(data) info = data;
		$(document).trigger('update_display');
		$(document).trigger('update_miners');
		$(document).trigger('update_blocks');
	});
};


setInterval(function() {
	$(document).trigger('update');
}, intervalFigures * 1000);


$('#collapseA').on('hide.bs.collapse', function () {
	$('#panel-localnode').text('Show');
});

$('#collapseA').on('show.bs.collapse', function () {
	$('#panel-localnode').text('Hide');
});

$('#collapseB').on('hide.bs.collapse', function () {
	$('#panel-p2pool').text('Show');
});

$('#collapseB').on('show.bs.collapse', function () {
	$('#panel-p2pool').text('Hide');
});

$('#collapseC').on('hide.bs.collapse', function () {
	$('#panel-network').text('Show');
});

$('#collapseC').on('show.bs.collapse', function () {
	$('#panel-network').text('Hide');
});

$('#collapseD').on('hide.bs.collapse', function () {
	$('#panel-connection').text('Show');
});

$('#collapseD').on('show.bs.collapse', function () {
	$('#panel-connection').text('Hide');
});

$('#collapseE').on('hide.bs.collapse', function () {
	$('#panel-exchange').text('Show');
});

$('#collapseE').on('show.bs.collapse', function () {
	$('#panel-exchange').text('Hide');
});

$('#collapseF').on('hide.bs.collapse', function () {
	$('#panel-hashrate').text('Show');
});

$('#collapseF').on('show.bs.collapse', function () {
	$('#panel-hashrate').text('Hide');
});

$('#collapseG').on('hide.bs.collapse', function () {
	$('#panel-miners').text('Show');
});

$('#collapseG').on('show.bs.collapse', function () {
	$('#panel-miners').text('Hide');
});

$('#collapseH').on('hide.bs.collapse', function () {
	$('#panel-blocks').text('Show');
});

$('#collapseH').on('show.bs.collapse', function () {
	$('#panel-blocks').text('Hide');
});

$('#collapseI').on('hide.bs.collapse', function () {
	$('#panel-history').text('Show');
});

$('#collapseI').on('show.bs.collapse', function () {
	$('#panel-history').text('Hide');
});

$('#collapseI').on('hide.bs.collapse', function () {
	$('#panel-stats').text('Show');
});

$('#collapseI').on('show.bs.collapse', function () {
	$('#panel-stats').text('Hide');
});

$('#collapseK').on('hide.bs.collapse', function () {
	$('#panel-monocle').text('Show');
});

$('#collapseK').on('show.bs.collapse', function () {
	$('#panel-monocle').text('Hide');
});

$('#collapseL').on('hide.bs.collapse', function () {
	$('#panel-parallax').text('Show');
});

$('#collapseL').on('show.bs.collapse', function () {
	$('#panel-parallax').text('Hide');
});

$('#collapseM').on('hide.bs.collapse', function () {
	$('#panel-usanode').text('Show');
});

$('#collapseM').on('show.bs.collapse', function () {
	$('#panel-usanode').text('Hide');
});

$('#collapseM2').on('hide.bs.collapse', function () {
	$('#panel-usawnode').text('Show');
});

$('#collapseM2').on('show.bs.collapse', function () {
	$('#panel-usawnode').text('Hide');
});

$('#collapseN').on('hide.bs.collapse', function () {
	$('#panel-uknode').text('Show');
});

$('#collapseN').on('show.bs.collapse', function () {
	$('#panel-uknode').text('Hide');
});

$('#collapseP').on('hide.bs.collapse', function () {
	$('#panel-eunode').text('Show');
});

$('#collapseP').on('show.bs.collapse', function () {
	$('#panel-eunode').text('Hide');
});

$('#collapseQ').on('hide.bs.collapse', function () {
	$('#panel-usaenode').text('Show');
});

$('#collapseQ').on('show.bs.collapse', function () {
	$('#panel-usaenode').text('Hide');
});

$('#collapseR').on('hide.bs.collapse', function () {
	$('#panel-usacnode').text('Show');
});

$('#collapseR').on('show.bs.collapse', function () {
	$('#panel-usacnode').text('Hide');
});

$('#collapseS').on('hide.bs.collapse', function () {
	$('#panel-aunode').text('Show');
});

$('#collapseS').on('show.bs.collapse', function () {
	$('#panel-aunode').text('Hide');
});