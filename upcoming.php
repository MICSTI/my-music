<?php
	include('resources.php');

	$html = "";
	
	// Headline
	$html .= "<h3>Upcoming concerts</h3>";
	
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
		
		$html .= "<div class='event " . $label . "' data-affiliate-id='" . $event["AffiliateEventId"] . "' data-link='" . $event["AffiliateLink"] . "' data-city='" . $event["EventCityName"] . "' onclick='openLink(this)'>";
			$html .= "<div class='event-datetime'>";
				$date_mysql = new MysqlDate($event["EventDate"]);
				$date = $date_mysql->convert2AustrianDate();
				$time = $event["EventTime"];
				
				$html .= $date . " " . $time;
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
		} else if ($count <= 10) {
			return "label-iron";
		} else if ($count <= 250) {
			return "label-summer-sky";
		} else {
			return "label-buttercup";
		}
	}