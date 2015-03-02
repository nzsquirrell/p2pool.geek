google.load('visualization', '1', {'packages':['corechart']});

google.setOnLoadCallback(drawChart);


function drawChart() {
	$.getJSON('/json/vtcpools.php', function(hdata) {
		if(hdata) {
			$('#poolchart').empty();
			var data = new google.visualization.arrayToDataTable(hdata);
			var options = {'title': 'Vertcoin Pool Hashrate Distribution'};
			var chart = new google.visualization.PieChart(document.getElementById('poolchart'));
			chart.draw(data, options);
		}
	});
};

