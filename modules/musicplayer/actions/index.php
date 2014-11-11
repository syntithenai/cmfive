<?php
/*
 * Main action for music player. 
 * @author Steve Ryan, stever@syntithenai.com 2014
 */
function index_ALL(Web $w) {
	$w->ctx('api',Config::get('system.rest_api_key'));
	// Automatically print CSRF token
	if (class_exists("CSRF")) {
		$w->ctx('csrf_id',CSRF::getTokenID());
		$w->ctx('csrf_val',CSRF::getTokenValue());
	}
	
	
}

?>
