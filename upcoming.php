<?php
	include('resources.php');
	
	$weekdays = Array("So", "Mo", "Di", "Mi", "Do", "Fr", "Sa");

	$html = "";
	
	// Headline
	$html .= "<h3 id='upcoming'>Upcoming concerts</h3>";
	
	// update status
	$html .= "<div class='event-update-status'>";
	
		$html .= "<div class='event-update-title'>Update status</div>";
	
		$cities = $mc->getMdb()->getEventCities();
		
		foreach ($cities as $city) {
			// try to get update config value for this city
			$update_timestamp = $mc->getMdb()->getConfig("event_update_timestamp_" . $city["CityId"]);
			
			if ($update_timestamp !== false) {
				$timestamp = new UnixTimestamp($update_timestamp);
				$formatted_timestamp = $timestamp->convert2AustrianDateTime();
				
				$html .= "<div class='event-city-status'>";
					$html .= "<span class='event-city-name bold'>" . $city["CityName"] . ": </span>";
					$html .= "<span class='event-city-timestamp'>" . $formatted_timestamp . "</span>";
				$html .= "</div>";
			}
		}
		
	$html .= "</div>";
	
	// event filtering
	$html .= "<div class='event-filter'>";
		$html.= "<span class='bold'>Event filtering: </span>";
	
		// show all events
		$html .= "<span class='event-filter-item bold' data-filter='all'>All events</span>";
		$html .= "<span class='event-filter-item' data-filter='matched'>Matched events</span>";
		$html .= "<span class='event-filter-item' data-filter='graz'>Events in Graz</span>";
		$html .= "<span class='event-filter-item' data-filter='vienna'>Events in Vienna</span>";
	$html .= "</div>";
	
	$events = $mc->getMdb()->getEventEntries();
	
	foreach ($events as $event) {
		$name = $event["EventName"];
		$viewed = $event["EventViewed"];
		
		$aid = $event["ArtistId"];
		
		if ($aid > -1) {
			$artist = $mc->getMdb()->getArtist($aid);
			$played = $artist["ArtistPlayCount"];
		} else {
			$played = 0;
		}
		
		$label = getImportanceLabel($played);
		
		$matched = $label != "" ? "true" : "false";
		
		$html .= "<div class='event " . $label . "' data-matched='" . $matched . "' data-affiliate-id='" . $event["AffiliateEventId"] . "' data-link='" . $event["AffiliateLink"] . "' data-city='" . strtolower($event["EventCityName"]) . "' onclick='openLink(this)'>";
		
			// "NEW" ribbon
			if ($viewed == 0)
				$html .= "<div class='ribbon'><span>New</span></div>";
		
			$html .= "<div class='event-datetime'>";
				$php_date = strtotime($event["EventDate"]);
				$weekday = $weekdays[date("w", $php_date)];
			
				$date_mysql = new MysqlDate($event["EventDate"]);
				$date = $date_mysql->convert2AustrianDate();
				$time = $event["EventTime"];
				
				$html .= $weekday . ", " . $date . " " . $time;
			$html .= "</div>";
			
			$html .= "<div class='event-artist'>" . $name . "</div>";
			
			$html .= "<div class='event-location'>" . $event["EventLocation"] . "</div>";
		$html .= "</div>";
	}
	
	echo $mc->getIndexHTML($html, "concerts");
	
	// mark the events as viewed
	$mc->getMdb()->markEventsAsViewed();
	
	function getImportanceLabel($count) {
		if ($count <= 0) {
			return "";
		} else if ($count < 10) {
			return "label-iron";
		} else if ($count < 200) {
			return "label-summer-sky";
		} else {
			return "label-buttercup";
		}
	}