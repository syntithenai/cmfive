<?php
class Channel extends DbObject {
	
	public $name;
	public $is_active;
	public $notify_user_email;
	public $notify_user_id;
	

	public function getForm() {

		return array("Channel" => array(
			array(
				array("Name", "text", "name", $this->name),
				array("Is Active", "checkbox", "is_active", ($this->is_active === null ? 1 : $this->is_active))
			),
			array(
				array("Notify Email", "text", "notify_user_email", $this->notify_user_email),
				// TODO: Need to prefil this with user names
				array("Notify User", "select", "notify_user_id", $this->notify_user_id, $this->w->Auth->getUsers())
			)
		));

	}

	public function getNotifyUser() {
		return $this->w->Auth->getUser($this->notify_user_id);
	}

}