<?php
	include('resources.php');
	
	set_time_limit(0);
	
	$city_id = $_GET["cityId"];
	$output = $_GET["output"];
	
	$write_to_db = $output != "view";
	
	if ($city_id != "") {
		$base_url = $mc->getMdb()->getConfig("event_api_url");
	
		$event_prefix = $mc->getMdb()->getConfig("event_prefix");
		
		$blocked = explode(";", $mc->getMdb()->getConfig("event_blocked_words"));
		
		$super_counter = 0;
		
		$total_count = 0;
		
		do {
			$api_url = str_replace("{CITY_ID}", $city_id, $base_url);
			$api_url = str_replace("{INDEX}", $super_counter, $api_url);
		
			$page = mb_convert_encoding(file_get_contents($api_url), 'HTML-ENTITIES', 'UTF-8');
			
			$count = 0;
			
			// get total event count
			$total = getStringBetween($page, "<li class=\"counter\">", "</li>");
			$total_count = getStringBetween($total, "von ", " Event");
			
			while ($count < 25) {
				$item = getStringBetween($page, "<tr", "</tr>");		
				if ($item === false) {
					break;
				}
			
				$h4 = getStringBetween($item, "<h4", "</h4>");
				
				$link = cleanHtml(substr(getStringBetween($h4, "<a href=", " onclick"), 1, -1));
				$eventId = getStringBetween($link, "key=");
				$artist = cleanHtml(getStringBetween($h4, "<span>", "</span>"));
				
				try {
					$aid = $mc->getMdb()->getArtistIdByName($artist);
				} catch (PDOException $e) {
					$aid = false;
					echo "Skipped " . $artist . "<hr/>";
				}
				
				$artist_id = $aid !== false ? $aid : -1;
				
				$dl = getStringBetween($item, "<dl", "</dl>");
				
				$location = cleanHtml(getStringBetween($dl, "<dt>", "</dt>"));
				$city = cleanHtml(getStringBetween($dl, "<span>", "</span>"));
				
				$abbr = getStringBetween($item, "<abbr", "</abbr>");
				
				$datetime = getStringBetween($abbr, ">");
				
				if (strpos($datetime, "-") === false) {
					$date_ok = true;
				} else {
					$date_ok = false;
				}
				
				$dateParts = explode(" / ", $datetime);
				
				$date = $dateParts[0];
				$time = $dateParts[1];
				
				// show event flag, evaluates to true if the title does not contain one or more of the blocked words
				$show_event = !array_contains(strtolower($artist), $blocked);
				
				if ($show_event) {
					if ($write_to_db && $date_ok) {	
						// write to database
						$day = substr($date, 0, 2);
						$month = substr($date, 3, 2);
						$year = substr($date, -2) + 2000;
						
						$dateDb = $year . "-" . $month . "-" . $day;
						
						if (!$mc->getMdb()->eventEntryExists($eventId)) {
							$mc->getMdb()->addEventEntry($eventId, $artist, $artist_id, $event_prefix . $link, $location, $city_id, $city, $dateDb, $time);
						}
					} else {
						// only put it out to screen
						echo "<table>";
							echo "<tr>";
								echo "<td>Artist</td>";
								echo "<td>[" . $artist . "]</td>";
								
							echo "</tr>";
							
							echo "<tr>";
								echo "<td>Event Id</td>";
								echo "<td>[" . $eventId . "]</td>";
							echo "</tr>";
							
							echo "<tr>";
								echo "<td>Link</td>";
								echo "<td>[" . $event_prefix . $link . "]</td>";
							echo "</tr>";
							
							echo "<tr>";
								echo "<td>Location</td>";
								echo "<td>[" . $location . "]</td>";
							echo "</tr>";
							
							echo "<tr>";
								echo "<td>City</td>";
								echo "<td>[" . $city . "]</td>";
							echo "</tr>";
							
							echo "<tr>";
								echo "<td>Date</td>";
								echo "<td>[" . $date . "]</td>";
							echo "</tr>";
							
							echo "<tr>";
								echo "<td>Time</td>";
								echo "<td>[" . $time . "]</td>";
							echo "</tr>";
						echo "</table><hr/>";
					}
				}
				
				$endIndex = strpos($page, "</tr>");
				
				$page = substr($page, $endIndex + strlen("</tr>"));
				
				$count++;
				$super_counter++;
				
				if ($super_counter >= $total_count) {
					break;
				}
			}
		} while ($super_counter < $total_count);
		
		// save update timestamp
		if ($write_to_db) {
			$mc->getMdb()->setConfig("event_update_timestamp_" . $city_id, mktime());
		}
	}
	
	function getStringBetween($string, $start, $end = "", $excludeSearch = true) {
		$startPos = strpos($string, $start);
		
		if ($end != "") {
			$endPos = strpos($string, $end, $startPos);
		} else {
			$endPos = false;
		}
		
		if ($excludeSearch) {
			$startPos += strlen($start);
		}
		
		if ($startPos !== false AND $endPos !== false) {
			return trim(substr($string, $startPos, $endPos - $startPos));
		} else if ($startPos !== false AND $endPos === false) {
			return trim(substr($string, $startPos));
		}
		
		return false;
	}
	
	function array_contains($needle, $haystack) {
		foreach ($haystack as $elem) {
			if (strpos($needle, $elem) !== false) {
				return true;
			}
		}
		
		return false;
	}
	
	function cleanHtml($input) {
		return html_entity_decode($input, ENT_QUOTES, 'UTF-8');
	}