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