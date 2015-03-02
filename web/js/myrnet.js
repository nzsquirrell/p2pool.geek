$(document).ready(function() {
	$(document).trigger('init');
});

var intervalFigures = 30;
//var nodecount = 8;
var gcount = 0;
var interval = 'hour';
var display = 'node10';
var xrcount = 0;

$(document).on('init', function(e, eventInfo) {
	$(document).trigger('update_blocks');
	$(document).trigger('update_status');
});

setInterval(function() {
	$(document).trigger('update_blocks');
	$(document).trigger('update_status');
}, intervalFigures * 1000);


$(document).on('update_status', function(e, eventInfo) {
	$.getJSON('/json/status.php', function(data) {
		if(data) {
			$('#peers').empty();
			$.each(data.Peers, function(key, peer) {
				if(peer.direction=='Inbound')
				{
					dir = '<span class="glyphicon glyphicon-arrow-left"></span> Inbound';
				}
				else
				{
					dir = '<span class="glyphicon glyphicon-arrow-right"></span> Outbound';
				}
				if(peer.versionid<data.VersionID)
				{
					ver = '<span class="label label-danger">' + peer.version + '</span>';
				}
				else
				{
					ver = '<span class="label label-success">' + peer.version + '</span>';
				}
				tr = $('<tr/>').append($('<td/>').text(peer.address)).append($('<td/>').append(dir)).append($('<td/>').append(ver));
				$('#peers').append(tr);
			});

			$('#lastupdate').text(data.Updated);
			$('#sha256_hashrate').text(data.SHA_hash);
			$('#sha256_difficulty').text(data.SHA_diff);
			$('#scrypt_hashrate').text(data.SCR_hash);
			$('#scrypt_difficulty').text(data.SCR_diff);
			$('#groestl_hashrate').text(data.MGR_hash);
			$('#groestl_difficulty').text(data.MGR_diff);
			$('#skein_hashrate').text(data.SKN_hash);
			$('#skein_difficulty').text(data.SKN_diff);
			$('#qubit_hashrate').text(data.QUB_hash);
			$('#qubit_difficulty').text(data.QUB_diff);

			$('#net_block').text(data.Blocks);
			$('#net_value').text(data.BlockValue);
			$('#net_blockstillhalf').text(data.BlocksTillHalf);
			$('#net_halfdate').text(data.HalvingDate);
			$('#wallet_version').text(data.Version);
			$('#wallet_peercount').text(data.PeerCount);
		}
	});
});

$(document).on('update_blocks', function(e, eventInfo) {
	$.getJSON('/json/myrblocks.php', function(data) {
		if(data) {
			//$('#last_updated').text("Information last updated @ " + data.Updated);

			$('#recent_blocks').empty();
			$.each(data.Blocks, function(key, block) {
				block_explorer = $('<a/>').attr('href','http://birdspool.no-ip.org/block/' + block.Hash).attr('target', '_blank').text(block.Height);

				tr = $('<tr/>').attr('id', block.Height);
				tr.append($('<td/>').append(block_explorer));

				tr.append($('<td/>').append(block.Time));
				tr.append($('<td/>').append(block.Algorithm));
				tr.append($('<td/>').append(block.Difficulty));
				tr.append($('<td/>').append(block.Elapsed + 's'));
				if((block.AlgoElapsedProp).substring(0,1)=='-')
				{
					tr.append($('<td/>').append(block.AlgoElapsed + 's <span class="label label-danger">' + block.AlgoElapsedProp + '%</span>'));
				}
				else
				{
					tr.append($('<td/>').append(block.AlgoElapsed + 's <span class="label label-success">' + block.AlgoElapsedProp + '%</span>'));
				}
				tr.append($('<td/>').append(block.Hash));
				$('#recent_blocks').append(tr);
			});

			$('#block_stats').empty();
			$.each(data.Stats, function(key, period) {
				tr = $('<tr/>').append($('<td/>').text(period.Period)).append($('<td/>').text(period.TotalBlocks));
				$.each(period.Algo, function(key, algo) {
					tr.append($('<td/>').text(algo.Count));
					tr.append($('<td/>').text(algo.Difficulty));
					tr.append($('<td/>').text(algo.Hashrate));
				});
				$('#block_stats').append(tr);
			});
		};
	});
});

