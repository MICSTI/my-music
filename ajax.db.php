<?php
	include('resources.php');
	
	$response = array();
	
	if ($_POST) {
		$action = isset($_POST['action']) ? trim($_POST['action']) : "";
		$data = isset($_POST['data']) ? trim($_POST['data']) : "";
		
		switch ($action) {
			// add songs data
			case "add_songs_data":
				$json_data = json_decode($data, true);
				
				// get info from json data
				$date = $json_data["date"];
				$device_id = $json_data["device-id"];
				$activity_id = $json_data["activity-id"];
				$songs = $json_data["songs"];
				
				// put info into database
				$success = $mc->getMDB()->addPlayedSongs($date, $device_id, $activity_id, $songs);
				
				$response["success"] = $success;
				
				break;
				
			// add MM link
			case "add_mm_link":
				$json_data = json_decode($data, true);
				
				// get info from JSON data
				$parent_id = $json_data["parent_id"];
				$child_id = $json_data["child_id"];
				
				// put info into database
				$success = $mc->getMDB()->addMMLinkConnection($parent_id, $child_id);
				
				$response["success"] = $success;
			
				break;
				
			// played date data
			case "played_date":
				$json_data = json_decode($data, true);
				
				// get info from JSON data
				$date = getMysqlDate($json_data["date"]);
				
				// get data for this date from database
				$played_data = $mc->getMDB()->getPlayedHistoryForDate($date);
				
				$count = count($played_data);
				
				for ($i = 0; $i < $count; $i++) {
					// convert MySQL timestamps to UNIX timestamps
					$mysql_timestamp = new MysqlDateTime($played_data[$i]["Timestamp"]);
					$played_data[$i]["UnixTimestamp"] = $mysql_timestamp->convert2UnixTimestamp();
					
					// add device icon
					$device = $mc->getMDB()->getDevice($played_data[$i]["DeviceId"]);
					
					$device_icon = $mc->getMDB()->getIcon($device["DeviceDeviceTypeIconId"]);
					
					$played_data[$i]["Device"] = getIconRef($device_icon, "", $device["DeviceName"]);
					
					// add activity label string
					$activity = $mc->getMDB()->getActivity($played_data[$i]["ActivityId"]);
					$played_data[$i]["Activity"] = getActivitySpan($activity);
				}
				
				$response["playeds"] = $played_data;
				
				$response["success"] = true;
			
				break;
			
			// Charts favourites
			case "charts_compilation":
				$json_data = json_decode($data, true);
				
				$chart_type = $json_data["chart_type"];
				$year = $json_data["year"];
				
				switch ($chart_type) {
					case "favourites":					
						// favourite songs
						$songs = $mc->getMDB()->getMostPlayedSongs();
						
						// favourite artists
						$artists = $mc->getMDB()->getMostPlayedArtists();
						
						// favourite records
						$records = $mc->getMDB()->getMostPlayedRecords();
						
						// compile charts
						$chart_id = $mc->getMDB()->compileCharts("favourites", $songs, $artists, $records);
						
						// update charts compilation timestamp
						$mc->getMDB()->updateChartContainerTimestamp($chart_id);
						
						// set message
						$response["message"] =  $mc->getFrontend()->getChartCompilationStatus($mc->getMDB(), true, "favourites");
							
						$response["success"] = true;
						
						break;
						
					default:
						$response["success"] = false;
						$response["message"] = "Unknown chart type '" . $chart_type . "'";
						
						break;
				}
				
				/*
				*/
			
				break;
				
			default:
				$response["success"] = false;
				$response["message"] = "Unknown action";
				break;
		}
	}
	
	echo json_encode($response);