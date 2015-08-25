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
	
	function formatBitrate($bitrate) {
		return floor($bitrate / 1000);
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
	
	/**
		Returns the country flag span with a tooltip.
		If the extended option is set to true, instead of the tooltip the country name is displayed next to the flag.
	*/
	function getCountryFlag($country, $extended = false) {
		if (empty($country)) {
			return "";
		}
		
		if ($extended) {
			$data_tooltip = "";
			$country_name = " <span>" . $country["CountryName"] . "</span>";
		} else {
			$data_tooltip = " data-toggle='tooltip' data-original-title='" . $country["CountryName"] . "'";
			$country_name = "";
		}
		
		return "<span data-id='" . $country["CountryId"] . "' class='flag-icon flag-icon-" . $country["CountryShort"] . "'" . $data_tooltip . "></span>" . $country_name;
	}
	
	function getIconRef($icon, $img_path = "", $tooltip = "") {
		$add_tooltip = " data-toggle='tooltip' data-original-title='" . $tooltip . "'";
		
		if ($icon["IconType"] == "glyphicon") {
			return "<span class='glyphicon " . $icon["IconPath"] . "'" . ($tooltip != "" ? $add_tooltip : "") . "></span>";
		} else {
			return "<span class='icon-external'" . ($tooltip != "" ? $add_tooltip : "") . "><img src='" . $img_path . $icon["IconPath"] . "' /></span>";
		}
	}
	
	function getActivitySpan($activity) {
		return "<span class='label label-big label-" . $activity["ActivityColor"] . "'>#" . $activity["ActivityName"] . "</span>";
	}
	
	/**
		Returns a span containing the rating displayed in stars and half-stars
	*/
	function getStarsRating($rating) {
		$html = "";
		
		$stars = 0;
		
		if ($rating >= 96) {
			$stars = 5;
		} else if ($rating >= 86) {
			$stars = 4.5;
		} else if ($rating >= 76) {
			$stars = 4;
		} else if ($rating >= 66) {
			$stars = 3.5;
		} else if ($rating >= 56) {
			$stars = 3;
		} else if ($rating >= 46) {
			$stars = 2.5;
		} else if ($rating >= 36) {
			$stars = 2;
		} else if ($rating >= 26) {
			$stars = 1.5;
		} else if ($rating >= 16) {
			$stars = 1;
		} else if ($rating >= 6) {
			$stars = 0.5;
		} else if ($rating >= 0) {
			$stars = 0;
		} else {
			$stars = -1;
		}
		
		$html .= "<span class='star-rating'>";
			if ($stars < 0) {
				// no rating
			} else {
				while ($stars > 0) {
					if ($stars >= 1) {
						// full star
						$html .= "<span class='glyphicon glyphicon-star'></span>";
					} else {
						// half star
						$html .= "<span class='glyphicon glyphicon-star half-star'></span>";
					}
					
					$stars--;
				}
			}
		$html .= "</span>";
		
		return $html;
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
	
	/**
		Returns a randomly generated string.
		Optionally, you can set the size of the string, it defaults to 8.
	*/
	function generateRandomString($length = 8) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
	
	/**
		Returns the country select box, default value is 0 (extra option: none)
	*/
	function getCountrySelectBox($mdb, $params, $country_id = 0) {
		$html = "";
		
		$html .= "<select class='" . $params["class"] . "' id='" . $params["id"] . "' name='" . $params["name"] . "'>";
		
			// blank option
			$html .= "<option value='0'>None</option>";
			
			// all country options
			$countries = $mdb->getCountries();
			
			foreach ($countries as $country) {
				$html .= "<option value='" . $country["CountryId"] . "' data-content=\"" . getCountryFlag($country, true) . "\" " . compareOption($country["CountryId"], $country_id) . ">" . $country["CountryName"] . "</option>";
			}
			
		$html .= "</select>";
		
		return $html;
	}
	
	/**
		Returns the device select box, default value is the default device from the database (config property "default_device")
	*/
	function getDeviceSelectBox($mdb, $params, $device_id = 0) {
		$html = "";
		
		$html .= "<select class='" . $params["class"] . "' id='" . $params["id"] . "' name='" . $params["name"] . "'>";
		
			// all devices
			$devices = $mdb->getDevices();
			
			// keep track of active state to add a divider between active and non-active devices
			$dev_active = true;
			
			// if no device_id was passed, use the default device from the database
			$selected_device = $device_id > 0 ? $device_id : $mdb->getConfig("default_device");
			
			foreach ($devices as $device) {
				$icon = $mdb->getIcon($device["DeviceDeviceTypeIconId"]);
				
				if ($device["DeviceActive"] != $dev_active) {
					$dev_active = $device["DeviceActive"];
					$html .= "<option data-divider='true'></option>";
				}
				
				$html .= "<option value='" . $device["DeviceId"] . "' data-icon='" . $icon["IconPath"] . "' " . compareOption($selected_device, $device["DeviceId"]) . ">" . $device["DeviceName"] . "</option>";
			}
		
		$html .= "</select>";
		
		return $html;
	}
	
	/**
		Returns the activity select box, default value is the default on the way activity from the database (config property "default_mobile_activity")
	*/
	function getActivitySelectBox($mdb, $params, $activity_id = 0) {
		$html = "";
		
		$html .= "<select class='" . $params["class"] . "' id='" . $params["id"] . "' name='" . $params["name"] . "'>";
		
			// all devices
			$activities = $mdb->getActivities();
			
			// if no activity_id was passed, use the default activity from the database
			$selected_activity = $activity_id > 0 ? $activity_id : $mdb->getConfig("default_mobile_activity");
			
			foreach ($activities as $activity) {
				$html .= "<option value='" . $activity["ActivityId"] . "' data-content=\"<span class='label label-big label-" . $activity["ActivityColor"] . "'>#" . $activity["ActivityName"] . "</span> \" " . compareOption($selected_activity, $activity["ActivityId"]) . ">" . "</option>";
			}
		
		$html .= "</select>";
		
		return $html;
	}