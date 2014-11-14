<?php
/**
 * Song data model
 * 
 * @author Steve Ryan
 */
class MusicPlayerSong extends MusicPlayerObject {
	
	// object properties
	public $album_id;
	public $artist_id;
	public $genre_id;
	public $file;
	
	public function getAllFields() {
		return array('title','file','artist_id','album_id','genre_id');
	}
	
	public function getPropertyMeta($property) {
		$meta=parent::getPropertyMeta($property);
		if ($property=='artist_id') {
			//echo "artists\n";
			$service=new SearchableService($this->w);
			$meta['options']=$service->search('MusicPlayerArtist');
			//print_r($meta['options']);
		}
		if ($property=='album_id') {
			$service=new SearchableService($this->w);
			$meta['options']=$service->search('MusicPlayerAlbum');
		}
		if ($property=='genre_id') {
			$service=new SearchableService($this->w);
			$meta['options']=$service->search('MusicPlayerGenre');
		}
		return $meta;
	}
	
	// could be solved by convention on field names or carsten mentioned type hints in relation to integer not null bug
	public function getPropertyUIType($property) {
		$meta=array(
		'file' => 'file',
		'artist_id' => 'select',
		'album_id' => 'select',
		'genre_id' => 'select',
		);
		
		if (array_key_exists($property,$meta)) { 
			return $meta[$property];
		} else {
			return parent::getPropertyUIType($property);
		}
	}
	
}
