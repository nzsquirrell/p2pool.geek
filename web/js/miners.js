$(document).ready(function() {
	$(document).trigger('init');
});


var intervalFigures = 60;
var nodecount = 8;

$(document).on('init', function(e, eventInfo) {
	$(document).trigger('update_miners');
	$(document).trigger('update_nodes');
	$(document).trigger('update_alerts');
});

setInterval(function() {
	$(document).trigger('update_miners');
	$(document).trigger('update_nodes');
	$(document).trigger('update_alerts');
}, intervalFigures * 1000);


$(document).on('update_nodes', function(e, eventInfo) {
	$.getJSON('json/getchartoptions.php', function(data) {
		if(data)
		{
			var selectedID = $('#node_display').val();
			var opt;
			$('#node_display').empty();
			$('#node_display').append($('<option/>').attr('value','0').text('All'));
			$.each(data.Nodes, function(key, node) {
				if(selectedID == node.id)
				{
					opt = $('<option/>').attr('value',node.id).attr('selected','').text(node.name + ' ~ ' + node.algo);
				}
				else
				{
					opt = $('<option/>').attr('value',node.id).text(node.name + ' ~ ' + node.algo);
				}
				$('#node_display').append(opt);
			});
		};
	});
});

$(document).on('update_miners', function(e, eventInfo) {
	$.getJSON('json/getminers.php?nodeid=' + $('#node_display').val(), function(data) {
		if(data) {
			$('#last_updated').text("Information last updated @ " + data.Updated);

			$('#active_miners').empty();

			$.each(data.miners, function(key, miner) {

				if($('#node_display').val()==0 || $('#node_display').val()==miner.NodeID)
				{
					if(miner.Donator=='Y')
					{
						var star = ' <span class="glyphicon glyphicon-star-empty"></span>';
					}
					else
					{
						var star = '';
					}
					tr = $('<tr/>').attr('id', miner.Address);
					address_explorer = $('<a/>').attr('href',miner.Payouts['MYR'].Explorer).attr('target', '_blank').text(miner.Payouts['MYR'].Address);
					tr.append($('<td/>').append(address_explorer).append(star));

// 					if($("input:radio[name=payout_display]:checked" ).val()=='drk') {
// 						address_explorer = $('<a/>').attr('href',miner.Payouts['DRK'].Explorer).attr('target', '_blank').text(miner.Payouts['DRK'].Address);
// 						tr.append($('<td/>').append(address_explorer).append(star));
// 						$('#header_address').text('DRK Address');
// 					}
// 					else if($("input:radio[name=payout_display]:checked" ).val()=='udrk') {
// 						address_explorer = $('<a/>').attr('href',miner.Payouts['UDRK'].Explorer).attr('target', '_blank').text(miner.Payouts['UDRK'].Address);
// 						tr.append($('<td/>').text(miner.Payouts['UDRK'].Address));
// 						//tr.append($('<td/>').append(address_explorer).append(star));
// 						$('#header_address').text('UDRK Address');
// 					}
// 					else if($("input:radio[name=payout_display]:checked" ).val()=='plx') {
// 						address_explorer = $('<a/>').attr('href',miner.Payouts['PLX'].Explorer).attr('target', '_blank').text(miner.Payouts['PLX'].Address);
// 						//tr.append($('<td/>').text(miner.Payouts['PLX'].Address));
// 						tr.append($('<td/>').append(address_explorer).append(star));
// 						$('#header_address').text('PLX Address');
// 					}
					tr.append($('<td/>').append(miner.HashRate + " (" + miner.NonStaleHashRateProp + " of pool)"));
					tr.append($('<td/>').append(miner.StaleRateProp));
					tr.append($('<td/>').append(miner.NodeAbbr));
					tr.append($('<td/>').append(miner.Algorithm));

// 					if($("input:radio[name=payout_display]:checked" ).val()=='drk') {
						tr.append($('<td/>').append(miner.Payouts['MYR'].Expected));
						tr.append($('<td/>').append(miner.Payouts['MYR'].PerDay));
// 					}
// 					else if($("input:radio[name=payout_display]:checked" ).val()=='udrk') {
// 						tr.append($('<td/>').append(miner.Payouts['UDRK'].Expected));
// 						tr.append($('<td/>').append(miner.Payouts['UDRK'].PerDay));
// 					}
// 					else if($("input:radio[name=payout_display]:checked" ).val()=='plx') {
// 						tr.append($('<td/>').append(miner.Payouts['PLX'].Expected));
// 						tr.append($('<td/>').append(miner.Payouts['PLX'].PerDay));
// 					}

					button = $('<button/>').attr('class','btn btn-primary btn-xs').attr('onClick','window.location=\'/miner/' + miner.Network + '/'+miner.Payouts['MYR'].Address+'\'').text('More Information');
					tr.append($('<td/>').append(button));
					$('#active_miners').append(tr);
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


