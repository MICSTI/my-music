<?php
	include('resources.php');
	
	$html = "";
	
	// data
	$songs = $mc->getMDB()->getMostPlayedSongs();
	$artists = $mc->getMDB()->getMostPlayedArtists();
	$records = $mc->getMDB()->getMostPlayedRecords();
	
	// headline
	$html .= "<h3>Favourites</h3>";
	
	// Tabs for songs and artists
	$html .= "<ul class='nav nav-tabs'>";
		$html .= "<li class='active'>";
			$html .= "<a data-toggle='tab' href='#songs'>Songs</a>";
		$html .= "</li>";
		
		$html .= "<li>";
			$html .= "<a data-toggle='tab' href='#artists'>Artists</a>";
		$html .= "</li>";
		
		$html .= "<li>";
			$html .= "<a data-toggle='tab' href='#records'>Records</a>";
		$html .= "</li>";
	$html .= "</ul>";
	
	$html .= "<div class='tab-content'>";
		$html .= "<div id='songs' class='tab-pane fade in active'>";
			$html .= getSongContent($songs);
		$html .= "</div>";
		
		$html .= "<div id='artists' class='tab-pane fade'>";
			$html .= getArtistContent($artists);
		$html .= "</div>";
		
		$html .= "<div id='records' class='tab-pane fade'>";
			$html .= getRecordContent($records);
		$html .= "</div>";
	$html .= "</div>";
	
	echo $mc->getIndexHTML($html);
	
	/**
		Returns the content for the song tab
	*/
	function getSongContent($songs) {
		$content = "";
		
		$place = 1;
		$previous = -1;
		
		$content .= "<table class='table table-striped'>";
			$content .= "<thead>";
				$content .= "<tr>";
					$content .= "<th class='col-sm-2 rank'>Place</th>";
					$content .= "<th class='col-sm-4'>Song</th>";
					$content .= "<th class='col-sm-4'>Artist</th>";
					$content .= "<th class='col-sm-2'>Count</th>";
				$content .= "</tr>";
			$content .= "</thead>";
			
			$content .= "<tbody>";
				foreach ($songs as $song) {
					// determine rank
					$played_count = $song["PlayedCount"];
					
					if ($played_count != $previous) {
						$rank = $place;
					} else {
						$rank = "";
					}
					
					// set previous value to current value for next loop
					$previous = $played_count;
					
					// increment rank
					$place++;
					
					$content .= "<tr>";
						$content .= "<td class='rank'>" . $rank . "</td>";
						$content .= "<td><a href='song.php?id=" . $song["SongId"] . "'>" . $song["SongName"] . "</a></td>";
						$content .= "<td><a href='artist.php?id=" . $song["ArtistId"] . "'>" . $song["ArtistName"] . "</a></td>";
						$content .= "<td>" . $played_count . "</td>";
					$content .= "</tr>";
				}
			$content .= "</tbody>";
		$content .= "</table>";
		
		return $content;
	}
	
	/**
		Returns the content for the artist tab
	*/
	function getArtistContent($artists) {
		$content = "";
		
		$place = 1;
		$previous = -1;
		
		$content .= "<table class='table table-striped'>";
			$content .= "<thead>";
				$content .= "<tr>";
					$content .= "<th class='col-sm-3 rank'>Place</th>";
					$content .= "<th class='col-sm-8'>Artist</th>";
					$content .= "<th class='col-sm-3'>Count</th>";
				$content .= "</tr>";
			$content .= "</thead>";
			
			$content .= "<tbody>";
				foreach ($artists as $artist) {
					// determine rank
					$played_count = $artist["PlayedCount"];
					
					if ($played_count != $previous) {
						$rank = $place;
					} else {
						$rank = "";
					}
					
					// set previous value to current value for next loop
					$previous = $played_count;
					
					// increment rank
					$place++;
					
					$content .= "<tr>";
						$content .= "<td class='rank'>" . $rank . "</td>";
						$content .= "<td><a href='artist.php?id=" . $artist["ArtistId"] . "'>" . $artist["ArtistName"] . "</a></td>";
						$content .= "<td>" . $played_count . "</td>";
					$content .= "</tr>";
				}
			$content .= "</tbody>";
		$content .= "</table>";
		
		return $content;
	}
	
	/**
		Returns the content for the record tab
	*/
	function getRecordContent($records) {
		$content = "";
		
		$place = 1;
		$previous = -1;
		
		$content .= "<table class='table table-striped'>";
			$content .= "<thead>";
				$content .= "<tr>";
					$content .= "<th class='col-sm-2 rank'>Place</th>";
					$content .= "<th class='col-sm-4'>Record</th>";
					$content .= "<th class='col-sm-4'>Artist</th>";
					$content .= "<th class='col-sm-2'>Count</th>";
				$content .= "</tr>";
			$content .= "</thead>";
			
			$content .= "<tbody>";
				foreach ($records as $record) {
					// determine rank
					$played_count = $record["PlayedCount"];
					
					if ($played_count != $previous) {
						$rank = $place;
					} else {
						$rank = "";
					}
					
					// set previous value to current value for next loop
					$previous = $played_count;
					
					// increment rank
					$place++;
					
					$content .= "<tr>";
						$content .= "<td class='rank'>" . $rank . "</td>";
						$content .= "<td><a href='record.php?id=" . $record["RecordId"] . "'>" . $record["RecordName"] . "</a></td>";
						$content .= "<td><a href='artist.php?id=" . $record["ArtistId"] . "'>" . $record["ArtistName"] . "</a></td>";
						$content .= "<td>" . $played_count . "</td>";
					$content .= "</tr>";
				}
			$content .= "</tbody>";
		$content .= "</table>";
		
		return $content;
	}