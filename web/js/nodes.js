$(document).ready(function() {
	$(document).trigger('init');
});


var intervalFigures = 60;
var nodecount = 17;

$(document).on('init', function(e, eventInfo) {
	$(document).trigger('update_nodes');
	$(document).trigger('update_alerts');
});

setInterval(function() {
	$(document).trigger('update_nodes');
	$(document).trigger('update_alerts');
}, intervalFigures * 1000);



$(document).on('update_nodes', function(e, eventInfo) {
	$.getJSON('json/getnodes.php', function(data) {
		if(data) {

			$('#last_updated').text("Information last updated @ " + data.Updated);

			//for($i=1; $i <= nodecount; $i++)
			$.each(data.Nodes, function(i, node)
			{
				i = node.NodeID;
				if(node.Status=='Online')
				{
					$('#panel-node-' + i).attr('class','panel panel-primary');
				}
				else if(node.Status=='Unknown')
				{
					$('#panel-node-' + i).attr('class','panel panel-warning');
				}
				else if(node.Status=='Offline')
				{
					$('#panel-node-' + i).attr('class','panel panel-danger');
				}
				$('#node' + i + '_name').text(node.Name + " ~ " + node.Algorithm);
				$('#node' + i + '_algo').text(node.Algorithm);
				$('#node' + i + '_address').empty();
				$('#node' + i + '_address').append($('<a/>').attr('href',node.URL).attr('target','_blank').text(node.URL));
				$('#node' + i + '_status').text(node.Status);
				$('#node' + i + '_time').text(node.LocalTime);
				$('#node' + i + '_uptime').text(node.Uptime);
				$('#node' + i + '_peers').text(node.PeersTotal + " (" + node.PeersOutbound + " out, " + node.PeersInbound + " in)");
				if(node.Fee=='0.00%')
				{
					$('#node' + i + '_fee').attr('class','label label-success').text(node.Fee);
				}
				else
				{
					$('#node' + i + '_fee').text(node.Fee);
				}
				$('#node' + i + '_version').text(node.Version);
				$('#node' + i + '_efficiency').text(node.Efficiency);
				$('#node' + i + '_miners').text(node.MinerCount);
				$('#node' + i + '_hashrate').text(node.HashRate + " (" + node.StaleRateProp + " Stale)");
				$('#node' + i + '_nonstale').text(node.NonStaleRate + " (" + node.NonStaleRateProp + " of P2Pool)");
				if(node.Merged=='Yes')
				{
					$('#node' + i + '_merged').attr('class','label label-success').text(node.Merged);
				}
				else
				{
					$('#node' + i + '_merged').text(node.Merged);
				}
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


