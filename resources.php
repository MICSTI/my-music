<?php
	// PHP files
	require_once('util.php');
	require_once('music.controller.php');
	require_once('init.db.php');
	require_once('mm.db.php');
	require_once('music.db.php');
	require_once('time.format.php');
	require_once('file.util.php');
	require_once('frontend.php');
	
	// Default time zone
	date_default_timezone_set("Europe/Vienna");
	
	// Execution time limit
	set_time_limit(0);
	
	// Music controller
	$mc = new MusicController();