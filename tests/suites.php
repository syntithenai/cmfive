<?php
/****
 * This file contains configuration of test suites in dev and test environments
 * and a mapping of test request url to determine environment.
 */
 
/*
 * Return an array of paths to tests with basePath prepended
 * Add test suites here
 */ 
function getSuitePaths($basePath) {
	return array(
		'tasks'=>$basePath.''.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'task'
	);
} 
// CONFIG TO BE COPIED AND MODIFIED
$windowsConfig=array(
	'basepath' => 'c:\wamp\www\cmfive',
	'paths' =>getSuitePaths('c:\wamp\www\cmfive'),
	'codeception' =>'C:\wamp\www\cmfiveclean\system\composer\vendor\codeception\codeception\codecept',
	'env'=>'devsteve'
);
$linuxConfig=array(
	'basepath'=>'/var/www/projects/cmfive/dev',
	'paths' =>getSuitePaths('/var/www/projects/cmfive/dev'),
	'codeception' =>'/var/www/projects/cmfive/dev/system/composer/vendor/codeception/codeception/codecept',
	'env'=>'test'
);
// hack from base config here	
$steveDevConfig=array_merge(array('phpLogFile'=>'c:\wamp\logs\php_error.log','basepath'=>'c:\wamp\www\cmfive','paths'=>getSuitePaths('c:\wamp\www\cmfive')),$windowsConfig);
$testConfig=array_merge(array('basepath'=>'/var/www/projects/cmfive/dev','paths'=>getSuitePaths('/var/www/projects/cmfive/dev')),$linuxConfig);

// MAPPING OF URLS TO TEST CONFIGS
$suites=array(
	// dev steve
	'http://cmfive.steve'=>$steveDevConfig,
	// test site
	'http://cmfive.dev.code.2pisoftware.com/' =>$testConfig,
);