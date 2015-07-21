<?php
	include('resources.php');
	
	$html = "";
	
	if ($_GET) {
		$date = $_GET['date'];
		
		$mysql_date = new MysqlDate($date);
		
		$songs = $mc->getMDB()->getPlayedHistoryForDate($date);
		
		if (!empty($songs)) {
			$html .= "<h4><strong>Played songs on " . $mysql_date->convert2AustrianDate() . "</strong></h4>";
		
			$html .= "<table class='table table-striped'>";
				$html .= "<thead>";
					$html .= "<tr>";
						$html .= "<th>Time</th>";
						$html .= "<th>Song</th>";
						$html .= "<th>Artist</th>";
						$html .= "<th>Record</th>";
					$html .= "</tr>";
				$html .= "</thead>";
				
				$html .= "<tbody>";
					foreach ($songs as $song) {
						$html .= "<tr>";
							$html .= "<td>" . getTimeFromTimestamp($song["Timestamp"]) . "</td>";
							$html .= "<td>" . $song["SongName"] . "</td>";
							$html .= "<td>" . $song["ArtistName"] . "</td>";
							$html .= "<td>" . $song["RecordName"] . "</td>";
						$html .= "</tr>";
					}
				$html .= "</tbody>";
			$html .= "</table>";
		} else {
			$html .= "<h4><strong>No songs were played on " . $mysql_date->convert2AustrianDate() . "</strong></h4>";
		}
		
		
	}
	
	echo $mc->getIndexHTML($html);