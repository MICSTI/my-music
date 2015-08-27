<?php
	include('resources.php');
	
	$html = "";
	
	// Headline
	$html .= "<h3>Update status report</h3>";
	
	$html .= "<div id='update-status-report'>";
	
		if ($_POST) {
			$data = isset($_POST["data"]) ? $_POST["data"] : "";
			
			$json_data = json_decode($data, true);
			
			$suggestions = $json_data["suggestions"];
			$added = $json_data["added"];
			$updated = $json_data["updated"];
			
			// MM link suggestions
			if (!empty($suggestions)) {
				$html .= "<div class='panel panel-default'>";
					$html .= "<div class='panel-heading bold'>Suggested MediaMonkey links</div>";
					
					$html .= "<div class='panel-body'>";
						foreach ($suggestions as $song) {
							$html .= getSongItem($song, true);
						}
					$html .= "</div>";
				$html .= "</div>";
			}
			
			// Added songs
			$html .= "<div class='panel panel-default'>";
				$html .= "<div class='panel-heading bold'>New songs</div>";
				
				$html .= "<div class='panel-body'>";
					if (!empty($added)) {
						foreach ($added as $song) {
							$html .= getSongItem($song);
						}
					} else {
						$html .= "No songs were added to the library during the update.";
					}
				$html .= "</div>";
			$html .= "</div>";
			
			// Updated songs
			$html .= "<div class='panel panel-default'>";
				$html .= "<div class='panel-heading bold'>Updated songs</div>";
				
				$html .= "<div class='panel-body'>";
					if (!empty($updated)) {
						foreach ($updated as $song) {
							$html .= getSongItem($song);
						}
					} else {
						$html .= "No songs were updated during the update.";
					}
				$html .= "</div>";
			$html .= "</div>";
		} else {
			$html .= "Unfortunately, we have nothing to show you here.";
		}
		
	$html .= "</div>";
	
	echo $mc->getIndexHTML($html);
	
	function getSongItem($song, $suggestion = false) {
		$html = "";
		
		$html .= "<div class='update-status-report-song'>";
			
			// song details
			$html .= "<div class='col-xs-5'>";
				$html .= "<div>" . $song["SongName"] . "</div>";
				$html .= "<div>" . $song["ArtistName"] . "</div>";
				$html .= "<div>" . $song["RecordName"] . "</div>";
			$html .= "</div>";
			
			// song length + rating
			$html .= "<div class='update-status-report-length-rating col-xs-5'>";
				$html .= "<div>" . millisecondsToMinutes($song["SongLength"]) . " min</div>";
				$html .= "<div>" . getStarsRating($song["SongRating"]) . "</div>";
			$html .= "</div>";
			
			// MM link suggestion
			$html .= "<div class='col-xs-2'>";
				if ($suggestion) {
					$html .= "<button type='button' class='btn btn-primary' onclick=''>Add link</button>";
				}
			$html .= "</div>";
			
		$html .= "</div>";
		
		return $html;
	}