<?php
	include('resources.php');

	$html = "";
	
	// data
	$chart_info = $mc->getMDB()->getChartInfo("top2020");
	
	$chart_id = $chart_info["ChartId"];
	
	$songs = $mc->getMDB()->getChartsContentSongs($chart_id);
	$artists = $mc->getMDB()->getChartsContentArtists($chart_id);
	
	// headline
	$html .= "<h3>Top 20/20</h3>";
	
	// Tabs for songs and artists
	$html .= "<ul class='nav nav-tabs'>";
		$html .= "<li class='active'>";
			$html .= "<a data-toggle='tab' href='#songs'>Songs</a>";
		$html .= "</li>";
		
		$html .= "<li>";
			$html .= "<a data-toggle='tab' href='#artists'>Artists</a>";
		$html .= "</li>";
	$html .= "</ul>";
	
	$html .= "<div class='tab-content'>";
		$html .= "<div id='songs' class='tab-pane fade in active'>";
			$html .= getSongContent($mc->getMDB(), $songs);
		$html .= "</div>";
		
		$html .= "<div id='artists' class='tab-pane fade'>";
			$html .= getArtistContent($mc->getMDB(), $artists);
		$html .= "</div>";
	$html .= "</div>";
	
	echo $mc->getIndexHTML($html, "charts");
	
	/**
		Returns the content for the song tab
	*/
	function getSongContent($mdb, $songs) {
		$content = "";

		$previous = -1;
		
		$content .= "<table class='table table-striped'>";
			$content .= "<thead>";
				$content .= "<tr>";
					$content .= "<th class='col-sm-1 rank'>Place</th>";
					$content .= "<th class='col-sm-3'>Song</th>";
					$content .= "<th class='col-sm-3'>Artist</th>";
					$content .= "<th class='col-sm-1'>Count</th>";
					$content .= "<th class='col-sm-1'>Country</th>";
					$content .= "<th class='col-sm-3'> </th>";
				$content .= "</tr>";
			$content .= "</thead>";
			
			$content .= "<tbody>";
				foreach ($songs as $song) {
					$played_count = $song["PlayedCount"];
					
					// country
					$main_country = $mdb->getCountry($song["ArtistMainCountryId"]);
					$secondary_country = $mdb->getCountry($song["ArtistSecondaryCountryId"]);
					
					$main_country_flag = getCountryFlag($main_country);
					$secondary_country_flag = getCountryFlag($secondary_country);
					
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
						$content .= "<td>" . $main_country_flag . " " . $secondary_country_flag . "</td>";
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
	function getArtistContent($mdb, $artists) {
		$content = "";
		
		$previous = -1;
		
		$content .= "<table class='table table-striped'>";
			$content .= "<thead>";
				$content .= "<tr>";
					$content .= "<th class='col-sm-1 rank'>Place</th>";
					$content .= "<th class='col-sm-3'>Artist</th>";
					$content .= "<th class='col-sm-1'>Count</th>";
					$content .= "<th class='col-sm-1'>Country</th>";
					$content .= "<th class='col-sm-6'> </th>";
				$content .= "</tr>";
			$content .= "</thead>";
			
			$content .= "<tbody>";
				foreach ($artists as $artist) {
					$played_count = $artist["PlayedCount"];
					
					// country
					$main_country = $mdb->getCountry($artist["ArtistMainCountryId"]);
					$secondary_country = $mdb->getCountry($artist["ArtistSecondaryCountryId"]);
					
					$main_country_flag = getCountryFlag($main_country);
					$secondary_country_flag = getCountryFlag($secondary_country);
					
					// don't display rank if it's the same count as before - they are tied
					$rank = $artist["Rank"];
					$rank_display = $played_count == $previous ? "" : $rank;
					
					// set previous value to current value for next loop
					$previous = $played_count;
					
					$content .= "<tr>";
						$content .= "<td class='rank'>" . $rank_display . "</td>";
						$content .= "<td><a href='artist.php?id=" . $artist["ArtistId"] . "'>" . $artist["ArtistName"] . "</a></td>";
						$content .= "<td>" . $played_count . "</td>";
						$content .= "<td>" . $main_country_flag . " " . $secondary_country_flag . "</td>";
						$content .= "<td> </td>";
					$content .= "</tr>";
				}
			$content .= "</tbody>";
		$content .= "</table>";
		
		return $content;
	}