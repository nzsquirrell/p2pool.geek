google.load('visualization', '1', {'packages':['corechart']});

google.setOnLoadCallback(drawChart);

$(document).ready(function() {
	$(document).trigger('init');
});


var intervalFigures = 60;
//var nodecount = 8;
var gcount = 0;
var interval = 'hour';
var display = 'node10';
var xrcount = 0;

$(document).on('init', function(e, eventInfo) {
	$(document).trigger('update_nodes');
	$(document).trigger('update_blocks');
 	$(document).trigger('update_alerts');

});

setInterval(function() {
	$(document).trigger('update_blocks');
 	$(document).trigger('update_alerts');
 	$(document).trigger('update_nodes');
	xrcount++;
	if((gcount % 10)==0) {
//		drawChart();
	};
}, intervalFigures * 1000);

$(document).on('update_nodes', function(e, eventInfo) {
	$.getJSON('json/getchartoptions.php', function(data) {
		if(data)
		{
			var selectedID = $('#chartdisplay').val();
			//console.log(selectedID);
			var opt;
			$('#chartdisplay').empty();
			$.each(data.Nodes, function(key, node) {

				if(selectedID == ('node' + node.id))
				{
					opt = $('<option/>').attr('value','node' + node.id).attr('selected','').text(node.name + ' ~ ' + node.algo);
				}
				else
				{
					opt = $('<option/>').attr('value','node' + node.id).text(node.name + ' ~ ' + node.algo);
				}
				$('#chartdisplay').append(opt);
			});

			$.each(data.Miners, function(key, miner) {
				//console.log(selectedID);
				if(selectedID == miner)
				{
					opt = $('<option/>').attr('value',miner).attr('selected','').text(miner);
				}
				else
				{
					opt = $('<option/>').attr('value',miner).text(miner);
				}
				$('#chartdisplay').append(opt);
			});
		};
	});
});



$(document).on('update_blocks', function(e, eventInfo) {
	$.getJSON('json/getblocks.php', function(data) {
		if(data) {
			$('#last_updated').text("Information last updated @ " + data.Updated);

			$('#recent_blocks').empty();
			$.each(data.RecentBlocks, function(key, block) {
				//block_explorer = $('<a/>').attr('href',block.BlockExplorer).attr('target', '_blank').text(block.Height);
				if(data.Coins[block.CoinID].BlockExplorer!='' && block.Hash!='')
				{
					block_explorer = $('<a/>').attr('href',data.Coins[block.CoinID].BlockExplorer + block.Hash).attr('target', '_blank').text(block.Height);
				}
				else
				{
					block_explorer = block.Height;
				}

				tr = $('<tr/>').attr('id', block.Height);
				if(block.Confirmations=='Orphan')
				{
					tr = tr.attr('class','danger');
					tr.append($('<td/>').append(block.Coin)).append($('<td/>').append('&nbsp;'));
				}
				else
				{
					var c = '';
					if(block.Network==4)
					{
						c = 'info';
					}
					else if(block.Network==3)
					{
						c = 'success';
					}
					else if(block.Network==2)
					{
						c = 'primary';
					}
					else if(block.Network==1)
					{
						c = 'warning';
					}
					else if(block.Network==0)
					{
						c = 'danger';
					}

					tr.append($('<td/>').attr('class',c).append($('<strong/>').text(block.Coin)));
					tr.append($('<td/>').attr('class',c).text(block.Algorithm));
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
		};
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


function drawChart() {
// 	$.getJSON('json/chart.php?interval='+interval+'&miner='+display, function(hdata) {
// 		if(hdata) {
// 			$('#hashchart').empty();
// 			var data = new google.visualization.arrayToDataTable(hdata);
// 			var options = {curveType: 'function',legend: { position: 'right' }, hAxis: { title: 'Date & Time'}, vAxis: { title: 'MH/s', minValue: 0, viewWindowMode: 'maximized'}};
// 			var chart = new google.visualization.LineChart(document.getElementById('hashchart'));
// 			chart.draw(data, options);
// 		}
// 	});
	blockChart();
};


function blockChart() {
	network = $("input:radio[name=history_display]:checked" ).val();
	//var blockcoin = 'MYR';
	$.getJSON('json/blocks.php?network='+network, function(hdata) {
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