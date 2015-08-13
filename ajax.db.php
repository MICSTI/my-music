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
				
			default:
				$response["success"] = false;
				$response["message"] = "Unknown action";
				break;
		}
	}
	
	echo json_encode($response);