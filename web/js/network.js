$(document).ready(function() {
	$(document).trigger('update');
});

var network = 0;


$(document).on('update', function(e, eventInfo) {
	$.getJSON('json/networks.php?network='+network, function(data) {
		if(data)
		{
			$('#googleMap').empty();
			var myCenter = new google.maps.LatLng(10,0);
			var mapProp = {center:myCenter,zoom:2,mapTypeId:google.maps.MapTypeId.ROADMAP};
			var map=new google.maps.Map(document.getElementById("googleMap"),mapProp);

			$.each(data.Nodes, function(key, node) {
				if(node.IsVertGeek)
				{
					var img = '/images/bluepin.png'
				}
				else
				{
					var img = '/images/greenpin.png'
				}
				var marker = new google.maps.Marker({
					position: new google.maps.LatLng(node.Latitude,node.Longitude),
					title: node.Title,
					icon: img,
					map: map
				});
				var info = node.Info + '<a href="'+node.URL+'" target="_blank">'+node.URL+'</a>';
				var infowindow = new google.maps.InfoWindow({
					content: info
				});
				google.maps.event.addListener(marker, 'click', function() {
					infowindow.open(map,marker);
				});
			});
			$('#updated').text("Node information last updated "+data.Updated);
		}
	});
});


function changeNet()
{
	//network = $('#network:checked').val();
	network = $("input:radio[name=network]:checked" ).val();
	console.log('Network = '+network);
	$(document).trigger('update');
}