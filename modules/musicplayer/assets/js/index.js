/*
 * @author Steve Ryan, stever@syntithenai.com 2014
 */
 
$(document).ready(function() {
	$('#songs').rad('loadPartial',{module:'musicplayer',partial:'index', class:'MusicPlayerSong'}).done(function() {
		$('#songs').rad({class:'MusicPlayerSong'});
	});
	$('#playlists').rad('loadPartial',{module:'musicplayer',partial:'index', class:'MusicPlayerPlaylist'}).done(function() {
		$('#playlists').rad({class:'MusicPlayerPlaylist',autoSearch:'true'});
	});		
});
