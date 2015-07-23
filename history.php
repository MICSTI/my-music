<?php
	include('resources.php');
	
	$html = "";
	
	$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
	
	$mysql_date = new MysqlDate($date);
	
	$songs = $mc->getMDB()->getPlayedHistoryForDate($date);
	
	$datepicker_elem = "<input type='text' id='pickdate' placeholder='Pick date' size='12' />";
	
	if (!empty($songs)) {
		$html .= "<h4><strong>Played songs on </strong>" . $datepicker_elem . "</h4>";
		
		$html .= "<span>";
			$html .= "<span><a href='history.php'>Year before</span>";
			$html .= "<span><a href='history.php'>Year after</span>";
		$html .= "</span>";
	
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
						$html .= "<td>" . $song["SongName"] . "</td>";
						$html .= "<td>" . $song["ArtistName"] . "</td>";
						$html .= "<td class='hidden-xs'>" . $song["RecordName"] . "</td>";
					$html .= "</tr>";
				}
			$html .= "</tbody>";
		$html .= "</table>";
	} else {
		$html .= "<h4><strong>No songs were played on </strong>" . $datepicker_elem . "</h4>";
	}
	
	echo $mc->getIndexHTML($html);