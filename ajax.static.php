<?php
	include('resources.php');
	
	if ($_GET) {
		$content = isset($_GET["content"]) ? $_GET["content"] : false;
		
		if ($content !== false) {
			switch ($content) {
				case "add_played_song_add":
					echo $mc->getFrontend()->getAddPlayedSongLine();
					break;
			}
		}
	}