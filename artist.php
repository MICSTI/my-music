<?php
	include('resources.php');
	
	// Action ids
	$SAVE_ARTIST_DETAILS = "DVDT2mad";
	
	$html = "";
	
	if ($_GET) {
		$aid = isset($_GET['id']) ? $_GET['id'] : false;
		
		if ($aid !== false) {		
			// data
			$artist_info = $mc->getMDB()->getArtist($aid);
			$play_count = $artist_info["ArtistPlayCount"];
			$releases = $artist_info["Releases"];
			
			$popular = $mc->getMDB()->getPopularSongByArtist($aid);
			
			// get charts info
			$charts_info = $mc->getMDB()->getChartsInfoForInstance("artists", $aid);
			
			// headline
			$html .= "<h3>Artist details</h3>";
			
			// general info about the artist
			$html .= "<div class='panel panel-default'>";
				$html .= "<div class='panel-heading bold'>General information</div>";
				
				$html .= "<div class='panel-body'>";
					// Name
					$html .= "<div class='song-general-info col-sm-4'>";
						$html .= "<div class='col-sm-3 bold'>Name:</div>";
						$html .= "<div class='col-sm-9'>" . $artist_info["ArtistName"] . "</div>";
					$html .= "</div>";
					
					// # times played
					$html .= "<div class='song-general-info col-sm-7'>";
						$html .= "<div class='col-sm-3 bold'>Played:</div>";
						$html .= "<div class='col-sm-9'>" . $play_count . " times</div>";
					$html .= "</div>";
					
					// artist details edit button
					$html .= "<div class='song-general-info col-sm-1'>";
						$html .= "<button type='button' id='btn-artist-details-edit' class='btn btn-default pull-right' onclick=\"crudModal('" . $SAVE_ARTIST_DETAILS . "', '" . $artist_info["ArtistId"] . "')\"><span class='glyphicon glyphicon-pencil'></span></button>";
					$html .= "</div>";
					
					// charts info
					$html .= "<div class='song-general-info col-sm-4'>";
						if (count($charts_info) > 0) {
							$html .= "<div class='col-sm-3 bold'>Charts:</div>";
							
							$html .= "<div class='col-sm-9'>";
								$html .= $mc->getFrontend()->getChartsInfoDisplay($charts_info);
							$html .= "</div>";
						}
					$html .= "</div>";
					
					// origin country
					$main_country = $mc->getMDB()->getCountry($artist_info["ArtistMainCountryId"]);
					$secondary_country = $mc->getMDB()->getCountry($artist_info["ArtistSecondaryCountryId"]);
					
					$main_country_flag = getCountryFlag($main_country);
					$secondary_country_flag = getCountryFlag($secondary_country);
					
					$html .= "<div class='song-general-info col-sm-7'>";
						$html .= "<div class='col-sm-3 bold'>Origin:</div>";
						
						$html .= "<div class='col-sm-9'>";
							$html .= "<span id='artist-main-country-flag'>" . $main_country_flag . "</span> ";
							$html .= "<span id='artist-secondary-country-flag'>" . $secondary_country_flag . "</span>";
						$html .= "</div>";
					$html .= "</div>";
				$html .= "</div>";
			$html .= "</div>";
			
			// song accordion
			$html .= "<div id='song-accordion' class='panel-group'>";
				// Popular
				$html .= "<div class='panel panel-default'>";
					$html .= "<div class='panel-heading'>";
						$html .= "<h4 class='panel-title'>";
							$html .= "<a class='bold' data-toggle='collapse' data-parent='#song-accordion' href='#songs-popular'>Most popular</a>";
						$html .= "</h4>";
					$html .= "</div>";
					
					$html .= "<div id='songs-popular' class='panel-collapse collapse in'>";
						$html .= "<div class='panel-body'>";
							// Popular songs table
							$html .= "<table class='table table-striped'>";
								$html .= "<thead>";
									$html .= "<tr>";
										$html .= "<th class='col-sm-4'>Title</th>";
										$html .= "<th class='col-sm-3 hidden-xs'>Record</th>";
										$html .= "<th class='col-sm-1 hidden-xs'>Duration</th>";
										$html .= "<th class='col-sm-2'>Count</th>";
										$html .= "<th class='col-sm-2'>Last listened</th>";
									$html .= "</tr>";
								$html .= "</thead>";
								
								$html .= "<tbody>";
									foreach ($popular as $song) {
										$html .= "<tr>";
											$html .= "<td>" . getSongLink($song["SongId"], $song["SongName"]) . "</td>";
											$html .= "<td class='hidden-xs'>" . getRecordLink($song["RecordId"], $song["RecordName"]) . "</td>";
											$html .= "<td class='hidden-xs'>" . millisecondsToMinutes($song["SongLength"]) . "</td>";
											$html .= "<td>" . $song["PlayedCount"] . "</td>";
											$html .= "<td>" . $song["MostRecentPlayed"] . "</td>";
										$html .= "</tr>";
									}
								$html .= "</tbody>";
							$html .= "</table>";
						$html .= "</div>";
					$html .= "</div>";
				$html .= "</div>";
				
				// Releases
				if (!empty($releases)) {
					foreach ($releases as $release) {
						$record_info = $mc->getMDB()->getRecord($release["RecordId"]);
						
						$html .= "<div class='panel panel-default'>";
							$html .= "<div class='panel-heading'>";
								$html .= "<h4 class='panel-title'>";
									$html .= "<a class='bold' data-toggle='collapse' data-parent='#song-accordion' href='#songs-release-" . $release["RecordId"] . "'>" . $release["RecordTitle"] . "</a>";
									
									// add inline badge to indicate album play count
									$html .= " <span class='label label-default'>" . $record_info["SongPlayedCount"] . "</span>";
								$html .= "</h4>";
							$html .= "</div>";
							
							$html .= "<div id='songs-release-" . $release["RecordId"] . "' class='panel-collapse collapse'>";
								$html .= "<div class='panel-body'>";
									$html .= $mc->getFrontend()->getRecordDetailsHtml($record_info);
								$html .= "</div>";
							$html .= "</div>";
						$html .= "</div>";
					}
				}
			$html .= "</div>";
		}
	}
	
	echo $mc->getIndexHTML($html);