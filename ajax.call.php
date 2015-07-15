<?php
	include('resources.php');
	
	if ($_GET) {
		$called = isset($_GET['called']) ? trim($_GET['called']) : "";
		
		$success = false;
		
		switch ($called) {
			case "importSongs":
				echo $mc->importFromSongFile();
				
				break;
				
			case "createXML":
				$mc->createXML("songs");
				
				break;
		
			default:
				break;
		}
	}