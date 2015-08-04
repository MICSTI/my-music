<?php
	function getColors() {
		return array("brown", "coral", "cornflowerblue", "crimson", "green", "maroon", "orange", "tomato");
	}
	
	function getDayName($day) {
		$day_names = array(1 => "Monday", 2 => "Tuesday", 3 => "Wednesday", 4 => "Thursday", 5 => "Friday", 6 => "Saturday", 7 => "Sunday");
		
		return $day_names[$day];
	}
	
	function capitalizeFirstLetter($text) {
		return strtoupper(substr($text, 0, 1)) . substr($text, 1);
	}

	function getTimeFromTimestamp($timestamp) {
		$datetime = new DateTime($timestamp);
		
		return $datetime->format('H:i');
	}
	
	function getDateFromTimestamp($timestamp) {
		$datetime = new DateTime($timestamp);
		
		return $datetime->format('d.m.Y');
	}
	
	function getMysqlDate($austrian_date) {
		return substr($austrian_date, 6) . "-" . substr($austrian_date, 3, 2) . "-" . substr($austrian_date, 0, 2);
	}
	
	function millisecondsToMinutes($ms) {
		$time = $ms / 1000;
		
		$minutes = floor($time / 60);
		$seconds = floor($time - ($minutes * 60));
		
		if ($seconds < 10)
			$seconds = "0" . $seconds;
		
		return $minutes . ":" . $seconds;
	}
	
	function getMostRecentPlayedText($most_recent) {
		$text = "";
		
		if ($most_recent !== false) {
			$last_played = new MysqlDate($most_recent);
			$text = $last_played->convert2AustrianDatetime();
		}
		
		return $text;
	}
	
	function getSongLink($id, $text) {
		return "<a href='song.php?id=" . $id . "'>" . $text . "</a>";
	}
	
	function getArtistLink($id, $text) {
		return "<a href='artist.php?id=" . $id . "'>" . $text . "</a>";
	}
	
	function getRecordLink($id, $text) {
		return "<a href='record.php?id=" . $id . "'>" . $text . "</a>";
	}
	
	function getIconRef($icon, $img_path = "", $tooltip = "") {
		$add_tooltip = " data-toggle='tooltip' data-original-title='" . $tooltip . "'";
		
		if ($icon["IconType"] == "glyphicon") {
			return "<span class='glyphicon " . $icon["IconPath"] . "'" . ($tooltip != "" ? $add_tooltip : "") . "></span>";
		} else {
			return "<span class='icon-external'" . ($tooltip != "" ? $add_tooltip : "") . "><img src='" . $img_path . $icon["IconPath"] . "' /></span>";
		}
	}
	
	/**
		Compares two strings and returns "selected" if they are equal, and an empty string if they are not.
		Useful for adding select box options.
	*/
	function compareOption($check, $actual) {
		return (($check == $actual) ? "selected" : "");
	}
	
	/**
		Compares two strings and returns "checked" if they are equal, and an empty string if they are not.
		Useful for adding checkbox checked states.
	*/
	function compareCheck($check, $actual) {
		return (($check == $actual) ? "checked" : "");
	}