<?php
	include('resources.php');
	
	$html = "";
	
	if ($_GET) {
		$rid = isset($_GET['id']) ? $_GET['id'] : false;
		
		if ($rid !== false) {		
			// data
			$record_info = $mc->getMDB()->getRecord($rid);
			
			$song_list = $record_info["SongList"];
			
			// headline
			$html .= "<h3>Record details</h3>";
			
			// general information
			$html .= "<div class='panel panel-default'>";
				$html .= "<div class='panel-heading bold'>General information</div>";
				
				$html .= "<div class='panel-body'>";
					$html .= "<div class='song-general-info col-sm-4'>";
						$html .= "<div class='col-sm-3 bold'>Title:</div>";
						$html .= "<div class='col-sm-9'>" . $record_info["RecordName"] . "</div>";
						
						$html .= "<div class='col-sm-3 bold'>Artist:</div>";
						$html .= "<div class='col-sm-9'><a href='artist.php?id=" . $record_info["ArtistId"] . "'>" . $record_info["ArtistName"] . "</a></div>";
					$html .= "</div>";
					
					$html .= "<div class='song-general-info col-sm-8'>";
						$html .= "<div class='col-sm-3 bold'>Duration:</div>";
						$html .= "<div class='col-sm-9'>" . millisecondsToMinutes($record_info["SongLengthCount"]) . " min</div>";
					
						$html .= "<div class='col-sm-3 bold'>Played songs:</div>";
						$html .= "<div class='col-sm-9'>" . $record_info["SongPlayedCount"] . "</div>";
					$html .= "</div>";
				$html .= "</div>";
			$html .= "</div>";
			
			// song list
			$html .= "<div class='panel panel-default'>";
				$html .= "<div class='panel-heading bold'>Songs</div>";
				
				$html .= "<div class='panel-body'>";
					if (!empty($song_list)) {
						$html .= "<table class='table table-striped'>";
							$html .= "<thead>";
								$html .= "<tr>";
									$html .= "<th class='col-sm-1'>Track no.</th>";
									$html .= "<th class='col-sm-4'>Title</th>";
									$html .= "<th class='col-sm-1'>Duration</th>";
									$html .= "<th class='col-sm-2'>Rating</th>";
									$html .= "<th class='col-sm-2'>Count</th>";
									$html .= "<th class='col-sm-2'>Last listened</th>";
								$html .= "</tr>";
							$html .= "</thead>";
							
							$html .= "<tbody>";
								foreach ($song_list as $song) {
									$sid = $song["SongId"];
									
									$track_no = $song["SongTrackNo"] > 0 ? $song["SongTrackNo"] : "";
									
									$most_recent = $mc->getMDB()->getMostRecentPlayed($sid);
									
									if ($most_recent !== false) {
										$last_played = new MysqlDate($most_recent);
										$last_played = $last_played->convert2AustrianDatetime();
									} else {
										$last_played = "";
									}
									
									
									
									$html .= "<tr>";
										$html .= "<td class='rank'>" . $track_no . "</td>";
										$html .= "<td><a href='song.php?id=" . $song["SongId"] . "'>" . $song["SongName"] . "</a></td>";
										$html .= "<td>" . millisecondsToMinutes($song["SongLength"]) . "</td>";
										$html .= "<td>" . $song["SongRating"] . "</td>";
										$html .= "<td>" . $song["PlayedCount"] . "</td>";
										$html .= "<td>" . $last_played . "</td>";
									$html .= "</tr>";
								}
							$html .= "</tbody>";
						$html .= "</table>";
					} else {
						$html .= "Sadly, this song has never been listened to!";
					}
				$html .= "</div>";
			$html .= "</div>";
		}
	}
	
	echo $mc->getIndexHTML($html);