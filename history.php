<?php
	include('resources.php');
	
	$html = "";
	
	$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
	
	$mysql_date = new MysqlDate($date);
	
	$songs = $mc->getMDB()->getPlayedHistoryForDate($date);
	
	// setting for displaying red exclamation mark next to artist name
	$no_country_setting = $mc->getMDB()->getConfig("display_no_country_indicator");
	
	if ($no_country_setting == "true" OR $no_country_setting == "yes") {
		$display_no_country = true;
	} else {
		$display_no_country = false;
	}
	
	// day name
	$datetime = new DateTime($date);
	$day_name = getDayName(date('N', $datetime->getTimestamp()));
	
	$datepicker_elem = "<input type='text' id='pickdate' class='form-control' placeholder='Pick date' value='" . $mysql_date->convert2AustrianDate() . "' autocomplete='off' />";
	
	// Headline
	$html .= "<h3>History</h3>";
	
	// Year before + year after
	$year_jump = "";
	
	if (substr($date, 5) == "02-29") {
		// special case: 29th of February - Year before shows 4 years before
		// we ignore the other special case of leap years that are divisible by 100 but not by 400 - like 2100, 2200, etc - these are not actually leap years but we ignore them.
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
			$html .= "<span id='history-dp'><strong>Played songs on " . $day_name . " </strong>" . $datepicker_elem . "</span>";
		} else {
			$html .= "<span id='history-dp'><strong>No songs were played on " . $day_name . " </strong>" . $datepicker_elem . "</span>";
		}		
		$html .= "<button type='button' class='btn btn-primary' onclick='window.location.href=\"history.php?date=" . $year_after . "\"'>" . $after_text . " <span class='glyphicon glyphicon-chevron-right'></span></button>";
	$html .= "</span>";
	
	if (!empty($songs)) {
		$html .= "<table class='table table-striped'>";
			$html .= "<thead>";
				$html .= "<tr>";
					$html .= "<th class='col-sm-1'>Time</th>";
					$html .= "<th class='col-sm-3'>Song</th>";
					$html .= "<th class='col-sm-3'>Artist</th>";
					$html .= "<th class='col-sm-3 hidden-xs'>Record</th>";
					$html .= "<th class='col-sm-1 hidden-xs'>Device</th>";
					$html .= "<th class='col-sm-1 hidden-xs'>Activity</th>";
				$html .= "</tr>";
			$html .= "</thead>";
			
			$html .= "<tbody>";
				foreach ($songs as $song) {
					$device = $mc->getMDB()->getDevice($song["DeviceId"]);
					$device_icon = $mc->getMDB()->getIcon($device["DeviceDeviceTypeIconId"]);
					$device_string = getIconRef($device_icon, "", $device["DeviceName"]);
					
					$activity = $mc->getMDB()->getActivity($song["ActivityId"]);
					$activity_string = getActivitySpan($activity);
					
					// get artist info (for displaying exclamation mark if artist has no country)
					$artist = $mc->getMDB()->getArtist($song["ArtistId"]);
					
					if (empty($artist["ArtistMainCountryId"]) AND empty($artist["ArtistSecondaryCountryId"])) {
						$no_country = true;
					} else {
						$no_country = false;
					}
					
					if ($display_no_country AND $no_country) {
						$no_country_span = "<span class='no-country'>!</span>";
					} else {
						$no_country_span = "";
					}
					
					$html .= "<tr>";
						$html .= "<td>" . getTimeFromTimestamp($song["Timestamp"]) . "</td>";
						$html .= "<td>" . getSongLink($song["SongId"], $song["SongName"]) . "</td>";
						$html .= "<td>" . getArtistLink($song["ArtistId"], $song["ArtistName"]) . $no_country_span . "</td>";
						$html .= "<td class='hidden-xs'>" . getRecordLink($song["RecordId"], $song["RecordName"]) . "</td>";
						$html .= "<td class='hidden-xs'>" . $device_string . "</td>";
						$html .= "<td class='hidden-xs'>" . $activity_string . "</td>";
					$html .= "</tr>";
				}
			$html .= "</tbody>";
		$html .= "</table>";
	}
	
	echo $mc->getIndexHTML($html, "history");