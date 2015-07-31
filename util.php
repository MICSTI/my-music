<?php
	function getTimeFromTimestamp($timestamp) {
		$datetime = new DateTime($timestamp);
		
		return $datetime->format('H:i');
	}
	
	function getDateFromTimestamp($timestamp) {
		$datetime = new DateTime($timestamp);
		
		return $datetime->format('d.m.Y');
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
		Inits all elements that have the .selectpicker class attribute as a selectpicker
	*/
	function getSelectpickerReadyFunction() {
		return "<script type='text/javascript'>$(document).ready(function() { $('.selectpicker').selectpicker({}); } );</script>";
	}
	
	/**
		Inits all elements that have the data-toggle='tooltip' attribute as a tooltip
	*/
	function getTooltipReadyFunction() {
		return "<script type='text/javascript'>$(document).ready(function() { $('[data-toggle=\"tooltip\"]').tooltip(); } );</script>";
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