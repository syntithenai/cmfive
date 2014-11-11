<script src="/modules/musicplayer/assets/js/query-builder.min.js"></script>
<script src="/modules/musicplayer/assets/js/jquery.rad.js"></script>
<script src="/modules/musicplayer/assets/js/index.js"></script>

<link rel="stylesheet" href="/modules/musicplayer/assets/js/query-builder.min.css" type="text/css"  charset="utf-8">

<style>
.buttonset {display:inline;}
.buttonset input {margin-right: 1em;}
[data-delete],label.button.active {background-color:red !important}
.rules-group-body {float:none !important}
</style>

<div data-id='message' ></div>

<input type='hidden' id="CSRF" data-field='<?php echo $csrf_id; ?>' value="<?php echo $csrf_val; ?>" />


<div class='tabs'>
	<div class='tab-head'>
		<a href='#playlists' >Playlists</a>
		<a href='#songs' >Songs</a>
	</div>
	<div class='tab-body'>
		<div id='playlists'>
			
		</div>
		<div id='songs' >edit songs</div>
	</div>
	
</div>	



