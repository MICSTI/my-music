<?php
	include('resources.php');
	
	$html = "";
	
	// data
	$chart_info = $mc->getMDB()->getChartInfo("favourites");
	
	$chart_id = $chart_info["ChartId"];
	
	$songs = $mc->getMDB()->getChartsContentSongs($chart_id);
	$artists = $mc->getMDB()->getChartsContentArtists($chart_id);
	$records = $mc->getMDB()->getChartsContentRecords($chart_id);
	
	// headline
	$html .= "<h3>Favourites</h3>";
	
	// Tabs for songs, artists and records
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
	
	echo $mc->getIndexHTML($html, "charts");
	
	/**
		Returns the content for the song tab
	*/
	function getSongContent($songs) {
		$content = "";

		$previous = -1;
		
		$content .= "<table class='table table-striped'>";
			$content .= "<thead>";
				$content .= "<tr>";
					$content .= "<th class='col-sm-1 rank'>Place</th>";
					$content .= "<th class='col-sm-3'>Song</th>";
					$content .= "<th class='col-sm-3'>Artist</th>";
					$content .= "<th class='col-sm-1'>Count</th>";
					$content .= "<th class='col-sm-4'> </th>";
				$content .= "</tr>";
			$content .= "</thead>";
			
			$content .= "<tbody>";
				foreach ($songs as $song) {
					$played_count = $song["PlayedCount"];
					
					// don't display rank if it's the same count as before - they are tied
					$rank = $song["Rank"];
					$rank_display = $played_count == $previous ? "" : $rank;
					
					// set previous value to current value for next loop
					$previous = $played_count;
					
					$content .= "<tr>";
						$content .= "<td class='rank'>" . $rank_display . "</td>";
						$content .= "<td><a href='song.php?id=" . $song["SongId"] . "'>" . $song["SongName"] . "</a></td>";
						$content .= "<td><a href='artist.php?id=" . $song["ArtistId"] . "'>" . $song["ArtistName"] . "</a></td>";
						$content .= "<td>" . $played_count . "</td>";
						$content .= "<td> </td>";
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
		
		$previous = -1;
		
		$content .= "<table class='table table-striped'>";
			$content .= "<thead>";
				$content .= "<tr>";
					$content .= "<th class='col-sm-1 rank'>Place</th>";
					$content .= "<th class='col-sm-3'>Artist</th>";
					$content .= "<th class='col-sm-1'>Count</th>";
					$content .= "<th class='col-sm-7'> </th>";
				$content .= "</tr>";
			$content .= "</thead>";
			
			$content .= "<tbody>";
				foreach ($artists as $artist) {
					$played_count = $artist["PlayedCount"];
					
					// don't display rank if it's the same count as before - they are tied
					$rank = $artist["Rank"];
					$rank_display = $played_count == $previous ? "" : $rank;
					
					// set previous value to current value for next loop
					$previous = $played_count;
					
					$content .= "<tr>";
						$content .= "<td class='rank'>" . $rank_display . "</td>";
						$content .= "<td><a href='artist.php?id=" . $artist["ArtistId"] . "'>" . $artist["ArtistName"] . "</a></td>";
						$content .= "<td>" . $played_count . "</td>";
						$content .= "<td> </td>";
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
		
		$previous = -1;
		
		$content .= "<table class='table table-striped'>";
			$content .= "<thead>";
				$content .= "<tr>";
					$content .= "<th class='col-sm-1 rank'>Place</th>";
					$content .= "<th class='col-sm-3'>Record</th>";
					$content .= "<th class='col-sm-3'>Artist</th>";
					$content .= "<th class='col-sm-1'>Count</th>";
					$content .= "<th class='col-sm-4'> </th>";
				$content .= "</tr>";
			$content .= "</thead>";
			
			$content .= "<tbody>";
				foreach ($records as $record) {
					$played_count = $record["PlayedCount"];
					
					// don't display rank if it's the same count as before - they are tied
					$rank = $record["Rank"];
					$rank_display = $played_count == $previous ? "" : $rank;
					
					// set previous value to current value for next loop
					$previous = $played_count;
					
					$content .= "<tr>";
						$content .= "<td class='rank'>" . $rank_display . "</td>";
						$content .= "<td><a href='record.php?id=" . $record["RecordId"] . "'>" . $record["RecordName"] . "</a></td>";
						$content .= "<td><a href='artist.php?id=" . $record["ArtistId"] . "'>" . $record["ArtistName"] . "</a></td>";
						$content .= "<td>" . $played_count . "</td>";
						$content .= "<td> </td>";
					$content .= "</tr>";
				}
			$content .= "</tbody>";
		$content .= "</table>";
		
		return $content;
	}