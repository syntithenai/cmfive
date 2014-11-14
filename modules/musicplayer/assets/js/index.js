/*
 * Init page for music player
 * The following initialisation is over the top (and causes a delay on initial load because of multiple sequential requests)
 * It is intended that the normalisation applied to templates will be folded up in a real example
 * perhaps just back to edit form and search results or fully custom 
 * @author Steve Ryan, stever@syntithenai.com 2014
 */

$(document).ready(function() {
	$('[data-id="playlist"]').load('/musicplayer/playlist');
	// ALSO POSSIBLE TO LOAD  ASYNC BY AJAX
	//$('#songs').rad('loadPartial',{module:'rad',partial:'radindex', class:'MusicPlayerSong'}).done(function() {
			$('#songs').rad({class:'MusicPlayerSong'});
	//});	
	//$('#playlists').rad('loadPartial',{module:'rad',partial:'radindex', class:'MusicPlayerPlaylist'}).done(function() {
			$('#playlists').rad({class:'MusicPlayerPlaylist',autoSearch:'true'});
	//});
	
});


/* 
$(document).ready(function() {
	$('[data-id="playlist"]').load('/musicplayer/playlist');
	$('#playlists').rad('loadPartial',{module:'rad',partial:'index', class:'MusicPlayerPlaylist'}).done(function() {
		var promises=[];
		var searchPromise=$.Deferred();
		promises.push(searchPromise);
		$('#playlists [data-id="search"]').rad('loadPartial',{module:'rad',partial:'search', class:'MusicPlayerPlaylist'}).done(function() {
			var iPromises=[];
			iPromises.push($('#playlists [data-id="searchform"]').rad('loadPartial',{module:'rad',partial:'searchform', class:'MusicPlayerPlaylist'}));
			iPromises.push($('#playlists [data-id="searchresults"]').rad('loadPartial',{module:'rad',partial:'searchresults', class:'MusicPlayerSong'}));
			$.when.apply($,iPromises).then(function() {
				searchPromise.resolve();
			});
		});
		promises.push($('#playlists [data-id="editform"]').rad('loadPartial',{module:'rad',partial:'editform', class:'MusicPlayerPlaylist'}));
		$.when.apply($,promises).done(function() {
			$('#playlists').rad({class:'MusicPlayerPlaylist',autoSearch:'true'});
		});
	});
	$('#songs').rad('loadPartial',{module:'rad',partial:'index', class:'MusicPlayerSong'}).done(function() {
		var promises=[];
		var searchPromise=$.Deferred();
		promises.push(searchPromise);
		$('#songs [data-id="search"]').rad('loadPartial',{module:'rad',partial:'search', class:'MusicPlayerSong'}).done(function() {
			var iPromises=[];
			iPromises.push($('#songs [data-id="searchform"]').rad('loadPartial',{module:'rad',partial:'searchform', class:'MusicPlayerSong'}));
			iPromises.push($('#songs [data-id="searchresults"]').rad('loadPartial',{module:'rad',partial:'searchresults', class:'MusicPlayerSong'}));
			$.when.apply($,iPromises).then(function() {
				searchPromise.resolve();
			});
		});
		promises.push($('#songs [data-id="editform"]').rad('loadPartial',{module:'rad',partial:'editform', class:'MusicPlayerSong'}));
		$.when.apply($,promises).done(function() {
			$('#songs').rad({class:'MusicPlayerSong'});
		});
	});
	
});
*/
