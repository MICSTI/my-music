<?php
	include('resources.php');
	
	$html = "";
	
	$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
	
	$mysql_date = new MysqlDate($date);
	
	$songs = $mc->getMDB()->getPlayedHistoryForDate($date);
	
	$datepicker_elem = "<input type='text' id='pickdate' placeholder='Pick date' size='12' value='" . $mysql_date->convert2AustrianDate() . "' />";
	
	// Headline
	$html .= "<h3>History</h3>";
	
	// Year before + year after
	$year_jump = "";
	
	if (substr($date, 5) == "02-29") {
		// special case: 29th of February - Year before shows 4 years before
		// we ignore the other special case of leap years that are divisible by 100 but not by 400 - like 2100, 2200, etc.
		$before_text = "4 years before";
		$after_text = "4 years after";
		
		$year_before = (substr($date, 0, 4) - 4) . substr($date, 4);
		$year_after = (substr($date, 0, 4) + 4) . substr($date, 4);
	} else {
		// normal case: year before
		$before_text = "Year before";
		$after_text = "Year after";
		
		$year_before = (substr($date, 0, 4) - 1) . substr($date, 4);
		$year_after = (substr($date, 0, 4) + 1) . substr($date, 4);
	}
	
	$html .= "<span>";
		$html .= "<button type='button' class='btn btn-primary' onclick='window.location.href=\"history.php?date=" . $year_before . "\"'><span class='glyphicon glyphicon-chevron-left'></span> " . $before_text . "</button>";
		
		if (!empty($songs)) {
			$html .= "<span id='history-dp'><strong>Played songs on </strong>" . $datepicker_elem . "</span>";
		} else {
			$html .= "<span id='history-dp'><strong>No songs were played on </strong>" . $datepicker_elem . "</span>";
		}		
		$html .= "<button type='button' class='btn btn-primary' onclick='window.location.href=\"history.php?date=" . $year_after . "\"'>" . $after_text . " <span class='glyphicon glyphicon-chevron-right'></span></button>";
	$html .= "</span>";
	
	if (!empty($songs)) {
		$html .= "<table class='table table-striped'>";
			$html .= "<thead>";
				$html .= "<tr>";
					$html .= "<th>Time</th>";
					$html .= "<th>Song</th>";
					$html .= "<th>Artist</th>";
					$html .= "<th class='hidden-xs'>Record</th>";
				$html .= "</tr>";
			$html .= "</thead>";
			
			$html .= "<tbody>";
				foreach ($songs as $song) {
					$html .= "<tr>";
						$html .= "<td>" . getTimeFromTimestamp($song["Timestamp"]) . "</td>";
						$html .= "<td>" . getSongLink($song["SongId"], $song["SongName"]) . "</td>";
						$html .= "<td>" . getArtistLink($song["ArtistId"], $song["ArtistName"]) . "</td>";
						$html .= "<td class='hidden-xs'>" . getRecordLink($song["RecordId"], $song["RecordName"]) . "</td>";
					$html .= "</tr>";
				}
			$html .= "</tbody>";
		$html .= "</table>";
	}
	
	echo $mc->getIndexHTML($html, "history");