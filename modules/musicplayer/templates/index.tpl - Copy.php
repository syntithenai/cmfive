
<script src="/modules/musicplayer/assets/js/index.js"></script>
<script src="/modules/musicplayer/assets/js/query-builder.min.js"></script>
<link rel="stylesheet" href="/modules/musicplayer/assets/js/query-builder.min.css" type="text/css"  charset="utf-8">

<style>
#loading {
    position: fixed;
    top: 50%;
    left: 50%;
	 opacity: 0.4;
    -webkit-transform: translate(-50%, -50%);
    transform: translate(-50%, -50%);
}
.buttonset {display:inline;}
.buttonset input {margin-right: 1em;}
.dleft {float:left; clear both;}
[data-delete],label.button.active {background-color:red !important}

</style>




<div id='message' ></div>
<div id='metadata' >
<?php echo $metaFields ?>
<input type='hidden' id="CSRF" data-field='<?php echo $csrf_id; ?>' value="<?php echo $csrf_val; ?>" />
		
</div>


<div id='search' style='display:none;' >
	<div>Search supports * wildcards and space seperated tokens acting as logical AND</div>
	<select id='searchconfig' ><option value='MusicPlayerPlaylist' >Playlist</option><option selected="true" value='MusicPlayerSong' >Song</option><option value='MusicPlayerArtist' >Artist</option><option value='MusicPlayerAlbum' >Album</option><option value='MusicPlayerGenre' >Genre</option></select>
			
	<div id='querybuilder'></div>
	<div id='searchform' class='row-fluid' >
		<form >
			<input type='text' id='searchinput' value='b ags'/>
			<span class='buttonset' ><input type='submit' data-action="search" value='Search' class='button tiny'/><input type='submit' data-action="advancedsearch" value='Advanced Search' class='button tiny'/><input type='submit' class='newbutton button tiny buttonset' value='New'/></span>
		</form>
		
	</div>
	
	

	<div  id='searchresults' style='display:none; border: 1px solid black' >
		<div class='searchresultsrowtemplate' style='display:none; border-bottom: 1px solid black'>
			<span class='buttonset' ><input type='submit' value ='Edit' data-action='edit'  class='button tiny' /><input  class='button tiny' type='submit' value ='Delete' data-action='delete' /></span>
			<span style='display:none' data-field='id'>1</span>
			<span data-field='title'>The title</span>
			<span data-field='data'>some data here</span>
		</div>
	</div>
</div>

<div id='editform' style='display:none'>
	<h3>Edit</h3>
	<form>
		<input type='hidden' data-role='editfield' data-field='id' />
		<input type='text'  data-role='editfield' data-field='title' />
		<input type='text'  data-role='editfield' data-field='data'/>
		<input type='hidden' data-role='hiddenfield' data-field='<?php echo $csrf_id; ?>' value="<?php echo $csrf_val; ?>" />
		<input type='submit' data-action='save' value='Save' class='button tiny'/><input type='submit' data-action='close' value='Cancel' class='button tiny'/>
	</form>
</div>

