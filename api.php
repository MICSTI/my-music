<?php
	include('resources.php');
	
	$return = "";
	
	if ($_GET) {
		$key = isset($_GET['key']) ? trim($_GET['key']) : "";
		
		switch ($key) {
			case "mobile_db_mod":
				$return = $mc->getMDB()->getConfig('mm_db_modification');
			
			default:
				break;
		}
	}
	
	echo $return;