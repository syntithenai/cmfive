<script src="/modules/musicplayer/assets/js/query-builder.min.js"></script>
<script src="/modules/rad/assets/js/jquery.rad.js"></script>
<script src="/modules/musicplayer/assets/js/index.js"></script>

<link rel="stylesheet" href="/modules/musicplayer/assets/js/query-builder.min.css" type="text/css"  charset="utf-8">

<style>
[data-delete],label.button.active {background-color:red !important}
.rules-group-body {float:none !important}
.playlist {
	float: left;
	width: 300px;
}
.management {
	margin-left: 300px;
}
</style>

<div data-id='playlist' class='playlist' ></div>

<div class='management' >
	<div data-id='message' ></div>

	<input type='hidden' id="CSRF" data-field='<?php echo $csrf_id; ?>' value="<?php echo $csrf_val; ?>" />


	<div class='tabs'>
		<div class='tab-head'>
			<a href='#playlists' >Playlists</a>
			<a href='#songs' >Songs</a>
		</div>
		<div class='tab-body'>
			<div id='playlists'><?php echo $w->partial("radindex",array('classname'=>'MusicPlayerPlaylist'), "rad"); ?></div>
			<div id='songs' ><?php echo $w->partial("radindex",array('classname'=>'MusicPlayerSong'), "rad"); ?></div>
		</div>
		
	</div>	
</div>	


