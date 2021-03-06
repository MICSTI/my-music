<?php
	include('resources.php');
	
	// Action ids
	$SAVE_SONG_MMLINK = "5r8G1TS4";
	
	$html = "";
	
	if ($_GET) {
		$sid = isset($_GET['id']) ? $_GET['id'] : false;
		
		if ($sid !== false) {		
			$song_info = $mc->getMDB()->getSong($sid);
			
			$history = $mc->getMDB()->getPlayedSongHistory($sid);
			
			$play_count = $mc->getMDB()->getSongPlayCount($sid);
			
			$added_date = new MysqlDate($mc->getMDB()->getSongAddedDate($sid));
			
			// get charts info
			$charts_info = $mc->getMDB()->getChartsInfoForInstance("songs", $sid);
			
			if (!empty($history)) {
				$first_played = $history[count($history) - 1]["timestamp"];
				$last_played = $history[0]["timestamp"];
				
				// TODO: Add "on device xxxx"
				$first_played = getDateFromTimestamp($first_played) . " " . getTimeFromTimestamp($first_played);
				$last_played = getDateFromTimestamp($last_played) . " " . getTimeFromTimestamp($last_played);
			} else {
				$first_played = "";
				$last_played = "";
			}
			
			// headline
			$html .= "<h3>Song details</h3>";
		
			// general info about the song
			$html .= "<div class='panel panel-default'>";
				$html .= "<div class='panel-heading bold'>General information</div>";
				
				$html .= "<div class='panel-body'>";
					$html .= "<div class='song-general-info col-sm-4'>";
						$html .= "<div class='col-sm-3 bold'>Title:</div>";
						$html .= "<div class='col-sm-9'>" . $song_info["SongName"] . "</div>";
						
						$html .= "<div class='col-sm-3 bold'>Artist:</div>";
						$html .= "<div class='col-sm-9'>" . getArtistLink($song_info["ArtistId"],$song_info["ArtistName"]) . "</div>";
						
						$html .= "<div class='col-sm-3 bold'>Record:</div>";
						$html .= "<div class='col-sm-9'>" . getRecordLink($song_info["RecordId"], $song_info["RecordName"]) . "</div>";
						
						$html .= "<div class='col-sm-3 bold'>Duration:</div>";
						$html .= "<div class='col-sm-9'>" . millisecondsToMinutes($song_info["SongLength"]) . " min</div>";
						
						if (count($charts_info) > 0) {
							$html .= "<div class='col-sm-3 bold'>Charts:</div>";
							
							$html .= "<div class='col-sm-9'>";
								$html .= $mc->getFrontend()->getChartsInfoDisplay($charts_info);
							$html .= "</div>";
						}
					$html .= "</div>";
					
					$html .= "<div class='song-general-info col-sm-7'>";
						$html .= "<div class='col-sm-3 bold'>Added to library:</div>";
						$html .= "<div class='col-sm-9'>" . $added_date->convert2AustrianDate() . "</div>";
						
						$times = $play_count == 1 ? "time" : "times";
						
						$html .= "<div class='col-sm-3 bold'>Played:</div>";
						$html .= "<div class='col-sm-9'>" . $play_count . " " . $times . "</div>";
					
						$html .= "<div class='col-sm-3 bold'>First time played:</div>";
						$html .= "<div class='col-sm-9'>" . $first_played . "</div>";
						
						$html .= "<div class='col-sm-3 bold'>Last time played:</div>";
						$html .= "<div class='col-sm-9'>" . $last_played . "</div>";
					$html .= "</div>";
					
					// song mm link button (only displayed if there are possible link candidates)
					$candidates = $mc->getMDB()->getPossibleMMLinkCandidates($sid);
					
					if (!empty($candidates)) {
						$html .= "<div class='song-general-info col-sm-1'>";
							$html .= "<button type='button' id='btn-song-mmlink-edit' class='btn btn-default pull-right' onclick=\"crudModal('" . $SAVE_SONG_MMLINK . "', '" . $song_info["SongId"] . "')\"><span class='glyphicon glyphicon-link'></span></button>";
						$html .= "</div>";
					}
				$html .= "</div>";
			$html .= "</div>";

			// song history
			$html .= "<div class='panel panel-default'>";
				$html .= "<div class='panel-heading bold'>Song history</div>";
				
				$html .= "<div class='panel-body'>";
					if (!empty($history)) {
						$html .= "<table class='table table-striped'>";
							$html .= "<thead>";
								$html .= "<tr>";
									$html .= "<th class='col-sm-1'>Date</th>";
									$html .= "<th class='col-sm-1'>Time</th>";
									$html .= "<th class='col-sm-1'>Device</th>";
									$html .= "<th class='col-sm-2'>Activity</th>";
									$html .= "<th class='col-sm-7'> </th>";
								$html .= "</tr>";
							$html .= "</thead>";
							
							$html .= "<tbody>";
								foreach ($history as $played) {
									$device = $mc->getMDB()->getDevice($played["devid"]);
									$device_icon = $mc->getMDB()->getIcon($device["DeviceDeviceTypeIconId"]);
									$device_string = getIconRef($device_icon, "", $device["DeviceName"]);
									
									$activity = $mc->getMDB()->getActivity($played["actid"]);
									$activity_string = getActivitySpan($activity);
									
									$html .= "<tr>";
										$html .= "<td>" . getDateFromTimestamp($played["timestamp"]) . "</td>";
										$html .= "<td>" . getTimeFromTimestamp($played["timestamp"]) . "</td>";
										$html .= "<td>" . $device_string . "</td>";
										$html .= "<td>" . $activity_string . "</td>";
										$html .= "<td> </td>";
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