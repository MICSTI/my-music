<?php
	include('resources.php');
	
	$data = array();
	
	if ($_GET) {
		$content = isset($_GET["content"]) ? $_GET["content"] : false;
		
		if ($content !== false) {
			switch ($content) {
				case "add_played_song_add":
					$random_id = generateRandomString();
					
					$data["id"] = $random_id;
					$data["content"] = $mc->getFrontend()->getAddPlayedSongLine($random_id);
					
					break;
			}
		}
	}
	
	echo json_encode($data);