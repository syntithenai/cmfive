<?php
/**
 * Song data model
 * 
 * @author Steve Ryan
 */
class MusicPlayerObject extends RadDbObject {
	
	// object properties
	
	// public $id; <-- this is defined in the parent class
	public $title;
	public $data;
	
	// standard system properties
	
	public $is_deleted; // <-- is_ = tinyint 0/1 for false/true
	public $dt_created; // <-- dt_ = datetime values
	public $dt_modified;
	public $modifier_id; // <-- foreign key to user table
	public $creator_id; // <-- foreign key to user table
	

	public function getTitleTemplate() {
		return '$title';
	}
	

	// functions for implementing access restrictions
	// NOTE allow for empty record in test

	public function canList(User $user) {
		if ($this->id >0) { 	
			return $user !== null && $user->hasAnyRole(array("example_admin"));
		} else {
			return false;
		}
	}
	
	public function canView(User $user) {
		if ($this->id >0) { 	
			return $user !== null && $user->hasAnyRole(array("example_admin"));
		} else {
			return false;
		}
	}
	
	public function canEdit(User $user) {
		if ($this->id >0) { 	
			return $user !== null && $user->hasAnyRole(array("example_admin"));
		} else {
			return false;
		}	
	}
	
	public function canDelete(User $user) {
		if ($this->id >0) { 	
			return $user !== null && $user->hasAnyRole(array("example_admin"));
		} else {
			return false;
		}	
	}	
	public function canCreate(User $user) {
		return $user !== null && $user->hasAnyRole(array("example_admin"));; 
	}
	
}
